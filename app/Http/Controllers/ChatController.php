<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post(self::API, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an academic administration assistant. Keep responses concise and useful.',
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
                'session_id' => $sessionId,
            ]);
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
}
