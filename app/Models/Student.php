<?php

namespace App\Models;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'student_id',
        'phone',
        'email',
        'department',
        'current_semester',
        'current_year',
        'current_section',
        'cgpa',
        'password',
    ];

    protected $casts = [
        'current_year' => 'integer',
        'cgpa' => 'decimal:2',
        'password' => 'hashed',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}
