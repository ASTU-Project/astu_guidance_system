<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected static array $departmentsByStage = [
        'year1' => [
            'Pre-Engineering',
            'Pre-Science',
        ],
        'year2_sem1' => [
            'Electrical Engineering & Computing',
            'Mechanical, Chemical & Materials Engineering',
            'Civil Engineering & Architecture',
            'Applied Natural Science',
            'Humanities & Social Sciences',
        ],
        'year2_sem2_plus' => [
            'Software Engineering',
            'Computer Science',
            'Electrical Engineering',
            'Mechanical Engineering',
            'Civil Engineering',
            'Chemical Engineering',
            'Materials Science & Engineering',
            'Architecture',
            'Applied Mathematics',
            'Applied Physics',
            'Applied Chemistry',
            'Applied Biology',
            'Humanities & Social Sciences',
        ],
    ];

    public function definition(): array
    {
        $year = $this->faker->numberBetween(1, 5);
        $semester = $this->faker->randomElement(['Semester I', 'Semester II']);

        $department = $this->resolveDepartment($year, $semester);

        return [
            'name'             => $this->faker->name(),
            'student_id'       => $this->faker->unique()->numerify('UGR/#####/14'),
            'phone'            => $this->faker->optional()->numerify('09########'),
            'email'            => $this->faker->unique()->safeEmail(),
            'department'       => $department,
            'current_semester' => $semester,
            'current_year'     => $year,
            'current_section'  => 'Section ' . $this->faker->numberBetween(1, 8),
            'cgpa'             => $this->faker->randomFloat(2, 2.0, 4.0),
            'password'         => Hash::make('password'),
        ];
    }

    private function resolveDepartment(int $year, string $semester): string
    {
        if ($year === 1) {
            return $this->faker->randomElement(self::$departmentsByStage['year1']);
        }

        if ($year === 2 && $semester === 'Semester I') {
            return $this->faker->randomElement(self::$departmentsByStage['year2_sem1']);
        }

        return $this->faker->randomElement(self::$departmentsByStage['year2_sem2_plus']);
    }
}
