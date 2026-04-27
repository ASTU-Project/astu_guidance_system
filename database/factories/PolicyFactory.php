<?php

namespace Database\Factories;

use App\Models\Policy;
use Illuminate\Database\Eloquent\Factories\Factory;

class PolicyFactory extends Factory
{
    protected $model = Policy::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'category' => $this->faker->randomElement(['Academic', 'Governance', 'Finance', 'Conduct', 'Attendance']),
            'content' => $this->faker->paragraph,
            'is_active' => true,
        ];
    }
}
