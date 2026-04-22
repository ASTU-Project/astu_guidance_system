<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    protected static array $departments = [
        // Year 1
        ['name' => 'Pre-Engineering',                       'code' => 'PRE-ENG',  'min_gpa' => 2.00],
        ['name' => 'Pre-Science',                           'code' => 'PRE-SCI',  'min_gpa' => 2.00],
        // Year 2 Sem 1 broad tracks
        ['name' => 'Electrical Engineering & Computing',    'code' => 'EEC',      'min_gpa' => 2.50],
        ['name' => 'Mechanical, Chemical & Materials Engineering', 'code' => 'MCME', 'min_gpa' => 2.50],
        ['name' => 'Civil Engineering & Architecture',      'code' => 'CEA',      'min_gpa' => 2.50],
        ['name' => 'Applied Natural Science',               'code' => 'ANS',      'min_gpa' => 2.50],
        ['name' => 'Humanities & Social Sciences',          'code' => 'HSS',      'min_gpa' => 2.00],
        // Year 2 Sem 2+ specific departments
        ['name' => 'Software Engineering',                  'code' => 'SE',       'min_gpa' => 2.75],
        ['name' => 'Computer Science',                      'code' => 'CS',       'min_gpa' => 2.75],
        ['name' => 'Electrical Engineering',                'code' => 'EE',       'min_gpa' => 2.75],
        ['name' => 'Mechanical Engineering',                'code' => 'ME',       'min_gpa' => 2.75],
        ['name' => 'Civil Engineering',                     'code' => 'CE',       'min_gpa' => 2.75],
        ['name' => 'Chemical Engineering',                  'code' => 'CHE',      'min_gpa' => 2.75],
        ['name' => 'Materials Science & Engineering',       'code' => 'MSE',      'min_gpa' => 2.75],
        ['name' => 'Architecture',                          'code' => 'ARCH',     'min_gpa' => 2.75],
        ['name' => 'Applied Mathematics',                   'code' => 'AMATH',    'min_gpa' => 2.50],
        ['name' => 'Applied Physics',                       'code' => 'APHY',     'min_gpa' => 2.50],
        ['name' => 'Applied Chemistry',                     'code' => 'ACHE',     'min_gpa' => 2.50],
        ['name' => 'Applied Biology',                       'code' => 'ABIO',     'min_gpa' => 2.50],
    ];

    protected static int $index = 0;

    public function definition(): array
    {
        $dept = self::$departments[self::$index % count(self::$departments)];
        self::$index++;

        return [
            'name'       => $dept['name'],
            'code'       => $dept['code'],
            'spot_limit' => $this->faker->numberBetween(30, 120),
            'min_gpa'    => $dept['min_gpa'],
        ];
    }
}
