<?php

namespace App\Http\Controllers;

use App\Mcp\Tools\DepartmentList;
use App\Mcp\Tools\StudentList;
use App\Models\AutomationSetting;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request as McpRequest;
use League\CommonMark\CommonMarkConverter;

class ChatController extends Controller
{
    private const DEFAULT_MODEL = 'qwen-3-235b-a22b-instruct-2507';
    private const API = 'https://api.cerebras.ai/v1/chat/completions';
    private const MAX_COMPLETION_TOKENS = 512;
    private const OVERLOAD_MAX_COMPLETION_TOKENS = 256;
    private const TEMPERATURE = 0.2;
    private const TOP_P = 1;
    private const RATE_LIMIT_RETRIES = 2;
    private const RATE_LIMIT_BACKOFF_MS = 2000;
    private const RATE_LIMIT_MAX_WAIT_MS = 2500;
    private const MAX_TOOL_ROUNDS = 2;
    private const HISTORY_LIMIT = 3;
    private const HISTORY_MESSAGE_CHAR_LIMIT = 700;
    private const TOOL_RESULT_CHAR_BUDGET = 20000;
    private const TOOL_LIST_HARD_LIMIT = 25;
    private const IN_FLIGHT_LOCK_SECONDS = 10;

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

        $lockKey = $this->buildInFlightLockKey($user, $sessionId, $request->ip() ?? 'unknown');
        if (! Cache::add($lockKey, 1, now()->addSeconds(self::IN_FLIGHT_LOCK_SECONDS))) {
            return response()->json([
                'error' => 'A previous chat request is still processing. Please wait a moment and try again.',
                'session_id' => $sessionId,
            ], 429);
        }

        $automationSettings = $this->getAutomationSettings($user);
        $systemInstruction = $this->buildSystemInstruction($user, $automationSettings);
        $conversationMessages = $this->buildConversationMessages($user, $sessionId, $message, $systemInstruction);
        $enabledToolGroups = $this->getEnabledToolGroupsFromSettings($automationSettings);
        $enabledToolGroups = $this->applyDefaultReadToolGroups($enabledToolGroups);
        $tools = $this->buildToolDefinitions($enabledToolGroups, $user);
        $toolingMessages = $conversationMessages;

        try {
            $fastPath = $this->tryHandleGroundedReadFastPath($message, $enabledToolGroups, $user);
            if ($fastPath !== null) {
                $assistantText = $fastPath;
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
            }

            $assistantMessagePayload = null;
            $finalAssistantMessage = '';
            $toolRounds = 0;

            while ($toolRounds < self::MAX_TOOL_ROUNDS) {
                $payload = [
                    'model' => $model,
                    'messages' => $toolingMessages,
                    'max_completion_tokens' => self::MAX_COMPLETION_TOKENS,
                    'temperature' => self::TEMPERATURE,
                    'top_p' => self::TOP_P,
                ];

                if ($tools !== []) {
                    $payload['tools'] = $tools;
                    $payload['tool_choice'] = $this->decideToolChoice($message, $enabledToolGroups, $tools, $user) ?? 'auto';
                }

                $response = $this->postCompletionWithBackoff($apiKey, $payload);

                if (! $response->successful()) {
                    $status = $response->status();
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
                        'body' => $response->json() ?? $response->body(),
                    ]);

                    return response()->json([
                        'error' => $friendlyError,
                    ], $status === 429 ? 429 : 502);
                }

                $assistantMessagePayload = $response->json('choices.0.message');

                if (! is_array($assistantMessagePayload)) {
                    return response()->json([
                        'error' => 'AI response format was invalid.'], 502);
                }

                $toolCalls = is_array($assistantMessagePayload['tool_calls'] ?? null)
                    ? $assistantMessagePayload['tool_calls']
                    : [];

                if ($toolCalls === []) {
                    $finalAssistantMessage = (string) ($assistantMessagePayload['content'] ?? '');
                    break;
                }

                $toolingMessages[] = $assistantMessagePayload;

                foreach ($toolCalls as $call) {
                    $toolingMessages[] = $this->executeToolCall($call, $enabledToolGroups, $user);
                }

                $toolRounds++;
            }

            if ($assistantMessagePayload === null) {
                return response()->json([
                    'error' => 'AI response was empty.',
                ], 502);
            }

            if ($finalAssistantMessage === '' && isset($assistantMessagePayload['content'])) {
                $finalAssistantMessage = (string) $assistantMessagePayload['content'];
            }

            $assistantText = $this->normalizeAssistantContent($finalAssistantMessage);
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
        } finally {
            Cache::forget($lockKey);
        }
    }

    private function buildInFlightLockKey(mixed $user, string $sessionId, string $ip): string
    {
        $userPart = ($user !== null && isset($user->id)) ? ('u:'.$user->id) : ('ip:'.$ip);

        return 'chat:in_flight:'.$userPart.':'.$sessionId;
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

    /**
     * Retry once on provider 429 queue saturation with short backoff.
     *
     * @param array<string, mixed> $payload
     */
    private function postCompletionWithBackoff(string $apiKey, array $payload): Response
    {
        $attempt = 0;
        $requestPayload = $payload;

        while (true) {
            $response = Http::withToken($apiKey)
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
                ->post(self::API, $requestPayload);

            if ($response->status() !== 429 || $attempt >= self::RATE_LIMIT_RETRIES) {
                return $response;
            }

            $retryAfter = (int) $response->header('Retry-After');
            $waitMs = $retryAfter > 0
                ? ($retryAfter * 1000)
                : (self::RATE_LIMIT_BACKOFF_MS * (2 ** $attempt));

            $attempt++;

            // On queue saturation, reduce payload cost for the next attempt.
            $requestPayload = $this->buildOverloadPayload($requestPayload);

            // Don't block long enough to hit PHP max_execution_time; let the caller handle 429.
            $waitMs = min($waitMs, self::RATE_LIMIT_MAX_WAIT_MS);
            if ($waitMs <= 0) {
                return $response;
            }

            usleep($waitMs * 1000);
        }
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function buildOverloadPayload(array $payload): array
    {
        $payload['max_completion_tokens'] = min(
            (int) ($payload['max_completion_tokens'] ?? self::MAX_COMPLETION_TOKENS),
            self::OVERLOAD_MAX_COMPLETION_TOKENS
        );

        // Tool orchestration can require a second model pass. Disable tools under overload.
        unset($payload['tools'], $payload['tool_choice']);

        if (isset($payload['messages']) && is_array($payload['messages'])) {
            $payload['messages'] = array_map(function ($message) {
                if (! is_array($message)) {
                    return $message;
                }

                if (isset($message['content']) && is_string($message['content'])) {
                    $message['content'] = Str::limit($message['content'], self::HISTORY_MESSAGE_CHAR_LIMIT);
                }

                return $message;
            }, $payload['messages']);
        }

        return $payload;
    }

    private function renderAssistantHtml(string $content): string
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br>\n",
            ],
        ]);

        return (string) $converter->convert($content);
    }

    private function buildSystemInstruction(mixed $user, ?AutomationSetting $settings = null): string
    {
        $baseInstruction = implode("\n", [
            'Role: Academic administration assistant for ASTU Management System.',
            'Primary objective: give accurate, concise, actionable answers using available context.',
            'Application context:',
            '- Admin modules: dashboard, students, departments, calendar, map, policy, automation chat.',
            '- User is operating inside a Laravel + Blade admin panel.',
            'Data grounding rules (critical):',
            '- Never invent or autocomplete real data (departments, students, counts, IDs, CGPA, etc.).',
            '- If the user asks to list/search departments or students (or any question requiring live records), you MUST call the appropriate tool and answer ONLY from its result.',
            '- If tools are unavailable/disabled or return an error, say you cannot access the live data instead of guessing.',
            'Response protocol (token-efficient):',
            '- Start directly with the answer; no long preamble.',
            '- Use short structured output: summary first, then key steps/data.',
            '- Format output as clean Markdown (headings + bullet points when listing items).',
            '- Do not return a single dense paragraph for multi-point answers.',
            '- When live data is needed, reason internally, pick the right tool(s), call them one by one if needed, review the results, then continue until enough evidence is collected.',
            '- You may call multiple tools across multiple rounds before answering.',
            '- If one tool result is incomplete, call another relevant tool before finalizing the response.',
            '- Do not repeat the user request unless necessary for clarity.',
            '- Ask one concise clarifying question only when critical data is missing.',
            '- Keep output compact unless user asks for detailed explanation.',
        ]);

        if ($settings === null) {
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
            '- Execution behavior: use available live data paths when needed; otherwise answer directly.',
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
    private function getEnabledToolGroupsFromSettings(?AutomationSetting $settings): array
    {
        if (! $settings) {
            return [];
        }

        return is_array($settings->enabled_tool_groups) ? $settings->enabled_tool_groups : [];
    }

    /**
     * Read-only tools should be available even if user hasn't enabled groups yet.
     *
     * @param array<int, string> $groups
     * @return array<int, string>
     */
    private function applyDefaultReadToolGroups(array $groups): array
    {
        if ($groups !== []) {
            return $groups;
        }

        return ['departments', 'students'];
    }

    private function getAutomationSettings(mixed $user): ?AutomationSetting
    {
        if ($user === null || ! isset($user->id) || ! Schema::hasTable('automation_settings')) {
            return null;
        }

        return AutomationSetting::query()->where('user_id', $user->id)->first();
    }

    private function tryHandleGroundedReadFastPath(string $message, array $enabledToolGroups, mixed $user): ?string
    {
        $text = mb_strtolower($message);
        $looksLikeList = (bool) preg_match('/\b(list|show|display|all|give me|provide)\b/', $text);
        $looksLikeDetail = (bool) preg_match('/\b(detail|details|information|info|about)\b/', $text);

        if ($looksLikeList && preg_match('/\bdepartment|departments\b/', $text)) {
            $payload = $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, [
                'limit' => self::TOOL_LIST_HARD_LIMIT,
            ]);

            if (isset($payload['error'])) {
                return null;
            }

            $departments = is_array($payload['departments'] ?? null) ? $payload['departments'] : [];
            if ($departments === []) {
                return "No departments found.";
            }

            $lines = ["## Departments",];
            foreach ($departments as $dept) {
                if (is_array($dept) && isset($dept['name'])) {
                    $lines[] = '- '.(string) $dept['name'];
                }
            }

            return implode("\n", $lines);
        }

        if ($looksLikeDetail && preg_match('/\bdepartment|departments\b/', $text)) {
            // If the user didn't specify which department, provide the real list and ask them to choose.
            $payload = $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, [
                'limit' => self::TOOL_LIST_HARD_LIMIT,
            ]);

            if (isset($payload['error'])) {
                return null;
            }

            $departments = is_array($payload['departments'] ?? null) ? $payload['departments'] : [];
            if ($departments === []) {
                return "No departments found.";
            }

            // Try best-effort match: if message contains a department name/code substring, filter via tool search.
            // Otherwise, ask user to pick.
            $q = trim(preg_replace('/\b(give me|show|provide|department|departments|detail|details|information|info|about|please)\b/i', '', $message) ?? '');
            $q = trim($q);

            if ($q !== '') {
                $searchPayload = $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, [
                    'q' => $q,
                    'limit' => 5,
                ]);

                if (! isset($searchPayload['error'])) {
                    $matches = is_array($searchPayload['departments'] ?? null) ? $searchPayload['departments'] : [];
                    if (count($matches) === 1 && is_array($matches[0])) {
                        $dept = $matches[0];
                        $lines = ["## Department details"];
                        $lines[] = '- **Name**: '.(string) ($dept['name'] ?? '—');
                        $lines[] = '- **Code**: '.(string) ($dept['code'] ?? '—');
                        $lines[] = '- **Min GPA**: '.(string) ($dept['min_gpa'] ?? '—');
                        $lines[] = '- **Spot limit**: '.(string) ($dept['spot_limit'] ?? '—');
                        $lines[] = '';
                        $lines[] = 'If you meant a different department, tell me the name/code.';

                        return implode("\n", $lines);
                    }
                }
            }

            $lines = ["Which department do you want details for? Here are the current departments:"];
            foreach ($departments as $dept) {
                if (is_array($dept) && isset($dept['name'], $dept['code'])) {
                    $lines[] = '- '.(string) $dept['name'].' ('.(string) $dept['code'].')';
                } elseif (is_array($dept) && isset($dept['name'])) {
                    $lines[] = '- '.(string) $dept['name'];
                }
            }

            return implode("\n", $lines);
        }

        if ($looksLikeList && preg_match('/\bstudent|students\b/', $text)) {
            $payload = $this->runMcpTool(new StudentList(), $enabledToolGroups, 'students', $user, [
                'limit' => min(10, self::TOOL_LIST_HARD_LIMIT),
            ]);

            if (isset($payload['error'])) {
                return null;
            }

            $students = is_array($payload['students'] ?? null) ? $payload['students'] : [];
            if ($students === []) {
                return "No students found.";
            }

            $lines = ["## Students (sample)"];
            foreach ($students as $student) {
                if (is_array($student) && isset($student['name'], $student['student_id'])) {
                    $lines[] = '- '.(string) $student['name'].' ('.(string) $student['student_id'].')';
                }
            }

            $lines[] = '';
            $lines[] = 'Tip: ask like "search student Bilal" to narrow it down.';

            return implode("\n", $lines);
        }

        return null;
    }

    /**
     * @param array<int, string> $enabledToolGroups
     * @return array<int, array<string, mixed>>
     */
    private function buildToolDefinitions(array $enabledToolGroups, mixed $user): array
    {
        $tools = [];

        if (in_array('departments', $enabledToolGroups, true)) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'department_list',
                    'description' => 'Fetch REAL departments from the database. Use this whenever listing/searching departments. Never guess department names.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string', 'description' => 'Search by department name or code.'],
                            'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-25).'],
                            'cursor_id' => ['type' => 'integer', 'description' => 'Pagination cursor; returns rows with id > cursor_id.'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ];
        }

        if (in_array('students', $enabledToolGroups, true) && $this->isAdminUser($user)) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'student_list',
                    'description' => 'Fetch REAL students from the database. Use this whenever listing/searching students. Never guess student data.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string', 'description' => 'Search by name or student_id.'],
                            'department' => ['type' => 'string', 'description' => 'Filter by department.'],
                            'min_cgpa' => ['type' => 'number', 'description' => 'Minimum CGPA filter.'],
                            'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-25).'],
                            'cursor_id' => ['type' => 'integer', 'description' => 'Pagination cursor; returns rows with id > cursor_id.'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ];
        }

        return $tools;
    }

    /**
     * Best-effort forcing of tool call for grounded "list/search" requests.
     *
     * @param array<int, array<string, mixed>> $tools
     * @return array<string, mixed>|string|null
     */
    private function decideToolChoice(string $message, array $enabledToolGroups, array $tools, mixed $user): array|string|null
    {
        if ($tools === []) {
            return null;
        }

        $text = mb_strtolower($message);
        $looksLikeList = (bool) preg_match('/\b(list|show|display|all|give me|provide)\b/', $text);

        if ($looksLikeList && in_array('departments', $enabledToolGroups, true) && preg_match('/\bdepartment|departments\b/', $text)) {
            return [
                'type' => 'function',
                'function' => ['name' => 'department_list'],
            ];
        }

        if ($looksLikeList && in_array('students', $enabledToolGroups, true) && $this->isAdminUser($user) && preg_match('/\bstudent|students\b/', $text)) {
            return [
                'type' => 'function',
                'function' => ['name' => 'student_list'],
            ];
        }

        return 'auto';
    }

    /**
     * @param array<string, mixed> $call
     * @param array<int, string> $enabledToolGroups
    * @param mixed $user
     * @return array<string, string>
     */
    private function executeToolCall(array $call, array $enabledToolGroups, mixed $user): array
    {
        $callId = (string) ($call['id'] ?? Str::uuid());
        $function = is_array($call['function'] ?? null) ? $call['function'] : [];
        $functionName = (string) ($function['name'] ?? '');
        $rawArgs = (string) ($function['arguments'] ?? '{}');
        $decodedArgs = json_decode($rawArgs, true);
        $args = is_array($decodedArgs) ? $decodedArgs : [];

        $resultPayload = match ($functionName) {
            'department_list' => $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, $args),
            'student_list' => $this->runMcpTool(new StudentList(), $enabledToolGroups, 'students', $user, $args),
            default => [
                'error' => 'Unknown tool requested: '.$functionName,
            ],
        };

        $resultPayload = $this->compactToolResult($resultPayload);

        return [
            'role' => 'tool',
            'tool_call_id' => $callId,
            'content' => json_encode($resultPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"error":"Tool result encoding failed."}',
        ];
    }

    /**
     * Ensure tool results never explode prompt size.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function compactToolResult(array $payload): array
    {
        if (isset($payload['students']) && is_array($payload['students'])) {
            $payload['students'] = array_slice($payload['students'], 0, self::TOOL_LIST_HARD_LIMIT);
            $payload['count'] = isset($payload['count']) ? (int) $payload['count'] : count($payload['students']);
        }

        if (isset($payload['departments']) && is_array($payload['departments'])) {
            $payload['departments'] = array_slice($payload['departments'], 0, self::TOOL_LIST_HARD_LIMIT);
            $payload['count'] = isset($payload['count']) ? (int) $payload['count'] : count($payload['departments']);
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($encoded) && strlen($encoded) > self::TOOL_RESULT_CHAR_BUDGET) {
            return [
                'error' => 'Tool result too large; refine your query with q/limit/cursor filters.',
                'hint' => 'Try q (search), department, min_cgpa, or pagination via cursor_id.',
            ];
        }

        return $payload;
    }

    /**
     * Execute MCP tool class directly to avoid duplicating tool logic in chat controller.
     *
     * @param object $tool
     * @param array<int, string> $enabledToolGroups
    * @param mixed $user
     * @param array<string, mixed> $args
     * @return array<string, mixed>
     */
    private function runMcpTool(object $tool, array $enabledToolGroups, string $requiredGroup, mixed $user, array $args = []): array
    {
        if (! in_array($requiredGroup, $enabledToolGroups, true)) {
            return [
                'error' => ucfirst($requiredGroup).' tool is disabled in settings.',
            ];
        }

        try {
            $mcpRequest = new McpRequest(array_merge(['user' => $user], $args));

            if (method_exists($tool, 'handle')) {
                $mcpResponse = $tool->handle($mcpRequest);

                if (method_exists($mcpResponse, 'content')) {
                    $content = $mcpResponse->content();

                    $normalized = $this->normalizeMcpContent($content);
                    if ($normalized !== null) {
                        return $normalized;
                    }
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('MCP tool execution failed in chat', [
                'tool' => get_class($tool),
                'message' => $exception->getMessage(),
            ]);

            return [
                'error' => 'Tool execution failed for '.$requiredGroup.'.',
            ];
        }

        return [
            'error' => 'Tool returned no usable content.',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeMcpContent(mixed $content): ?array
    {
        if (is_array($content)) {
            return $content;
        }

        if (is_string($content)) {
            $decoded = json_decode($content, true);

            if (is_array($decoded)) {
                return $decoded;
            }

            return ['result' => $content];
        }

        if (is_object($content)) {
            // Laravel MCP tools commonly return Text content objects where the useful
            // payload is a JSON string in __toString() or in toArray()['text'].
            if (method_exists($content, '__toString')) {
                $asString = (string) $content;
                if ($asString !== '') {
                    $decodedString = json_decode($asString, true);
                    if (is_array($decodedString)) {
                        return $decodedString;
                    }
                }
            }

            if (method_exists($content, 'toArray')) {
                $array = $content->toArray();
                if (is_array($array)) {
                    if (isset($array['text']) && is_string($array['text'])) {
                        $decodedText = json_decode($array['text'], true);
                        if (is_array($decodedText)) {
                            return $decodedText;
                        }
                    }

                    return $array;
                }
            }

            if ($content instanceof \JsonSerializable) {
                $serialized = $content->jsonSerialize();
                if (is_array($serialized)) {
                    return $serialized;
                }
            }

            $decodedObject = json_decode(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true);
            if (is_array($decodedObject)) {
                return $decodedObject;
            }
        }

        return null;
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
                    'content' => Str::limit((string) $item->content, self::HISTORY_MESSAGE_CHAR_LIMIT),
                ];
            }
        }

        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }

    private function isAdminUser(mixed $user): bool
    {
        if (! $user instanceof User) {
            return false;
        }

        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        if (isset($user->is_admin)) {
            return (bool) $user->is_admin;
        }

        if (isset($user->role)) {
            return strtolower((string) $user->role) === 'admin';
        }

        if (isset($user->user_type)) {
            return strtolower((string) $user->user_type) === 'admin';
        }

        // Fallback for projects without explicit role schema yet.
        return (int) $user->id === 1;
    }
}
