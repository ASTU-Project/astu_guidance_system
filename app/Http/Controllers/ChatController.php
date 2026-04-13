<?php

namespace App\Http\Controllers;

use App\Models\AutomationSetting;
use App\Models\ChatMessage;
use App\Models\Department;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class ChatController extends Controller
{
    private const DEFAULT_MODEL = 'qwen-3-235b-a22b-instruct-2507';
    private const API = 'https://api.cerebras.ai/v1/chat/completions';
    private const HISTORY_LIMIT = 5;
    private const DEPARTMENT_CONTEXT_LIMIT = 30;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'session_id' => ['nullable', 'string', 'max:100'],
        ]);

        $apiKey = config('services.cerebras.key');
        $model = (string) config('services.cerebras.model', self::DEFAULT_MODEL);

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'error' => 'Cerebras API key is missing. Set CEREBRAS_API_KEY in .env.',
            ], 500);
        }

        $user = $request->user();
        $message = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();
        $systemInstruction = $this->buildSystemInstruction($user);
        $conversationMessages = $this->buildConversationMessages($user, $sessionId, $message, $systemInstruction);
        $enabledToolGroups = $this->getEnabledToolGroups($user);
        $tools = $this->buildToolDefinitions($enabledToolGroups);

        try {
            $phaseOnePayload = [
                'model' => $model,
                'messages' => $conversationMessages,
                'max_completion_tokens' => 1200,
            ];

            if ($tools !== []) {
                $phaseOnePayload['tools'] = $tools;
                $phaseOnePayload['tool_choice'] = 'auto';
            }

            $phaseOneResponse = Http::withToken($apiKey)
                ->withOptions([
                    // These options reduce intermittent TLS/socket reset issues on some local stacks.
                    'force_ip_resolve' => 'v4',
                    'version' => 1.1,
                ])
                ->connectTimeout(8)
                ->timeout(20)
                ->retry(2, 400, function (\Exception $exception): bool {
                    return $exception instanceof ConnectionException;
                }, throw: false)
                ->post(self::API, $phaseOnePayload);

            if (! $phaseOneResponse->successful()) {
                $status = $phaseOneResponse->status();
                $friendlyError = 'Unexpected API error.';

                if ($status === 401) {
                    $friendlyError = 'Invalid API key.';
                } elseif ($status === 429) {
                    $friendlyError = 'AI service is busy right now (rate limited). Please wait a moment and try again.';
                } elseif ($status >= 500) {
                    $friendlyError = 'AI service is down. Try again later.';
                }

                Log::warning('Cerebras non-success response', [
                    'status' => $status,
                    'body' => $phaseOneResponse->json() ?? $phaseOneResponse->body(),
                ]);

                return response()->json([
                    'error' => $friendlyError,
                ], $status === 429 ? 429 : 502);
            }

            $assistantMessagePayload = $phaseOneResponse->json('choices.0.message');

            if (! is_array($assistantMessagePayload)) {
                return response()->json([
                    'error' => 'AI response format was invalid.',
                ], 502);
            }

            $toolCalls = is_array($assistantMessagePayload['tool_calls'] ?? null)
                ? $assistantMessagePayload['tool_calls']
                : [];

            $assistantMessage = $assistantMessagePayload['content'] ?? '';

            if ($toolCalls !== []) {
                $toolResults = [];

                foreach ($toolCalls as $call) {
                    $toolResults[] = $this->executeToolCall($call, $enabledToolGroups);
                }

                $phaseTwoResponse = Http::withToken($apiKey)
                    ->withOptions([
                        'force_ip_resolve' => 'v4',
                        'version' => 1.1,
                    ])
                    ->connectTimeout(8)
                    ->timeout(20)
                    ->retry(2, 400, function (\Exception $exception): bool {
                        return $exception instanceof ConnectionException;
                    }, throw: false)
                    ->post(self::API, [
                        'model' => $model,
                        'messages' => array_merge($conversationMessages, [$assistantMessagePayload], $toolResults),
                        'max_completion_tokens' => 1200,
                    ]);

                if (! $phaseTwoResponse->successful()) {
                    $status = $phaseTwoResponse->status();

                    $friendlyError = 'AI tool follow-up request failed. Please retry.';

                    if ($status === 429) {
                        $friendlyError = 'AI service is busy right now (rate limited). Please wait a moment and try again.';
                    } elseif ($status === 401) {
                        $friendlyError = 'Invalid API key.';
                    } elseif ($status >= 500) {
                        $friendlyError = 'AI service is down. Try again later.';
                    }

                    Log::warning('Cerebras phase-two non-success response', [
                        'status' => $status,
                        'body' => $phaseTwoResponse->json() ?? $phaseTwoResponse->body(),
                    ]);

                    return response()->json([
                        'error' => $friendlyError,
                    ], $status === 429 ? 429 : 502);
                }

                $assistantMessage = $phaseTwoResponse->json('choices.0.message.content');
            }

            $assistantText = $this->normalizeAssistantContent($assistantMessage);
            $assistantHtml = $this->renderAssistantHtml($assistantText);

            if ($user !== null && Schema::hasTable('chat_messages')) {
                ChatMessage::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'role' => 'user',
                    'content' => $message,
                ]);

                ChatMessage::create([
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'role' => 'assistant',
                    'content' => $assistantText,
                ]);
            }

            return response()->json([
                'message' => $assistantText,
                'message_html' => $assistantHtml,
                'session_id' => $sessionId,
            ]);
        } catch (ConnectionException $exception) {
            Log::warning('Cerebras connection exception', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Network connection to Cerebras was reset. Please retry.',
            ], 503);
        } catch (\Throwable $exception) {
            Log::error('Chat controller exception', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to complete chat request.',
            ], 500);
        }
    }

    private function normalizeAssistantContent(mixed $content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            $parts = [];

            foreach ($content as $item) {
                if (is_array($item) && ($item['type'] ?? null) === 'text' && isset($item['text'])) {
                    $parts[] = (string) $item['text'];
                }
            }

            if ($parts !== []) {
                return implode("\n", $parts);
            }
        }

        return 'No response content returned by model.';
    }

    private function renderAssistantHtml(string $content): string
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($content);
    }

    private function buildSystemInstruction(mixed $user): string
    {
        $baseInstruction = 'You are an academic administration assistant. Keep responses concise, structured, and useful.';

        if ($user === null || ! isset($user->id) || ! Schema::hasTable('automation_settings')) {
            return $baseInstruction;
        }

        $settings = AutomationSetting::query()->where('user_id', $user->id)->first();

        if (! $settings) {
            return $baseInstruction;
        }

        $enabledGroups = is_array($settings->enabled_tool_groups) ? $settings->enabled_tool_groups : [];
        $enabledGroupsText = $enabledGroups === [] ? 'none selected' : implode(', ', $enabledGroups);

        $customPrompt = trim((string) ($settings->system_prompt ?? ''));

        $context = [
            $baseInstruction,
            'User automation settings context:',
            '- Enabled tool groups: '.$enabledGroupsText,
            '- Enable write tools: '.($settings->enable_write_tools ? 'true' : 'false'),
            '- Confirm destructive actions: '.($settings->confirm_destructive_actions ? 'true' : 'false'),
            '- Tool execution behavior: when tools are available, you may call them to get live data before answering.',
        ];

        if ($customPrompt !== '') {
            $context[] = 'User custom system prompt:';
            $context[] = Str::limit($customPrompt, 1200);
        }

        return implode("\n", $context);
    }

    /**
     * @return array<int, string>
     */
    private function getEnabledToolGroups(mixed $user): array
    {
        if ($user === null || ! isset($user->id) || ! Schema::hasTable('automation_settings')) {
            return [];
        }

        $settings = AutomationSetting::query()->where('user_id', $user->id)->first();

        if (! $settings) {
            return [];
        }

        return is_array($settings->enabled_tool_groups) ? $settings->enabled_tool_groups : [];
    }

    /**
     * @param array<int, string> $enabledToolGroups
     * @return array<int, array<string, mixed>>
     */
    private function buildToolDefinitions(array $enabledToolGroups): array
    {
        $tools = [];

        if (in_array('departments', $enabledToolGroups, true)) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'department_list',
                    'description' => 'List all departments with id, name, code, min_gpa, and spot_limit.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [],
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * @param array<string, mixed> $call
     * @param array<int, string> $enabledToolGroups
     * @return array<string, string>
     */
    private function executeToolCall(array $call, array $enabledToolGroups): array
    {
        $callId = (string) ($call['id'] ?? Str::uuid());
        $function = is_array($call['function'] ?? null) ? $call['function'] : [];
        $functionName = (string) ($function['name'] ?? '');

        $resultPayload = match ($functionName) {
            'department_list' => $this->runDepartmentListTool($enabledToolGroups),
            default => [
                'error' => 'Unknown tool requested: '.$functionName,
            ],
        };

        return [
            'role' => 'tool',
            'tool_call_id' => $callId,
            'content' => json_encode($resultPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"error":"Tool result encoding failed."}',
        ];
    }

    /**
     * @param array<int, string> $enabledToolGroups
     * @return array<string, mixed>
     */
    private function runDepartmentListTool(array $enabledToolGroups): array
    {
        if (! in_array('departments', $enabledToolGroups, true)) {
            return [
                'error' => 'Department tool is disabled in settings.',
            ];
        }

        $departments = Department::query()
            ->select(['id', 'name', 'code', 'min_gpa', 'spot_limit'])
            ->orderBy('name')
            ->limit(self::DEPARTMENT_CONTEXT_LIMIT)
            ->get();

        return [
            'count' => $departments->count(),
            'departments' => $departments->toArray(),
        ];
    }

    /**
     * Build a simple chat history context: system + last N session messages + current user message.
     *
     * @return array<int, array<string, string>>
     */
    private function buildConversationMessages(mixed $user, string $sessionId, string $currentMessage, string $systemInstruction): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $systemInstruction,
            ],
        ];

        if ($user !== null && isset($user->id) && Schema::hasTable('chat_messages')) {
            $history = ChatMessage::query()
                ->where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->whereIn('role', ['user', 'assistant'])
                ->latest('id')
                ->limit(self::HISTORY_LIMIT)
                ->get(['role', 'content'])
                ->reverse()
                ->values();

            foreach ($history as $item) {
                $messages[] = [
                    'role' => $item->role,
                    'content' => (string) $item->content,
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }
}
