<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'spot_limit',
        'min_gpa',
        'description',
    ];

    protected $casts = [
        'spot_limit' => 'integer',
        'min_gpa' => 'decimal:2',
    ];
}