<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'token_name' => ['nullable', 'string', 'max:80'],
        ]);

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ])) {
            return response()->json([
                'error' => 'Invalid credentials.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();
        $tokenName = $validated['token_name'] ?? 'mcp-access';
        $token = $user->createToken($tokenName);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Token revoked.',
        ]);
    }
}
