<?php

namespace App\Models;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    /** @use HasFactory<\Database\Factories\GradeFactory> */
    use HasFactory;

    protected $fillable = [
        'student_id',
        'subject_id',
        'score',
        'semester',
        'year',
    ];

    protected $casts = [
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'score' => 'integer',
        'year' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}