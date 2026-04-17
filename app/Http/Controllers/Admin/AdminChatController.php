<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ChatController;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminChatController extends ChatController
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

        $provider = $this->getLlmProvider();
        $apiKey = $this->getLlmApiKey();
        $model = $this->getLlmModel();

        if (trim($apiKey) === '') {
            return response()->json([
                'error' => ucfirst($provider).' API key is missing. Configure services.'. $provider .'.key.',
            ], 500);
        }

        $user = $request->user();
        $message = trim($validated['message']);
        $sessionId = $validated['session_id'] ?? (string) Str::uuid();

        $lockKey = $this->buildInFlightLockKey($user, $sessionId, $request->ip() ?? 'unknown');
        $lock = Cache::lock($lockKey, self::IN_FLIGHT_LOCK_SECONDS);
        $lockAcquired = $lock->get();

        if (! $lockAcquired) {
            return response()->json([
                'error' => 'A previous chat request is still processing. Please wait a moment and try again.',
                'session_id' => $sessionId,
            ], 429);
        }

        $automationSettings = $this->getAutomationSettings($user);
        $systemInstruction = $this->buildSystemInstruction($user, $automationSettings);
        $conversationMessages = $this->buildConversationMessages($user, $sessionId, $message, $systemInstruction);
        $enabledToolGroups = $this->getEnabledToolGroupsFromSettings($automationSettings);
        $enabledToolGroups = $this->applyDefaultReadToolGroups($enabledToolGroups, $user);
        $enabledToolGroups = $this->filterToolGroupsForUser($enabledToolGroups, $user);
        $tools = $this->buildToolDefinitions($enabledToolGroups, $user);
        $toolingMessages = $conversationMessages;

        try {
            $fastPath = $this->tryHandleGroundedReadFastPath($message, $enabledToolGroups, $user);
            if ($fastPath !== null) {
                return $this->storeAndRespond($user, $sessionId, $message, $fastPath);
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
            if (trim($assistantText) === '') {
                $assistantText = 'I could not generate a complete response. Please retry your request.';
            }

            return $this->storeAndRespond($user, $sessionId, $message, $assistantText);
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
            if ($lockAcquired) {
                $lock->release();
            }
        }
    }

    private function storeAndRespond(mixed $user, string $sessionId, string $message, string $assistantText): JsonResponse
    {
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
}
