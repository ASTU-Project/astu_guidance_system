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
        ['name' => 'Pre-Engineering', 'code' => 'PRE-ENG', 'min_gpa' => 2.00, 'description' => 'Foundation program for engineering students.'],
        ['name' => 'Pre-Applied Natural Science', 'code' => 'PRE-ANS', 'min_gpa' => 2.00, 'description' => 'Foundation program for natural science students.'],
        // Year 2 Sem 1 broad tracks (Schools)
        ['name' => 'Electrical Engineering & Computing', 'code' => 'EEC', 'min_gpa' => 2.50, 'description' => 'School of Electrical Engineering and Computing.'],
        ['name' => 'Mechanical, Chemical & Materials Engineering', 'code' => 'MCME', 'min_gpa' => 2.50, 'description' => 'School of Mechanical, Chemical, and Materials Engineering.'],
        ['name' => 'Civil Engineering & Architecture', 'code' => 'CEA', 'min_gpa' => 2.50, 'description' => 'School of Civil Engineering and Architecture.'],
        ['name' => 'Applied Natural Science', 'code' => 'ANS', 'min_gpa' => 2.50, 'description' => 'School of Applied Natural Science.'],
        ['name' => 'Humanities & Social Sciences', 'code' => 'HSS', 'min_gpa' => 2.00, 'description' => 'School of Humanities and Social Sciences.'],
        // Year 2 Sem 2+ specific departments (EEC)
        ['name' => 'Software Engineering', 'code' => 'SE', 'min_gpa' => 2.75, 'description' => 'Department of Software Engineering.'],
        ['name' => 'Computer Science', 'code' => 'CSE', 'min_gpa' => 2.75, 'description' => 'Department of Computer Science.'],
        ['name' => 'Electrical Engineering', 'code' => 'EE', 'min_gpa' => 2.75, 'description' => 'Department of Electrical Engineering.'],
        ['name' => 'Electronics & Communication Engineering', 'code' => 'ECE', 'min_gpa' => 2.75, 'description' => 'Department of Electronics & Communication Engineering.'],
        ['name' => 'Electrical Power & Control Engineering', 'code' => 'EPCE', 'min_gpa' => 2.75, 'description' => 'Department of Electrical Power & Control Engineering.'],
        // MCME
        ['name' => 'Mechanical Engineering', 'code' => 'ME', 'min_gpa' => 2.75, 'description' => 'Department of Mechanical Engineering.'],
        ['name' => 'Chemical Engineering', 'code' => 'CHE', 'min_gpa' => 2.75, 'description' => 'Department of Chemical Engineering.'],
        ['name' => 'Materials Science & Engineering', 'code' => 'MSE', 'min_gpa' => 2.75, 'description' => 'Department of Materials Science & Engineering.'],
        // CEA
        ['name' => 'Civil Engineering', 'code' => 'CE', 'min_gpa' => 2.75, 'description' => 'Department of Civil Engineering.'],
        ['name' => 'Architecture', 'code' => 'ARCH', 'min_gpa' => 2.75, 'description' => 'Department of Architecture.'],
        ['name' => 'Construction Technology', 'code' => 'CT', 'min_gpa' => 2.75, 'description' => 'Department of Construction Technology.'],
        ['name' => 'Urban Planning', 'code' => 'UP', 'min_gpa' => 2.75, 'description' => 'Department of Urban Planning.'],
        ['name' => 'Water Resources', 'code' => 'WR', 'min_gpa' => 2.75, 'description' => 'Department of Water Resources.'],
        ['name' => 'Geomatics', 'code' => 'GEO', 'min_gpa' => 2.75, 'description' => 'Department of Geomatics.'],
        // ANS
        ['name' => 'Applied Mathematics', 'code' => 'AMATH', 'min_gpa' => 2.50, 'description' => 'Department of Applied Mathematics.'],
        ['name' => 'Applied Physics', 'code' => 'APHY', 'min_gpa' => 2.50, 'description' => 'Department of Applied Physics.'],
        ['name' => 'Applied Chemistry', 'code' => 'ACHE', 'min_gpa' => 2.50, 'description' => 'Department of Applied Chemistry.'],
        ['name' => 'Applied Biology', 'code' => 'ABIO', 'min_gpa' => 2.50, 'description' => 'Department of Applied Biology.'],
        ['name' => 'Applied Geology', 'code' => 'AGEO', 'min_gpa' => 2.50, 'description' => 'Department of Applied Geology.'],
        ['name' => 'Industrial Chemistry', 'code' => 'ICHE', 'min_gpa' => 2.50, 'description' => 'Department of Industrial Chemistry.'],
        ['name' => 'Pharmacy', 'code' => 'PHARM', 'min_gpa' => 2.50, 'description' => 'Department of Pharmacy.'],
        // HSS
        ['name' => 'Economics', 'code' => 'ECON', 'min_gpa' => 2.00, 'description' => 'Department of Economics.'],
        ['name' => 'Psychology', 'code' => 'PSY', 'min_gpa' => 2.00, 'description' => 'Department of Psychology.'],
        ['name' => 'Sociology', 'code' => 'SOC', 'min_gpa' => 2.00, 'description' => 'Department of Sociology.'],
        ['name' => 'Civics', 'code' => 'CIV', 'min_gpa' => 2.00, 'description' => 'Department of Civics.'],
        ['name' => 'Communication', 'code' => 'COMM', 'min_gpa' => 2.00, 'description' => 'Department of Communication.'],
    ];

    public function definition(): array
    {
        try {
            $dept = $this->faker->unique()->randomElement(self::$departments);
        } catch (\OverflowException $e) {
            // If we run out of unique departments, generate a synthetic one
            $dept = [
                'name' => $this->faker->unique()->words(3, true),
                'code' => strtoupper($this->faker->unique()->lexify('???')),
                'min_gpa' => $this->faker->randomFloat(2, 2.0, 4.0),
                'description' => $this->faker->sentence(),
            ];
        }

        return [
            'name'       => $dept['name'],
            'code'       => $dept['code'],
            'spot_limit' => $this->faker->numberBetween(30, 120),
            'min_gpa'    => $dept['min_gpa'],
            'description'=> $dept['description'] ?? 'No description available.',
        ];
    }
}
