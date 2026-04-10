<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityLink extends Model
{
    /** @use HasFactory<\Database\Factories\CommunityLinkFactory> */
    use HasFactory;

    protected $table = 'community_link';

    protected $fillable = [
        'name',
        'type',
        'url',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}