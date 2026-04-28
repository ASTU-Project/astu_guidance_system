<?php

namespace Database\Factories;

use App\Models\StudentCode;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentCodeFactory extends Factory
{
    protected $model = StudentCode::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'code' => $this->faker->unique()->numerify('##########'),
        ];
    }
}
