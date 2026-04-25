<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CommunityLink extends Model
{
    use HasFactory;

    protected $table = 'community_link';

    protected $fillable = [
        'name',
        'type',
        'url',
        'image_url',
        'leader',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getImageSrcAttribute(): ?string
    {
        return $this->image_url ? Storage::disk('public')->url($this->image_url) : null;
    }
}
