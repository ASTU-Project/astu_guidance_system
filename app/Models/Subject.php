<?php

namespace App\Models;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'credit_hours',
        'semester',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
        'semester' => 'integer',
    ];

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }
}