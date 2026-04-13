<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationSetting extends Model
{
    /** @use HasFactory<\Database\Factories\AutomationSettingFactory> */
    use HasFactory;

    // This model stores user-scoped automation preferences, not per-session chat state.
    protected $fillable = [
        'user_id',
        'enable_write_tools',
        'confirm_destructive_actions',
        'enabled_tool_groups',
        'system_prompt',
    ];

    protected $casts = [
        'enable_write_tools' => 'boolean',
        'confirm_destructive_actions' => 'boolean',
        'enabled_tool_groups' => 'array',
    ];

    protected $attributes = [
        'enabled_tool_groups' => '[]',
    ];
}
