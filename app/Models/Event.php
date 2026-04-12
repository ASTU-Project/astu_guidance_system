<?php

namespace App\Models;

use App\Models\EventBase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'event_id',
        'task',
        'event_date',
        'day',
        'start_hour',
        'start_min',
        'end_hour',
        'end_min',
        'source',
        'editable',
        'deletable',
        'color',
        'student_id',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_hour' => 'integer',
        'start_min' => 'integer',
        'end_hour' => 'integer',
        'end_min' => 'integer',
        'editable' => 'boolean',
        'deletable' => 'boolean',
    ];

    public function eventBase(): BelongsTo
    {
        return $this->belongsTo(EventBase::class, 'event_id', 'event_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
