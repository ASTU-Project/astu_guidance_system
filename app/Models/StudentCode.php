<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'code',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
