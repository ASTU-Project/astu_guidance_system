<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
