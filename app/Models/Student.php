<?php

namespace App\Models;

use App\Models\Grade;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
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

    protected $hidden = [
        'password',
    ];

    public function getSemesterNumberAttribute(): int
    {
        return str_contains($this->current_semester, 'II') ? 2 : 1;
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
