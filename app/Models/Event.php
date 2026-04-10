<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'day',
        'start_hour',
        'start_minute',
        'end_hour',
        'end_minute',
        'semester',
        'year',
        'department',
        'section',
        'source',
        'editable',
        'deletable',
        'color_id',
        'created_by',
        'student_id',
    ];

    protected $casts = [
        'day' => 'integer',
        'start_hour' => 'integer',
        'start_minute' => 'integer',
        'end_hour' => 'integer',
        'end_minute' => 'integer',
        'year' => 'integer',
        'editable' => 'boolean',
        'deletable' => 'boolean',
        'color_id' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
