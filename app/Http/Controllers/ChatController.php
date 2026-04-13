<?php

namespace App\Http\Controllers;

use App\Models\AutomationSetting;
use App\Models\ChatMessage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;

class ChatController extends Controller
{
    private const DEFAULT_MODEL = 'qwen-3-235b-a22b-instruct-2507';
    private const API = 'https://api.cerebras.ai/v1/chat/completions';

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

        try {
            $response = Http::withToken($apiKey)
                ->withOptions([
                    // These options reduce intermittent TLS/socket reset issues on some local stacks.
                    'force_ip_resolve' => 'v4',
                    'version' => 1.1,
                ])
                ->connectTimeout(10)
                ->timeout(30)
                ->retry(2, 400, function (\Exception $exception): bool {
                    return $exception instanceof ConnectionException;
                })
                ->post(self::API, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemInstruction,
                        ],
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                    'max_completion_tokens' => 1200,
                ]);

            if (! $response->successful()) {
                return response()->json([
                    'error' => 'Cerebras request failed.',
                    'details' => $response->json() ?? $response->body(),
                ], 502);
            }

            $assistantMessage = $response->json('choices.0.message.content');
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
            return response()->json([
                'error' => 'Network connection to Cerebras was reset. Please retry.',
                'details' => $exception->getMessage(),
            ], 503);
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => 'Unable to complete chat request.',
                'details' => $exception->getMessage(),
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
            '- Current endpoint behavior: no tools are executed yet in this chat endpoint; provide guidance/answers only.',
        ];

        if ($customPrompt !== '') {
            $context[] = 'User custom system prompt:';
            $context[] = Str::limit($customPrompt, 1200);
        }

        return implode("\n", $context);
    }
}
