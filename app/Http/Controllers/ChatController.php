<?php

namespace App\Http\Controllers;

use App\Mcp\Tools\DepartmentList;
use App\Mcp\Tools\PolicyList;
use App\Mcp\Tools\StudentList;
use App\Models\AutomationSetting;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request as McpRequest;
use League\CommonMark\CommonMarkConverter;

class ChatController extends Controller
{
    protected const DEFAULT_MODEL = 'qwen-3-235b-a22b-instruct-2507';
    protected const API = 'https://api.cerebras.ai/v1/chat/completions';
    protected const MAX_COMPLETION_TOKENS = 512;
    protected const OVERLOAD_MAX_COMPLETION_TOKENS = 256;
    protected const TEMPERATURE = 0.2;
    protected const TOP_P = 1;
    protected const RATE_LIMIT_RETRIES = 2;
    protected const RATE_LIMIT_BACKOFF_MS = 2000;
    protected const RATE_LIMIT_MAX_WAIT_MS = 2500;
    protected const MAX_TOOL_ROUNDS = 2;
    protected const HISTORY_LIMIT = 3;
    protected const HISTORY_MESSAGE_CHAR_LIMIT = 700;
    protected const TOOL_RESULT_CHAR_BUDGET = 20000;
    protected const TOOL_LIST_HARD_LIMIT = 25;
    protected const STUDENT_TOOL_LIST_HARD_LIMIT = 100;
    protected const FAST_PATH_STUDENT_DEFAULT_LIMIT = 10;
    protected const IN_FLIGHT_LOCK_SECONDS = 10;

    protected function buildInFlightLockKey(mixed $user, string $sessionId, string $ip): string
    {
        $userPart = ($user !== null && isset($user->id)) ? ('u:'.$user->id) : ('ip:'.$ip);

        return 'chat:in_flight:'.$userPart.':'.$sessionId;
    }

    protected function buildStudentContextPrompt(mixed $student, string $message): string
    {
        if (! is_object($student)) {
            return 'Student profile context is unavailable. Message: '.$message;
        }

        $name = (string) ($student->name ?? 'Student');
        $department = (string) ($student->department ?? 'Unknown Department');
        $year = (string) ($student->current_year ?? 'Unknown Year');
        $semester = (string) ($student->current_semester ?? 'Unknown Semester');

        return implode("\n", [
            'Student profile context:',
            '- Name: '.$name,
            '- Department: '.$department,
            '- Current Year: '.$year,
            '- Current Semester: '.$semester,
            '',
            'Student message: '.$message,
        ]);
    }

    protected function normalizeAssistantContent(mixed $content): string
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

    protected function getLlmProvider(): string
    {
        $provider = strtolower((string) config('services.llm.provider', 'cerebras'));

        return in_array($provider, ['cerebras', 'zydit'], true) ? $provider : 'cerebras';
    }

    protected function getLlmApiKey(): string
    {
        $provider = $this->getLlmProvider();

        if ($provider === 'zydit') {
            return (string) config('services.zydit.key', '');
        }

        return (string) config('services.cerebras.key', '');
    }

    protected function getLlmEndpoint(): string
    {
        $provider = $this->getLlmProvider();

        if ($provider === 'zydit') {
            return (string) config('services.zydit.endpoint', 'https://api.zydit.in/v1/chat/completions');
        }

        return self::API;
    }

    protected function getLlmModel(): string
    {
        $provider = $this->getLlmProvider();

        if ($provider === 'zydit') {
            return (string) config('services.zydit.model', 'z-ai/glm5');
        }

        return (string) config('services.cerebras.model', self::DEFAULT_MODEL);
    }

    /**
     * Retry once on provider 429 queue saturation with short backoff.
     *
     * @param array<string, mixed> $payload
     */
    protected function postCompletionWithBackoff(string $apiKey, array $payload): Response
    {
        $attempt = 0;
        $requestPayload = $payload;
        $endpoint = $this->getLlmEndpoint();

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
                ->post($endpoint, $requestPayload);

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
    protected function buildOverloadPayload(array $payload): array
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

    protected function renderAssistantHtml(string $content): string
    {
        $protectedLatexSegments = [];
        $markdownContent = $this->protectLatexSegments(
            str_replace(["\r\n", "\r"], "\n", $content),
            $protectedLatexSegments
        );

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br>\n",
            ],
        ]);

        $html = (string) $converter->convert($markdownContent);

        return $this->restoreProtectedLatexSegments($html, $protectedLatexSegments);
    }

    /**
     * Protect LaTeX segments so Markdown formatting does not alter the math source.
     *
     * @param array<string, string> $protectedLatexSegments
     */
    protected function protectLatexSegments(string $content, array &$protectedLatexSegments): string
    {
        $result = '';
        $offset = 0;
        $length = strlen($content);

        while ($offset < $length) {
            $segment = $this->extractLatexSegment($content, $offset);

            if ($segment === null) {
                $result .= $content[$offset];
                $offset++;

                continue;
            }

            $placeholder = 'ASTU_MATH_'.count($protectedLatexSegments).'_TOKEN';
            $protectedLatexSegments[$placeholder] = e($segment['content']);
            $result .= $placeholder;
            $offset = $segment['end'];
        }

        return $result;
    }

    /**
     * @return array{content:string,end:int}|null
     */
    protected function extractLatexSegment(string $content, int $offset): ?array
    {
        $delimiters = [
            ['open' => '$$', 'close' => '$$', 'multiline' => true],
            ['open' => '\\[', 'close' => '\\]', 'multiline' => true],
            ['open' => '\\(', 'close' => '\\)', 'multiline' => false],
        ];

        foreach ($delimiters as $delimiter) {
            $open = $delimiter['open'];

            if (! str_starts_with(substr($content, $offset), $open)) {
                continue;
            }

            $start = $offset + strlen($open);
            $closingOffset = $this->findLatexClosingDelimiter(
                $content,
                $start,
                $delimiter['close'],
                $delimiter['multiline']
            );

            if ($closingOffset === null) {
                return null;
            }

            $end = $closingOffset + strlen($delimiter['close']);

            return [
                'content' => substr($content, $offset, $end - $offset),
                'end' => $end,
            ];
        }

        if (preg_match('/\\\\begin\{([a-z*]+)\}/A', substr($content, $offset), $matches) === 1) {
            $environment = (string) ($matches[1] ?? '');
            $opening = (string) ($matches[0] ?? '');
            $closing = '\\end{'.$environment.'}';
            $start = $offset + strlen($opening);
            $closingOffset = strpos($content, $closing, $start);

            if ($closingOffset !== false) {
                $end = $closingOffset + strlen($closing);

                return [
                    'content' => substr($content, $offset, $end - $offset),
                    'end' => $end,
                ];
            }
        }

        if (($content[$offset] ?? '') !== '$' || ($content[$offset + 1] ?? '') === '$' || $this->isEscapedOffset($content, $offset)) {
            return null;
        }

        $nextCharacter = $content[$offset + 1] ?? '';
        if ($nextCharacter === '' || preg_match('/\s/', $nextCharacter) === 1) {
            return null;
        }

        $closingOffset = $this->findInlineDollarClosingDelimiter($content, $offset + 1);

        if ($closingOffset === null) {
            return null;
        }

        return [
            'content' => substr($content, $offset, ($closingOffset + 1) - $offset),
            'end' => $closingOffset + 1,
        ];
    }

    protected function findLatexClosingDelimiter(string $content, int $offset, string $closeDelimiter, bool $allowMultiline): ?int
    {
        $length = strlen($content);
        $delimiterLength = strlen($closeDelimiter);

        while ($offset < $length) {
            if (! $allowMultiline && ($content[$offset] ?? '') === "\n") {
                return null;
            }

            if (substr($content, $offset, $delimiterLength) === $closeDelimiter && ! $this->isEscapedOffset($content, $offset)) {
                return $offset;
            }

            $offset++;
        }

        return null;
    }

    protected function findInlineDollarClosingDelimiter(string $content, int $offset): ?int
    {
        $length = strlen($content);

        while ($offset < $length) {
            $character = $content[$offset] ?? '';

            if ($character === "\n") {
                return null;
            }

            if ($character === '$' && ! $this->isEscapedOffset($content, $offset)) {
                $previousCharacter = $content[$offset - 1] ?? '';

                if ($previousCharacter !== '' && preg_match('/\s/', $previousCharacter) !== 1) {
                    return $offset;
                }
            }

            $offset++;
        }

        return null;
    }

    protected function isEscapedOffset(string $content, int $offset): bool
    {
        $backslashCount = 0;
        $cursor = $offset - 1;

        while ($cursor >= 0 && ($content[$cursor] ?? '') === '\\') {
            $backslashCount++;
            $cursor--;
        }

        return $backslashCount % 2 === 1;
    }

    /**
     * @param array<string, string> $protectedLatexSegments
     */
    protected function restoreProtectedLatexSegments(string $html, array $protectedLatexSegments): string
    {
        if ($protectedLatexSegments === []) {
            return $html;
        }

        return strtr($html, $protectedLatexSegments);
    }

    protected function buildSystemInstruction(mixed $user, ?AutomationSetting $settings = null): string
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
            '- Student current_year means academic level (1-5), not calendar year.',
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
    protected function getEnabledToolGroupsFromSettings(?AutomationSetting $settings): array
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
    protected function applyDefaultReadToolGroups(array $groups, mixed $user): array
    {
        if ($groups !== []) {
            return $groups;
        }

        if ($this->isAdminUser($user)) {
            return ['departments', 'students', 'policies'];
        }

        return ['departments'];
    }

    /**
     * Remove tool groups the current user cannot access to prevent confusing tool-disabled behavior.
     *
     * @param array<int, string> $groups
     * @return array<int, string>
     */
    protected function filterToolGroupsForUser(array $groups, mixed $user): array
    {
        $groups = array_values(array_unique($groups));

        if ($this->isAdminUser($user)) {
            return $groups;
        }

        return array_values(array_filter($groups, static fn (string $group): bool => $group === 'departments'));
    }

    protected function getAutomationSettings(mixed $user): ?AutomationSetting
    {
        if ($user === null || ! isset($user->id) || ! Schema::hasTable('automation_settings')) {
            return null;
        }

        return AutomationSetting::query()->where('user_id', $user->id)->first();
    }

    protected function tryHandleGroundedReadFastPath(string $message, array $enabledToolGroups, mixed $user): ?string
    {
        $text = mb_strtolower($message);
        $looksLikeList = (bool) preg_match('/\b(list|show|display|all|give me|provide)\b/', $text);
        $looksLikeDetail = (bool) preg_match('/\b(detail|details|information|info|about)\b/', $text);
        $looksLikeExplain = (bool) preg_match('/\b(explain|why|how|summari[sz]e|interpret|impact|meaning|describe)\b/', $text);
        $mentionsDepartments = (bool) preg_match('/\bdepartment|departments\b/', $text);
        $mentionsPolicies = (bool) preg_match('/\b(policy|policies|policys|rule|rules|regulation|regulations)\b/', $text);
        $requestedPolicyId = $this->extractRequestedPolicyId($message);
        $hasExplicitPolicyIdRequest = $requestedPolicyId !== null;

        if ($looksLikeExplain && $mentionsPolicies && ! $hasExplicitPolicyIdRequest) {
            $payload = $this->runMcpTool(new PolicyList(), $enabledToolGroups, 'policies', $user, [
                'question' => $message,
                'active_only' => true,
                'limit' => 8,
                'sort_by' => 'updated_at',
                'sort_order' => 'desc',
            ]);

            if (! isset($payload['error'])) {
                $policies = is_array($payload['policies'] ?? null) ? $payload['policies'] : [];

                if ($policies !== []) {
                    $lines = ['## Policy explanation (grounded)'];
                    $lines[] = 'Based on current policy records, here are the key rules:';
                    $lines[] = '';

                    foreach ($policies as $policy) {
                        if (! is_array($policy) || ! isset($policy['title'])) {
                            continue;
                        }

                        $lines[] = '- **'.(string) $policy['title'].'** ('.(string) ($policy['category'] ?? 'General').')';

                        if (isset($policy['content']) && is_string($policy['content'])) {
                            $lines[] = '  - '.Str::limit($policy['content'], 220);
                        }
                    }

                    $lines[] = '';
                    $lines[] = 'If you want, ask for a specific policy id (for example: "explain policy 1") for full detail.';

                    return implode("\n", $lines);
                }
            }
        }

        // Let the LLM compose explanatory answers from tool evidence instead of returning a direct fast-path template.
        if ($looksLikeExplain && ! $hasExplicitPolicyIdRequest) {
            return null;
        }

        if ($looksLikeList && $mentionsDepartments && $mentionsPolicies) {
            $departmentPayload = $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, [
                'limit' => self::TOOL_LIST_HARD_LIMIT,
                'sort_by' => 'name',
                'sort_order' => 'asc',
            ]);

            if (isset($departmentPayload['error'])) {
                return null;
            }

            $policyPayload = null;
            if (in_array('policies', $enabledToolGroups, true)) {
                $policyPayload = $this->runMcpTool(new PolicyList(), $enabledToolGroups, 'policies', $user, [
                    'limit' => self::TOOL_LIST_HARD_LIMIT,
                    'sort_by' => 'id',
                    'sort_order' => 'asc',
                    'active_only' => true,
                ]);

                if (isset($policyPayload['error'])) {
                    $policyPayload = null;
                }
            }

            $departments = is_array($departmentPayload['departments'] ?? null) ? $departmentPayload['departments'] : [];
            $policies = is_array($policyPayload['policies'] ?? null) ? $policyPayload['policies'] : [];

            if ($departments === [] && $policies === []) {
                return 'No departments or policies found.';
            }

            $lines = [];

            if ($departments !== []) {
                $lines[] = '## Departments';
                foreach ($departments as $department) {
                    if (! is_array($department) || ! isset($department['name'])) {
                        continue;
                    }

                    $line = '- '.(string) $department['name'];
                    if (isset($department['code'])) {
                        $line .= ' ('.(string) $department['code'].')';
                    }

                    $lines[] = $line;
                }
            }

            if ($policies !== []) {
                $lines[] = '';
                $lines[] = '## Policies';
                foreach ($policies as $policy) {
                    if (! is_array($policy) || ! isset($policy['title'])) {
                        continue;
                    }

                    $line = '- **'.(string) $policy['title'].'**';
                    if (isset($policy['category'])) {
                        $line .= ' ('.(string) $policy['category'].')';
                    }

                    $lines[] = $line;
                }
            } elseif ($mentionsPolicies) {
                $lines[] = '';
                $lines[] = '## Policies';
                $lines[] = '- No matching policy records were found.';
            }

            return implode("\n", $lines);
        }

        if ($looksLikeList && preg_match('/\bdepartment|departments\b/', $text)) {
            $payload = $this->runMcpTool(new DepartmentList(), $enabledToolGroups, 'departments', $user, [
                'limit' => self::TOOL_LIST_HARD_LIMIT,
                'sort_by' => 'name',
                'sort_order' => 'asc',
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
            $requestedLimit = $this->extractRequestedStudentLimit($message);
            $payload = $this->runMcpTool(new StudentList(), $enabledToolGroups, 'students', $user, [
                'limit' => $requestedLimit,
            ]);

            if (isset($payload['error'])) {
                return null;
            }

            $students = is_array($payload['students'] ?? null) ? $payload['students'] : [];
            if ($students === []) {
                return "No students found.";
            }

            $lines = ['## Students ('.count($students).')'];
            foreach ($students as $student) {
                if (is_array($student) && isset($student['name'], $student['student_id'])) {
                    $lines[] = '- '.(string) $student['name'].' ('.(string) $student['student_id'].')';
                }
            }

            if ($requestedLimit >= self::STUDENT_TOOL_LIST_HARD_LIMIT) {
                $lines[] = '';
                $lines[] = 'Note: Student list is capped at '.self::STUDENT_TOOL_LIST_HARD_LIMIT.' records per request.';
            }

            return implode("\n", $lines);
        }

        $looksLikeStudentCgpaFilter = (bool) preg_match('/\b(cgpa|gpa)\b/', $text)
            && (bool) preg_match('/\bstudent|students|sstudent|sstudents\b/', $text);
        if ($looksLikeStudentCgpaFilter) {
            $minCgpa = $this->extractRequestedMinCgpa($message);

            if ($minCgpa !== null) {
                $payload = $this->runMcpTool(new StudentList(), $enabledToolGroups, 'students', $user, [
                    'min_cgpa' => $minCgpa,
                    'sort_by' => 'cgpa',
                    'sort_order' => 'desc',
                    'limit' => self::TOOL_LIST_HARD_LIMIT,
                ]);

                if (isset($payload['error'])) {
                    return null;
                }

                $students = is_array($payload['students'] ?? null) ? $payload['students'] : [];
                if ($students === []) {
                    return 'No students found for CGPA >= '.$minCgpa.'.';
                }

                $lines = ['## Students with CGPA >= '.$minCgpa.' ('.count($students).')'];
                foreach ($students as $student) {
                    if (! is_array($student) || ! isset($student['name'], $student['student_id'])) {
                        continue;
                    }

                    $cgpa = isset($student['cgpa']) ? (string) $student['cgpa'] : '—';
                    $lines[] = '- '.(string) $student['name'].' ('.(string) $student['student_id'].') - CGPA: '.$cgpa;
                }

                return implode("\n", $lines);
            }
        }

        if (preg_match('/\b(policy|policies|policys|rule|rules|regulation|regulations)\b/', $text)) {
            if ($requestedPolicyId !== null) {
                $policyId = $requestedPolicyId;

                if ($policyId > 0) {
                    $detailPayload = $this->runMcpTool(new PolicyList(), $enabledToolGroups, 'policies', $user, [
                        'id' => $policyId,
                        'active_only' => false,
                        'limit' => 1,
                    ]);

                    if (! isset($detailPayload['error'])) {
                        $matches = is_array($detailPayload['policies'] ?? null) ? $detailPayload['policies'] : [];
                        if (count($matches) === 1 && is_array($matches[0])) {
                            $policy = $matches[0];
                            $lines = ['## Policy #'.$policyId];
                            $lines[] = '- **Title**: '.(string) ($policy['title'] ?? '—');
                            $lines[] = '- **Category**: '.(string) ($policy['category'] ?? 'General');
                            $lines[] = '- **Status**: '.((bool) ($policy['is_active'] ?? false) ? 'Active' : 'Inactive');
                            $lines[] = '';
                            $lines[] = '### Content';
                            $lines[] = (string) ($policy['content'] ?? 'No content available.');

                            return implode("\n", $lines);
                        }
                    }

                    return 'Policy #'.$policyId.' was not found.';
                }
            }

            $payload = $this->runMcpTool(new PolicyList(), $enabledToolGroups, 'policies', $user, [
                'question' => $message,
                'active_only' => true,
                'limit' => self::TOOL_LIST_HARD_LIMIT,
                'sort_by' => 'id',
                'sort_order' => 'asc',
            ]);

            if (isset($payload['error'])) {
                return null;
            }

            $policies = is_array($payload['policies'] ?? null) ? $payload['policies'] : [];
            if ($policies === []) {
                return 'No matching policies found.';
            }

            $lines = ['## Relevant policies'];
            foreach ($policies as $policy) {
                if (! is_array($policy) || ! isset($policy['title'])) {
                    continue;
                }

                $lines[] = '- **'.(string) $policy['title'].'** ('.(string) ($policy['category'] ?? 'General').')';
                if (isset($policy['content']) && is_string($policy['content'])) {
                    $lines[] = '  - '.Str::limit($policy['content'], 180);
                }
            }

            return implode("\n", $lines);
        }

        return null;
    }

    /**
     * @param array<int, string> $enabledToolGroups
     * @return array<int, array<string, mixed>>
     */
    protected function buildToolDefinitions(array $enabledToolGroups, mixed $user): array
    {
        $tools = [];

        if (in_array('departments', $enabledToolGroups, true)) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'department_list',
                    'description' => 'Fetch REAL departments from the database. For list-all requests omit q. For sorted output set sort_by + sort_order. Use cursor_id pagination when sort_by=id.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string', 'description' => 'Search by department name or code.'],
                            'sort_by' => ['type' => 'string', 'description' => 'Sort field: id, name, code, min_gpa, or spot_limit.'],
                            'sort_order' => ['type' => 'string', 'description' => 'Sort direction: asc or desc.'],
                            'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-100).'],
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
                    'description' => 'Fetch REAL students from the database. For list-all requests omit q and set limit. For ranking questions (top/highest score), use study_year with sort_by=cgpa and sort_order=desc.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string', 'description' => 'Search by name or student_id.'],
                            'department' => ['type' => 'string', 'description' => 'Filter by department.'],
                            'study_year' => ['type' => 'integer', 'description' => 'Academic year level filter (1-5), not a calendar year.'],
                            'min_cgpa' => ['type' => 'number', 'description' => 'Minimum CGPA filter.'],
                            'sort_by' => ['type' => 'string', 'description' => 'Sort field: id, cgpa, current_year, or name.'],
                            'sort_order' => ['type' => 'string', 'description' => 'Sort direction: asc or desc.'],
                            'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-100).'],
                            'cursor_id' => ['type' => 'integer', 'description' => 'Pagination cursor; returns rows with id > cursor_id.'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ];
        }

        if (in_array('policies', $enabledToolGroups, true) && $this->isAdminUser($user)) {
            $tools[] = [
                'type' => 'function',
                'function' => [
                    'name' => 'policy_list',
                    'description' => 'Fetch REAL policy records. Use id for specific policy detail, question for relevance matching, and sort_by/sort_order for deterministic ordering.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string', 'description' => 'Search by title/category/content.'],
                            'id' => ['type' => 'integer', 'description' => 'Fetch a specific policy by id.'],
                            'question' => ['type' => 'string', 'description' => 'Original student question for relevance filtering.'],
                            'category' => ['type' => 'string', 'description' => 'Exact category filter.'],
                            'active_only' => ['type' => 'boolean', 'description' => 'Return only active policies. Defaults true.'],
                            'sort_by' => ['type' => 'string', 'description' => 'Sort field: id, updated_at, title, or category.'],
                            'sort_order' => ['type' => 'string', 'description' => 'Sort direction: asc or desc.'],
                            'limit' => ['type' => 'integer', 'description' => 'Max rows to return (1-100).'],
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
    protected function decideToolChoice(string $message, array $enabledToolGroups, array $tools, mixed $user): array|string|null
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

        if (in_array('students', $enabledToolGroups, true) && $this->isAdminUser($user) && preg_match('/\b(top|highest|lowest|best|rank|ranking|score|cgpa|gpa)\b/', $text)) {
            return [
                'type' => 'function',
                'function' => ['name' => 'student_list'],
            ];
        }

        if (in_array('policies', $enabledToolGroups, true) && $this->isAdminUser($user) && preg_match('/\bpolicy|policies|rule|rules|regulation|regulations\b/', $text)) {
            return [
                'type' => 'function',
                'function' => ['name' => 'policy_list'],
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
    protected function executeToolCall(array $call, array $enabledToolGroups, mixed $user): array
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
            'policy_list' => $this->runMcpTool(new PolicyList(), $enabledToolGroups, 'policies', $user, $args),
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
    protected function compactToolResult(array $payload): array
    {
        if (isset($payload['students']) && is_array($payload['students'])) {
            $payload['students'] = array_slice($payload['students'], 0, self::STUDENT_TOOL_LIST_HARD_LIMIT);
            $payload['count'] = isset($payload['count']) ? (int) $payload['count'] : count($payload['students']);
        }

        if (isset($payload['departments']) && is_array($payload['departments'])) {
            $payload['departments'] = array_slice($payload['departments'], 0, self::TOOL_LIST_HARD_LIMIT);
            $payload['count'] = isset($payload['count']) ? (int) $payload['count'] : count($payload['departments']);
        }

        if (isset($payload['policies']) && is_array($payload['policies'])) {
            $payload['policies'] = array_slice($payload['policies'], 0, self::TOOL_LIST_HARD_LIMIT);
            $payload['count'] = isset($payload['count']) ? (int) $payload['count'] : count($payload['policies']);
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
    protected function runMcpTool(object $tool, array $enabledToolGroups, string $requiredGroup, mixed $user, array $args = []): array
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
    protected function normalizeMcpContent(mixed $content): ?array
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
    protected function buildConversationMessages(mixed $user, string $sessionId, string $currentMessage, string $systemInstruction): array
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

    protected function isAdminUser(mixed $user): bool
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

    protected function extractRequestedStudentLimit(string $message): int
    {
        if (preg_match('/\b(\d{1,3})\s+students?\b/i', $message, $matches)) {
            $requested = (int) ($matches[1] ?? self::FAST_PATH_STUDENT_DEFAULT_LIMIT);

            return max(1, min(self::STUDENT_TOOL_LIST_HARD_LIMIT, $requested));
        }

        return self::FAST_PATH_STUDENT_DEFAULT_LIMIT;
    }

    protected function extractRequestedMinCgpa(string $message): ?float
    {
        if (preg_match('/\b(?:more than|greater than|above|over|at least|min(?:imum)?|>=|>)\s*(\d(?:\.\d{1,2})?)\b/i', $message, $matches)) {
            $value = (float) ($matches[1] ?? 0);

            return max(0.0, min(4.0, $value));
        }

        if (preg_match('/\b(\d(?:\.\d{1,2})?)\s*(?:\+)?\s*(?:cgpa|gpa)\b/i', $message, $matches)) {
            $value = (float) ($matches[1] ?? 0);

            return max(0.0, min(4.0, $value));
        }

        return null;
    }

    protected function extractRequestedPolicyId(string $message): ?int
    {
        if (preg_match('/\b(?:policy|policies|policys|rule|rules|regulation|regulations)\s*#?\s*(\d+)\b/i', $message, $matches)) {
            $policyId = (int) ($matches[1] ?? 0);

            return $policyId > 0 ? $policyId : null;
        }

        return null;
    }
}
