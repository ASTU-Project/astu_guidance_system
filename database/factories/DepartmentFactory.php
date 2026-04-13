<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->randomElement([
            'Computer Science',
            'Software Engineering',
            'Information Systems',
            'Electrical Engineering',
            'Mechanical Engineering',
            'Civil Engineering',
            'Architecture',
            'Business Administration',
        ]);

        $code = collect(explode(' ', $name))
            ->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))
            ->implode('');

        return [
            'name' => $name,
            'code' => $code . $this->faker->unique()->numberBetween(10, 99),
            'spot_limit' => $this->faker->numberBetween(30, 120),
            'min_gpa' => $this->faker->randomFloat(2, 2.00, 3.50),
        ];
    }
}