<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventBase extends Model
{
    /** @use HasFactory<\Database\Factories\EventBaseFactory> */
    use HasFactory;

    protected $primaryKey = 'event_id';

    protected $fillable = [
        'department',
        'semester',
        'section',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'event_id', 'event_id');
    }
}
