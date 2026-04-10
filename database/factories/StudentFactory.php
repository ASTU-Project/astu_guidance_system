<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'student_id' => $this->faker->unique()->numerify('UGR/#####/14'),
            'phone' => $this->faker->optional()->numerify('09########'),
            'email' => $this->faker->unique()->safeEmail(),
            'department' => $this->faker->randomElement([
                'Computer Science',
                'Electrical Engineering',
                'Mechanical Engineering',
                'Civil Engineering',
                'Information Systems',
            ]),
            'current_semester' => $this->faker->randomElement(['Semester I', 'Semester II']),
            'current_year' => $this->faker->numberBetween(1, 5),
            'current_section' => 'Section ' . $this->faker->numberBetween(1, 8),
            'cgpa' => $this->faker->randomFloat(2, 2.0, 4.0),
            'password' => Hash::make('password'),
        ];
    }
}
