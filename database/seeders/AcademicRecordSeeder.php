<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesterOneSubjects = [
            ['name' => 'Discrete Mathematics', 'code' => 'MTH201', 'credit_hours' => 3, 'semester' => 1],
            ['name' => 'Data Structures', 'code' => 'CS203', 'credit_hours' => 4, 'semester' => 1],
            ['name' => 'Digital Logic', 'code' => 'ECE201', 'credit_hours' => 3, 'semester' => 1],
            ['name' => 'Technical Communication', 'code' => 'ENG210', 'credit_hours' => 2, 'semester' => 1],
            ['name' => 'Computer Organization', 'code' => 'CS205', 'credit_hours' => 3, 'semester' => 1],
        ];

        $semesterTwoSubjects = [
            ['name' => 'Object Oriented Programming', 'code' => 'CS201', 'credit_hours' => 3, 'semester' => 2],
            ['name' => 'Database Systems', 'code' => 'CS202', 'credit_hours' => 3, 'semester' => 2],
            ['name' => 'Mathematics II', 'code' => 'MTH202', 'credit_hours' => 4, 'semester' => 2],
            ['name' => 'Computer Networks', 'code' => 'CS204', 'credit_hours' => 3, 'semester' => 2],
            ['name' => 'Web Development', 'code' => 'CS206', 'credit_hours' => 3, 'semester' => 2],
        ];

        $subjects = collect(array_merge($semesterOneSubjects, $semesterTwoSubjects))
            ->mapWithKeys(function (array $subject): array {
                $record = Subject::query()->updateOrCreate(
                    ['code' => $subject['code']],
                    $subject
                );

                return [$record->code => $record];
            });

        $students = Student::query()->get();
        $currentYear = (int) now()->format('Y');
        $academicYears = [$currentYear - 1, $currentYear];

        foreach ($students as $student) {
            $baselineCgpa = max(2.0, min(4.0, (float) ($student->cgpa ?? 3.0)));
            $targetScore = (int) round(($baselineCgpa / 4) * 100);

            foreach ($academicYears as $year) {
                foreach ([1, 2] as $semesterNumber) {
                    $semesterLabel = 'Sem '.$semesterNumber;

                    $subjectsForSemester = $subjects
                        ->filter(fn (Subject $subject): bool => $subject->semester === $semesterNumber)
                        ->values();

                    foreach ($subjectsForSemester as $subject) {
                        $score = random_int(
                            max(45, $targetScore - 15),
                            min(98, $targetScore + 10)
                        );

                        Grade::query()->create([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'score' => $score,
                            'semester' => $semesterLabel,
                            'year' => $year,
                        ]);
                    }
                }
            }
        }
    }
}
