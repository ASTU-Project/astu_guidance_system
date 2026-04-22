<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AcademicRecordSeeder extends Seeder
{
    /**
     * Curriculum map keyed by track/department, then year, then semester.
     *
     * @var array<string, array<int, array<int, array<int, array{name:string,credit_hours:int}>>>>
     */
    private const CURRICULUM = [
        'pre-engineering' => [
            1 => [
                1 => [
                    ['name' => 'Applied Math I', 'credit_hours' => 3],
                    ['name' => 'General Physics', 'credit_hours' => 3],
                    ['name' => 'General Chemistry', 'credit_hours' => 3],
                    ['name' => 'Civics', 'credit_hours' => 2],
                    ['name' => 'HP I', 'credit_hours' => 2],
                    ['name' => 'Communication English', 'credit_hours' => 2],
                    ['name' => 'Competitive Programming', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Applied Math II', 'credit_hours' => 3],
                    ['name' => 'Logic and Critical Thinking', 'credit_hours' => 2],
                    ['name' => 'HP II', 'credit_hours' => 2],
                    ['name' => 'Fundamental Programming', 'credit_hours' => 3],
                    ['name' => 'Emerging Technology', 'credit_hours' => 2],
                    ['name' => 'Basic Writing', 'credit_hours' => 2],
                ],
            ],
        ],
        'pre-science' => [
            1 => [
                1 => [
                    ['name' => 'Biology', 'credit_hours' => 3],
                    ['name' => 'Physics', 'credit_hours' => 3],
                    ['name' => 'Chemistry', 'credit_hours' => 3],
                    ['name' => 'Applied Math', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Biology II', 'credit_hours' => 3],
                    ['name' => 'Physics II', 'credit_hours' => 3],
                    ['name' => 'Chemistry II', 'credit_hours' => 3],
                    ['name' => 'Applied Math II', 'credit_hours' => 3],
                    ['name' => 'Scientific Communication', 'credit_hours' => 2],
                ],
            ],
        ],
        'electrical engineering & computing' => [
            2 => [
                1 => [
                    ['name' => 'Circuit Analysis', 'credit_hours' => 3],
                    ['name' => 'Electronics (Analog and Digital)', 'credit_hours' => 3],
                    ['name' => 'Signals and Systems', 'credit_hours' => 3],
                    ['name' => 'Control Systems', 'credit_hours' => 3],
                    ['name' => 'Power Systems', 'credit_hours' => 3],
                    ['name' => 'Electromagnetics', 'credit_hours' => 3],
                    ['name' => 'Programming (C++ Java Python)', 'credit_hours' => 3],
                    ['name' => 'Data Structures and Algorithms', 'credit_hours' => 3],
                    ['name' => 'Database Systems', 'credit_hours' => 3],
                    ['name' => 'Operating Systems', 'credit_hours' => 3],
                    ['name' => 'Computer Networks', 'credit_hours' => 3],
                    ['name' => 'Software Engineering', 'credit_hours' => 3],
                ],
            ],
        ],
        'mechanical, chemical & materials engineering' => [
            2 => [
                1 => [
                    ['name' => 'Engineering Mechanics', 'credit_hours' => 3],
                    ['name' => 'Thermodynamics', 'credit_hours' => 3],
                    ['name' => 'Fluid Mechanics', 'credit_hours' => 3],
                    ['name' => 'Machine Design', 'credit_hours' => 3],
                    ['name' => 'Heat Transfer', 'credit_hours' => 3],
                    ['name' => 'Chemical Process Principles', 'credit_hours' => 3],
                    ['name' => 'Mass Transfer', 'credit_hours' => 3],
                    ['name' => 'Reaction Engineering', 'credit_hours' => 3],
                    ['name' => 'Materials Science', 'credit_hours' => 3],
                    ['name' => 'Metallurgy', 'credit_hours' => 3],
                    ['name' => 'Polymer Engineering', 'credit_hours' => 3],
                    ['name' => 'Material Testing', 'credit_hours' => 2],
                ],
            ],
        ],
        'civil engineering & architecture' => [
            2 => [
                1 => [
                    ['name' => 'Structural Analysis', 'credit_hours' => 3],
                    ['name' => 'Soil Mechanics', 'credit_hours' => 3],
                    ['name' => 'Fluid Mechanics', 'credit_hours' => 3],
                    ['name' => 'Transportation Engineering', 'credit_hours' => 3],
                    ['name' => 'Construction Management', 'credit_hours' => 3],
                    ['name' => 'Architectural Design', 'credit_hours' => 3],
                    ['name' => 'Building Construction', 'credit_hours' => 3],
                    ['name' => 'Urban Planning', 'credit_hours' => 3],
                    ['name' => 'Structural Basics', 'credit_hours' => 2],
                    ['name' => 'Environmental Design', 'credit_hours' => 2],
                ],
            ],
        ],
        'applied natural science' => [
            2 => [
                1 => [
                    ['name' => 'Calculus (Advanced)', 'credit_hours' => 3],
                    ['name' => 'Linear Algebra', 'credit_hours' => 3],
                    ['name' => 'Differential Equations', 'credit_hours' => 3],
                    ['name' => 'Numerical Methods', 'credit_hours' => 3],
                    ['name' => 'Classical Mechanics', 'credit_hours' => 3],
                    ['name' => 'Electromagnetism', 'credit_hours' => 3],
                    ['name' => 'Quantum Physics', 'credit_hours' => 3],
                    ['name' => 'Thermodynamics', 'credit_hours' => 3],
                    ['name' => 'Organic Chemistry', 'credit_hours' => 3],
                    ['name' => 'Physical Chemistry', 'credit_hours' => 3],
                    ['name' => 'Analytical Chemistry', 'credit_hours' => 3],
                    ['name' => 'Cell Biology', 'credit_hours' => 3],
                    ['name' => 'Genetics', 'credit_hours' => 3],
                    ['name' => 'Microbiology', 'credit_hours' => 3],
                ],
            ],
        ],
        'humanities & social sciences' => [
            2 => [
                1 => [
                    ['name' => 'Communication Skills', 'credit_hours' => 2],
                    ['name' => 'Academic Writing', 'credit_hours' => 2],
                    ['name' => 'Civics and Ethics', 'credit_hours' => 2],
                    ['name' => 'Economics', 'credit_hours' => 2],
                    ['name' => 'Psychology', 'credit_hours' => 2],
                    ['name' => 'Sociology', 'credit_hours' => 2],
                ],
                2 => [
                    ['name' => 'Communication Skills', 'credit_hours' => 2],
                    ['name' => 'Civics and Ethics', 'credit_hours' => 2],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Economics', 'credit_hours' => 2],
                ],
                2 => [
                    ['name' => 'Psychology', 'credit_hours' => 2],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Sociology', 'credit_hours' => 2],
                ],
                2 => [
                    ['name' => 'Research Project', 'credit_hours' => 3],
                ],
            ],
        ],
        'software engineering' => [
            2 => [
                2 => [
                    ['name' => 'Data Structures and Algorithms', 'credit_hours' => 3],
                    ['name' => 'Object-Oriented Programming', 'credit_hours' => 3],
                    ['name' => 'Database Systems', 'credit_hours' => 3],
                    ['name' => 'Discrete Mathematics', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Software Engineering', 'credit_hours' => 3],
                    ['name' => 'Operating Systems', 'credit_hours' => 3],
                    ['name' => 'Computer Networks', 'credit_hours' => 3],
                    ['name' => 'Web Development', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Advanced Databases', 'credit_hours' => 3],
                    ['name' => 'Mobile App Development', 'credit_hours' => 3],
                    ['name' => 'Human Computer Interaction', 'credit_hours' => 3],
                    ['name' => 'System Analysis and Design', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Distributed Systems', 'credit_hours' => 3],
                    ['name' => 'Software Project Management', 'credit_hours' => 3],
                    ['name' => 'Information Security', 'credit_hours' => 3],
                    ['name' => 'Cloud Computing', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Machine Learning', 'credit_hours' => 3],
                    ['name' => 'DevOps', 'credit_hours' => 3],
                    ['name' => 'Big Data Systems', 'credit_hours' => 3],
                    ['name' => 'Software Testing', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Senior Project I', 'credit_hours' => 3],
                    ['name' => 'Research Methods', 'credit_hours' => 2],
                    ['name' => 'AI Systems', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Senior Project II', 'credit_hours' => 3],
                    ['name' => 'Internship / Industrial Practice', 'credit_hours' => 4],
                ],
            ],
        ],
        'computer science' => [
            2 => [
                2 => [
                    ['name' => 'Data Structures', 'credit_hours' => 3],
                    ['name' => 'Discrete Mathematics', 'credit_hours' => 3],
                    ['name' => 'Computer Organization', 'credit_hours' => 3],
                    ['name' => 'Object-Oriented Programming', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Operating Systems', 'credit_hours' => 3],
                    ['name' => 'Computer Networks', 'credit_hours' => 3],
                    ['name' => 'Theory of Computation', 'credit_hours' => 3],
                    ['name' => 'Database Systems', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Compiler Design', 'credit_hours' => 3],
                    ['name' => 'Artificial Intelligence', 'credit_hours' => 3],
                    ['name' => 'Numerical Methods', 'credit_hours' => 3],
                    ['name' => 'Software Engineering', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Machine Learning', 'credit_hours' => 3],
                    ['name' => 'Distributed Systems', 'credit_hours' => 3],
                    ['name' => 'Computer Graphics', 'credit_hours' => 3],
                    ['name' => 'Information Security', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Data Mining', 'credit_hours' => 3],
                    ['name' => 'Parallel Computing', 'credit_hours' => 3],
                    ['name' => 'Advanced Algorithms', 'credit_hours' => 3],
                ],
            ],
        ],
        'electrical engineering' => [
            2 => [
                2 => [
                    ['name' => 'Circuit Analysis II', 'credit_hours' => 3],
                    ['name' => 'Electronics I', 'credit_hours' => 3],
                    ['name' => 'Signals and Systems', 'credit_hours' => 3],
                    ['name' => 'Engineering Math', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Electronics II', 'credit_hours' => 3],
                    ['name' => 'Control Systems', 'credit_hours' => 3],
                    ['name' => 'Electromagnetics', 'credit_hours' => 3],
                    ['name' => 'Digital Systems', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Power Systems I', 'credit_hours' => 3],
                    ['name' => 'Communication Systems', 'credit_hours' => 3],
                    ['name' => 'Microprocessors', 'credit_hours' => 3],
                    ['name' => 'Instrumentation', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Power Systems II', 'credit_hours' => 3],
                    ['name' => 'Embedded Systems', 'credit_hours' => 3],
                    ['name' => 'Industrial Control', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Renewable Energy', 'credit_hours' => 3],
                    ['name' => 'Power Electronics', 'credit_hours' => 3],
                    ['name' => 'Final Project I', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 3],
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Practice', 'credit_hours' => 3],
                ],
            ],
        ],
        'mechanical engineering' => [
            2 => [
                2 => [
                    ['name' => 'Engineering Mechanics', 'credit_hours' => 3],
                    ['name' => 'Thermodynamics I', 'credit_hours' => 3],
                    ['name' => 'Engineering Drawing', 'credit_hours' => 2],
                    ['name' => 'Materials Science', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Thermodynamics II', 'credit_hours' => 3],
                    ['name' => 'Fluid Mechanics', 'credit_hours' => 3],
                    ['name' => 'Machine Design I', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Heat Transfer', 'credit_hours' => 3],
                    ['name' => 'Machine Design II', 'credit_hours' => 3],
                    ['name' => 'Manufacturing', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Control Engineering', 'credit_hours' => 3],
                    ['name' => 'Automotive Engineering', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Energy Systems', 'credit_hours' => 3],
                    ['name' => 'Final Project I', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 3],
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Practice', 'credit_hours' => 3],
                ],
            ],
        ],
        'civil engineering' => [
            2 => [
                2 => [
                    ['name' => 'Engineering Mechanics', 'credit_hours' => 3],
                    ['name' => 'Surveying', 'credit_hours' => 3],
                    ['name' => 'Construction Materials', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Structural Analysis I', 'credit_hours' => 3],
                    ['name' => 'Fluid Mechanics', 'credit_hours' => 3],
                    ['name' => 'Geotechnical Engineering', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Structural Analysis II', 'credit_hours' => 3],
                    ['name' => 'Transportation Engineering', 'credit_hours' => 3],
                    ['name' => 'Hydraulics', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Reinforced Concrete', 'credit_hours' => 3],
                    ['name' => 'Foundation Engineering', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Construction Management', 'credit_hours' => 3],
                    ['name' => 'Final Project I', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 3],
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Practice', 'credit_hours' => 3],
                ],
            ],
        ],
        'chemical engineering' => [
            2 => [
                2 => [
                    ['name' => 'Chemical Process Principles', 'credit_hours' => 3],
                    ['name' => 'Thermodynamics I', 'credit_hours' => 3],
                    ['name' => 'Fluid Mechanics', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Thermodynamics II', 'credit_hours' => 3],
                    ['name' => 'Mass Transfer', 'credit_hours' => 3],
                    ['name' => 'Heat Transfer', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Reaction Engineering', 'credit_hours' => 3],
                    ['name' => 'Process Control', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Separation Processes', 'credit_hours' => 3],
                    ['name' => 'Plant Design', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Chemistry', 'credit_hours' => 3],
                    ['name' => 'Final Project I', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 3],
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Practice', 'credit_hours' => 3],
                ],
            ],
        ],
        'materials science & engineering' => [
            2 => [
                2 => [
                    ['name' => 'Materials Science', 'credit_hours' => 3],
                    ['name' => 'Engineering Chemistry', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Metallurgy', 'credit_hours' => 3],
                    ['name' => 'Polymer Science', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Ceramic Materials', 'credit_hours' => 3],
                    ['name' => 'Material Testing', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Nanotechnology', 'credit_hours' => 3],
                    ['name' => 'Corrosion', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Final Project I', 'credit_hours' => 3],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 3],
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Practice', 'credit_hours' => 3],
                ],
            ],
        ],
        'architecture' => [
            2 => [
                2 => [
                    ['name' => 'Design Studio II', 'credit_hours' => 4],
                    ['name' => 'Building Construction', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Design Studio III', 'credit_hours' => 4],
                    ['name' => 'Urban Planning', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Environmental Design', 'credit_hours' => 3],
                    ['name' => 'Structural Basics', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Advanced Design', 'credit_hours' => 4],
                    ['name' => 'Landscape Architecture', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Final Project I', 'credit_hours' => 4],
                ],
            ],
            5 => [
                1 => [
                    ['name' => 'Final Project II', 'credit_hours' => 4],
                ],
                2 => [
                    ['name' => 'Internship', 'credit_hours' => 3],
                ],
            ],
        ],
        'applied mathematics' => [
            2 => [
                2 => [
                    ['name' => 'Linear Algebra', 'credit_hours' => 3],
                    ['name' => 'Differential Equations', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Numerical Methods', 'credit_hours' => 3],
                    ['name' => 'Real Analysis', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Mathematical Modeling', 'credit_hours' => 3],
                    ['name' => 'Probability', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Complex Analysis', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Research Project', 'credit_hours' => 3],
                ],
            ],
        ],
        'applied physics' => [
            2 => [
                2 => [
                    ['name' => 'Mechanics', 'credit_hours' => 3],
                    ['name' => 'Electricity and Magnetism', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Thermodynamics', 'credit_hours' => 3],
                    ['name' => 'Optics', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Quantum Mechanics', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Solid State Physics', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Research Project', 'credit_hours' => 3],
                ],
            ],
        ],
        'applied chemistry' => [
            2 => [
                2 => [
                    ['name' => 'Organic Chemistry', 'credit_hours' => 3],
                    ['name' => 'Physical Chemistry', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Analytical Chemistry', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Industrial Chemistry', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Advanced Chemistry', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Research Project', 'credit_hours' => 3],
                ],
            ],
        ],
        'applied biology' => [
            2 => [
                2 => [
                    ['name' => 'Cell Biology', 'credit_hours' => 3],
                    ['name' => 'Genetics', 'credit_hours' => 3],
                ],
            ],
            3 => [
                1 => [
                    ['name' => 'Microbiology', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Ecology', 'credit_hours' => 3],
                ],
            ],
            4 => [
                1 => [
                    ['name' => 'Biotechnology', 'credit_hours' => 3],
                ],
                2 => [
                    ['name' => 'Research Project', 'credit_hours' => 3],
                ],
            ],
        ],
    ];

    /**
     * Maps final departments to the year-2 semester-1 broad stream and year-1 entry track.
     *
     * @var array<string, array{broad:string,pre:string}>
     */
    private const FINAL_DEPARTMENT_PATH = [
        'software engineering' => ['broad' => 'electrical engineering & computing', 'pre' => 'pre-engineering'],
        'computer science' => ['broad' => 'electrical engineering & computing', 'pre' => 'pre-engineering'],
        'electrical engineering' => ['broad' => 'electrical engineering & computing', 'pre' => 'pre-engineering'],
        'mechanical engineering' => ['broad' => 'mechanical, chemical & materials engineering', 'pre' => 'pre-engineering'],
        'chemical engineering' => ['broad' => 'mechanical, chemical & materials engineering', 'pre' => 'pre-engineering'],
        'materials science & engineering' => ['broad' => 'mechanical, chemical & materials engineering', 'pre' => 'pre-engineering'],
        'civil engineering' => ['broad' => 'civil engineering & architecture', 'pre' => 'pre-engineering'],
        'architecture' => ['broad' => 'civil engineering & architecture', 'pre' => 'pre-engineering'],
        'applied mathematics' => ['broad' => 'applied natural science', 'pre' => 'pre-science'],
        'applied physics' => ['broad' => 'applied natural science', 'pre' => 'pre-science'],
        'applied chemistry' => ['broad' => 'applied natural science', 'pre' => 'pre-science'],
        'applied biology' => ['broad' => 'applied natural science', 'pre' => 'pre-science'],
        'humanities & social sciences' => ['broad' => 'humanities & social sciences', 'pre' => 'pre-science'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::query()->get();

        foreach ($students as $student) {
            $baselineCgpa = max(2.0, min(4.0, (float) ($student->cgpa ?? 3.0)));
            $targetScore = (int) round(($baselineCgpa / 4) * 100);

            $timeline = $this->buildStudentTimeline($student);

            foreach ($timeline as $term) {
                $subjectsForTerm = $this->subjectsForTrackTerm($term['track'], $term['year'], $term['semester']);

                foreach ($subjectsForTerm as $index => $subjectDef) {
                    $subject = $this->getOrCreateSubject(
                        $term['track'],
                        $term['year'],
                        $term['semester'],
                        $index + 1,
                        $subjectDef
                    );

                    $score = random_int(
                        max(45, $targetScore - 15),
                        min(98, $targetScore + 10)
                    );

                    Grade::query()->create([
                        'student_id' => $student->id,
                        'subject_id' => $subject->id,
                        'score' => $score,
                        'semester' => 'Sem '.$term['semester'],
                        'year' => $term['year'],
                    ]);
                }
            }
        }
    }

    /**
     * @return array<int, array{year:int,semester:int,track:string}>
     */
    private function buildStudentTimeline(Student $student): array
    {
        $currentYear = max(1, min(5, (int) ($student->current_year ?? 1)));
        $currentSemester = $this->semesterToNumber((string) ($student->current_semester ?? 'Semester I'));
        $department = $this->normalizeKey((string) ($student->department ?? ''));

        $path = $this->resolvePathForDepartment($department);

        $timeline = [];

        for ($year = 1; $year <= $currentYear; $year++) {
            $maxSemester = $year === $currentYear ? $currentSemester : 2;

            for ($semester = 1; $semester <= $maxSemester; $semester++) {
                $track = $this->resolveTrackForTerm($year, $semester, $path, $department);

                $timeline[] = [
                    'year' => $year,
                    'semester' => $semester,
                    'track' => $track,
                ];
            }
        }

        return $timeline;
    }

    /**
     * @return array{pre:string,broad:string,final:string}
     */
    private function resolvePathForDepartment(string $department): array
    {
        if ($department === 'pre-engineering' || $department === 'pre-science') {
            return [
                'pre' => $department,
                'broad' => $department === 'pre-engineering'
                    ? 'electrical engineering & computing'
                    : 'applied natural science',
                'final' => $department === 'pre-engineering'
                    ? 'software engineering'
                    : 'applied mathematics',
            ];
        }

        if (isset(self::FINAL_DEPARTMENT_PATH[$department])) {
            return [
                'pre' => self::FINAL_DEPARTMENT_PATH[$department]['pre'],
                'broad' => self::FINAL_DEPARTMENT_PATH[$department]['broad'],
                'final' => $department,
            ];
        }

        if (isset(self::CURRICULUM[$department][2][1])) {
            return [
                'pre' => 'pre-engineering',
                'broad' => $department,
                'final' => 'software engineering',
            ];
        }

        return [
            'pre' => 'pre-engineering',
            'broad' => 'electrical engineering & computing',
            'final' => 'software engineering',
        ];
    }

    private function resolveTrackForTerm(int $year, int $semester, array $path, string $rawDepartment): string
    {
        if ($year === 1) {
            return $path['pre'];
        }

        if ($year === 2 && $semester === 1) {
            if (isset(self::CURRICULUM[$rawDepartment][2][1])) {
                return $rawDepartment;
            }

            return $path['broad'];
        }

        if (isset(self::CURRICULUM[$rawDepartment][$year][$semester])) {
            return $rawDepartment;
        }

        return $path['final'];
    }

    /**
     * @return array<int, array{name:string,credit_hours:int}>
     */
    private function subjectsForTrackTerm(string $track, int $year, int $semester): array
    {
        return self::CURRICULUM[$track][$year][$semester] ?? [];
    }

    /**
     * @param array{name:string,credit_hours:int} $subjectDef
     */
    private function getOrCreateSubject(string $track, int $year, int $semester, int $index, array $subjectDef): Subject
    {
        $code = $this->buildSubjectCode($track, $year, $semester, $index);

        $payload = [
            'name' => $subjectDef['name'],
            'code' => $code,
            'credit_hours' => $subjectDef['credit_hours'],
            'semester' => $semester,
        ];

        if (Schema::hasColumn('subjects', 'year')) {
            $payload['year'] = $year;
        }

        return Subject::query()->updateOrCreate(
            ['code' => $code],
            $payload
        );
    }

    private function buildSubjectCode(string $track, int $year, int $semester, int $index): string
    {
        $words = preg_split('/[^a-z0-9]+/i', strtoupper($track)) ?: [];
        $prefix = collect($words)
            ->filter(fn (string $word): bool => $word !== '')
            ->map(fn (string $word): string => substr($word, 0, 1))
            ->implode('');

        $prefix = substr($prefix !== '' ? $prefix : 'SUB', 0, 6);

        return sprintf('%s-Y%dS%d-%02d', $prefix, $year, $semester, $index);
    }

    private function semesterToNumber(string $semester): int
    {
        $normalized = strtolower(trim($semester));

        return str_contains($normalized, 'ii') || str_contains($normalized, '2') ? 2 : 1;
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));

        return preg_replace('/\s+/', ' ', $value) ?? $value;
    }
}
