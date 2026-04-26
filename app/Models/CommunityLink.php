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
        'logo_url',
        'leader',
        'description',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['logo_src', 'image_src'];
    public function getImageSrcAttribute(): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');
        return $storage->url($this->image_url);
    }

    public function getLogoSrcAttribute(): ?string
    {
        if (!$this->logo_url) {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');
        return $storage->url($this->logo_url);
    }
}
