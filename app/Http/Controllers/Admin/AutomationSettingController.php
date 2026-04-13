<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomationSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationSettingController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        // These settings are user-scoped preferences, not per-session chat state.
        $settings = AutomationSetting::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'enable_write_tools' => false,
                'confirm_destructive_actions' => true,
                'enabled_tool_groups' => [],
                'system_prompt' => null,
            ]
        );

        return response()->json([
            'settings' => [
                'enable_write_tools' => $settings->enable_write_tools,
                'confirm_destructive_actions' => $settings->confirm_destructive_actions,
                'enabled_tool_groups' => $settings->enabled_tool_groups ?? [],
                'system_prompt' => $settings->system_prompt,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enable_write_tools' => ['nullable', 'boolean'],
            'confirm_destructive_actions' => ['nullable', 'boolean'],
            'enabled_tool_groups' => ['nullable', 'array'],
            'enabled_tool_groups.*' => ['string', 'in:students,departments,calendar,policies'],
            'system_prompt' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // These settings are user-scoped preferences, not per-session chat state.
        $settings = AutomationSetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'enable_write_tools' => $request->boolean('enable_write_tools'),
                'confirm_destructive_actions' => $request->boolean('confirm_destructive_actions', true),
                'enabled_tool_groups' => $validated['enabled_tool_groups'] ?? [],
                'system_prompt' => $validated['system_prompt'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Automation settings saved successfully.',
            'settings' => [
                'enable_write_tools' => $settings->enable_write_tools,
                'confirm_destructive_actions' => $settings->confirm_destructive_actions,
                'enabled_tool_groups' => $settings->enabled_tool_groups ?? [],
                'system_prompt' => $settings->system_prompt,
            ],
        ]);
    }
}
