<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\ChatController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
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
        ]);

        $apiKey = config('services.cerebras.key');
        $model = (string) config('services.cerebras.model', self::DEFAULT_MODEL);

        if (! is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'error' => 'Cerebras API key is missing. Configure services.cerebras.key.',
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
}
