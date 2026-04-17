<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\ChatController;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StudentChatController extends ChatController
{
    public function __invoke(Request $request, ChatController $chatController): JsonResponse
    {
        return $this->handle($request);
    }

    private function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
            'session_id' => ['nullable', 'string', 'max:100'],
            'mode' => ['nullable', 'in:assistant,guide'],
            'history' => ['nullable', 'array'],
            'history.*.role' => ['required_with:history', 'string', 'in:user,assistant'],
            'history.*.content' => ['required_with:history', 'string'],
        ]);

        $mode = (string) ($validated['mode'] ?? 'assistant');

        if ($mode === 'guide') {
            return $this->handleGuideMode($request, $validated);
        }

        return $this->handleAssistantMode($request, $validated);
    }

    /**
     * @param array{message:string,session_id?:string|null,mode?:string,history?:array<int,array{role:string,content:string}>} $validated
     */
    private function handleAssistantMode(Request $request, array $validated): JsonResponse
    {

        $provider = $this->getLlmProvider();
        $apiKey = $this->getLlmApiKey();
        $model = $this->getLlmModel();

        if (trim($apiKey) === '') {
            return response()->json([
                'error' => ucfirst($provider).' API key is missing. Configure services.'. $provider .'.key.',
            ], 500);
        }

        $student = $request->user('student');
        $message = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();
        $enabledToolGroups = ['departments', 'policies'];

        $systemInstruction = implode("\n", [
            'Role: Student assistant for ASTU Management System.',
            'Allowed tools: department_list and policy_list only.',
            'Never invent department or policy data; use tools when needed.',
            'Keep answers concise and clear for students.',
        ]);

        $toolingMessages = [
            [
                'role' => 'system',
                'content' => $systemInstruction,
            ],
            [
                'role' => 'user',
                'content' => $this->buildStudentContextPrompt($student, $message),
            ],
        ];

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'department_list',
                    'description' => 'Fetch real department records from the database.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string'],
                            'sort_by' => ['type' => 'string'],
                            'sort_order' => ['type' => 'string'],
                            'limit' => ['type' => 'integer'],
                            'cursor_id' => ['type' => 'integer'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'policy_list',
                    'description' => 'Fetch real policy records from the database.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'q' => ['type' => 'string'],
                            'id' => ['type' => 'integer'],
                            'question' => ['type' => 'string'],
                            'category' => ['type' => 'string'],
                            'active_only' => ['type' => 'boolean'],
                            'sort_by' => ['type' => 'string'],
                            'sort_order' => ['type' => 'string'],
                            'limit' => ['type' => 'integer'],
                            'cursor_id' => ['type' => 'integer'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ];

        try {
            $fastPath = $this->tryHandleGroundedReadFastPath($message, $enabledToolGroups, $student);
            if ($fastPath !== null) {
                return response()->json([
                    'message' => $fastPath,
                    'message_html' => $this->renderAssistantHtml($fastPath),
                    'session_id' => $sessionId,
                ]);
            }

            $assistantMessagePayload = null;
            $finalAssistantMessage = '';
            $toolRounds = 0;

            while ($toolRounds < self::MAX_TOOL_ROUNDS) {
                $response = $this->postCompletionWithBackoff($apiKey, [
                    'model' => $model,
                    'messages' => $toolingMessages,
                    'max_completion_tokens' => self::MAX_COMPLETION_TOKENS,
                    'temperature' => self::TEMPERATURE,
                    'top_p' => self::TOP_P,
                    'tools' => $tools,
                    'tool_choice' => 'auto',
                ]);

                if (! $response->successful()) {
                    $status = $response->status();
                    $friendlyError = 'Unable to complete the request.';

                    if ($status === 401) {
                        $friendlyError = 'Invalid API key.';
                    } elseif ($status === 429) {
                        $friendlyError = 'AI service is busy right now. Please try again.';
                    } elseif ($status >= 500) {
                        $friendlyError = 'AI service is temporarily unavailable.';
                    }

                    return response()->json([
                        'error' => $friendlyError,
                    ], $status === 429 ? 429 : 502);
                }

                $assistantMessagePayload = $response->json('choices.0.message');

                if (! is_array($assistantMessagePayload)) {
                    return response()->json([
                        'error' => 'AI response format was invalid.',
                    ], 502);
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
                    $toolingMessages[] = $this->executeToolCall($call, $enabledToolGroups, $student);
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

            if (trim($assistantText) === '') {
                $assistantText = 'I could not generate a complete response. Please retry your request.';
            }

            return response()->json([
                'message' => $assistantText,
                'message_html' => $this->renderAssistantHtml($assistantText),
                'session_id' => $sessionId,
            ]);
        } catch (ConnectionException $exception) {
            return response()->json([
                'error' => 'Network connection to Cerebras was reset. Please retry.',
            ], 503);
        } catch (\Throwable $exception) {
            return response()->json([
                'error' => 'Unable to complete chat request.',
            ], 500);
        }
    }

    /**
     * @param array{message:string,session_id?:string|null,mode?:string,history?:array<int,array{role:string,content:string}>} $validated
     */
    private function handleGuideMode(Request $request, array $validated): JsonResponse
    {
        $message = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();
        $endpoint = (string) config('services.academic_guide.endpoint', 'http://localhost:8000/v1/chat');
        $topK = max(1, (int) config('services.academic_guide.top_k', 5));
        // Keep upstream timeout below common PHP max execution time to avoid fatal request kills.
        $timeout = max(5, min(25, (int) config('services.academic_guide.timeout', 20)));

        if ($endpoint === '') {
            return response()->json([
                'error' => 'Academic Guide endpoint is not configured.',
            ], 500);
        }

        $history = collect($validated['history'] ?? [])
            ->filter(function ($item): bool {
                return is_array($item)
                    && in_array((string) ($item['role'] ?? ''), ['user', 'assistant'], true)
                    && trim((string) ($item['content'] ?? '')) !== '';
            })
            ->map(function (array $item): array {
                return [
                    'role' => (string) $item['role'],
                    'content' => trim((string) $item['content']),
                ];
            })
            ->values()
            ->all();

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout(min(8, $timeout))
                ->timeout($timeout)
                ->post($endpoint, [
                    'message' => $message,
                    'history' => $history,
                    'top_k' => $topK,
                    'stream' => false,
                ]);

            if (! $response->successful()) {
                $payload = $response->json();
                $detail = strtolower(trim((string) data_get($payload, 'detail', '')));

                Log::warning('Academic Guide non-success response', [
                    'status' => $response->status(),
                    'body' => $payload ?? $response->body(),
                ]);

                if ($response->status() === 429 || str_contains($detail, 'error code: 429') || str_contains($detail, 'queue_exceeded')) {
                    return response()->json([
                        'error' => 'Academic Guide is busy right now. Please try again in a moment.',
                    ], 429);
                }

                if (str_contains($detail, 'timeout') || str_contains($detail, 'timed out')) {
                    return response()->json([
                        'error' => 'Academic Guide timed out. Please retry your question.',
                    ], 504);
                }

                return response()->json([
                    'error' => 'Academic Guide is currently unavailable. Please try again shortly.',
                ], 502);
            }

            $payload = $response->json();
            $answer = trim((string) data_get($payload, 'answer', ''));

            if ($answer === '') {
                return response()->json([
                    'error' => 'Academic Guide returned an empty response.',
                ], 502);
            }

            $sources = is_array(data_get($payload, 'sources')) ? data_get($payload, 'sources') : [];
            $assistantText = $this->formatGuideResponseMarkdown($answer, $sources);

            return response()->json([
                'message' => $assistantText,
                'message_html' => $this->renderAssistantHtml($assistantText),
                'session_id' => $sessionId,
                'mode' => 'guide',
            ]);
        } catch (ConnectionException $exception) {
            return response()->json([
                'error' => 'Unable to connect to Academic Guide service. Please check if it is running.',
            ], 503);
        } catch (\Throwable $exception) {
            Log::error('Academic Guide exception', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to process Academic Guide request.',
            ], 500);
        }
    }

    /**
     * @param array<int, mixed> $sources
     */
    private function formatGuideResponseMarkdown(string $answer, array $sources): string
    {
        $normalized = trim(str_replace(["\r\n", "\r"], "\n", $answer));
        $inlineMatch = [];
        $inlineSourcesRaw = '';
        $body = $normalized;

        if (preg_match('/^(?<body>[\s\S]*?)(?:\n|^)\s*(?:#+\s*)?sources\s*:?\s*(?<sources>[\s\S]*)$/i', $normalized, $inlineMatch) === 1) {
            $body = trim((string) ($inlineMatch['body'] ?? $normalized));
            $inlineSourcesRaw = trim((string) ($inlineMatch['sources'] ?? ''));
        }

        $inlineSources = collect($inlineSourcesRaw === '' ? [] : preg_split('/\n|\s*;\s*/', $inlineSourcesRaw))
            ->map(function (string $line): string {
                $clean = trim($line);
                $clean = preg_replace('/^[-*]\s+/', '', $clean) ?? $clean;
                $clean = preg_replace('/^\d+[\)\.\-]\s+/', '', $clean) ?? $clean;
                $clean = preg_replace('/^(?:sources?)\s*:\s*/i', '', $clean) ?? $clean;

                return trim((string) $clean);
            })
            ->filter(fn (string $line): bool => $line !== '')
            ->values();

        $payloadSources = collect($sources)
            ->filter(fn ($item): bool => is_array($item) && isset($item['document']))
            ->map(function (array $source): string {
                $document = trim((string) ($source['document'] ?? 'Unknown document'));
                $start = $source['start_page'] ?? null;
                $end = $source['end_page'] ?? null;

                if (is_numeric($start) && is_numeric($end)) {
                    if ((int) $start === (int) $end) {
                        return $document.' (page '.(int) $start.')';
                    }

                    return $document.' (pages '.(int) $start.'-'.(int) $end.')';
                }

                return $document;
            })
            ->filter(fn (string $line): bool => $line !== '')
            ->values();

        $sourceLines = $inlineSources->isNotEmpty()
            ? $inlineSources
            : $payloadSources;

        if ($sourceLines->isEmpty()) {
            return $body;
        }

        $markdownSources = $sourceLines
            ->map(fn (string $line): string => '- '.$line)
            ->unique()
            ->take(8)
            ->values()
            ->implode("\n");

        return trim($body)."\n\n### Sources\n".$markdownSources;
    }
}
