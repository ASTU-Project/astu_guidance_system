<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class StatusController extends Controller
{
    private const ALLOWED_SEMESTERS = ['Sem 1', 'Sem 2'];

    public function index(Request $request): View
    {
        /** @var Student $student */
        $student = $request->user('student');
        $studentYearLevel = $this->normalizeYearLevel($student->current_year);

        $semesterFromStudent = $this->normalizeSemester((string) $student->current_semester);

        $yearOptions = Grade::query()
            ->where('student_id', $student->id)
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->map(fn ($year): int => (int) $year)
            ->values();

        if ($yearOptions->isEmpty()) {
            $yearOptions = collect([(int) now()->format('Y')]);
        }

        $selectedYear = (int) $request->integer('year', (int) $yearOptions->last());

        if (! $yearOptions->contains($selectedYear)) {
            $selectedYear = (int) $yearOptions->last();
        }

        $requestedSemester = (string) $request->input('semester', $semesterFromStudent);
        $selectedSemester = in_array($requestedSemester, self::ALLOWED_SEMESTERS, true)
            ? $requestedSemester
            : $semesterFromStudent;

        $selectedView = in_array((string) $request->input('view', 'all_time'), ['current', 'all_time'], true)
            ? (string) $request->input('view', 'all_time')
            : 'all_time';

        $viewModes = [
            ['value' => 'current', 'label' => 'Current'],
            ['value' => 'all_time', 'label' => 'All Time'],
        ];

        $studentGrades = Grade::query()
            ->with('subject:id,name,code,credit_hours')
            ->where('student_id', $student->id)
            ->orderBy('year')
            ->orderBy('semester')
            ->get();

        $semesterBreakdown = collect(self::ALLOWED_SEMESTERS)
            ->map(function (string $semester) use ($studentGrades, $selectedYear): array {
                $grades = $studentGrades
                    ->filter(function (Grade $grade) use ($selectedYear, $semester): bool {
                        return (int) $grade->year === $selectedYear
                            && $this->normalizeSemester((string) $grade->semester) === $semester;
                    })
                    ->values();

                $subjects = $grades->map(function (Grade $grade): array {
                    $subject = $grade->subject;
                    $score = (int) $grade->score;

                    return [
                        'subject' => $subject?->name ?? 'Unknown Subject',
                        'code' => $subject?->code ?? 'N/A',
                        'credit' => (int) ($subject?->credit_hours ?? 0),
                        'score' => $score,
                        'grade' => $this->scoreToLetter($score),
                    ];
                })->values();

                return [
                    'semester' => $semester,
                    'gpa' => $this->calculateGpa($grades),
                    'subjects' => $subjects,
                    'credits' => (int) $subjects->sum('credit'),
                ];
            })
            ->values();

        $semesterPanels = $studentGrades
            ->groupBy('year')
            ->sortKeysDesc()
            ->flatMap(function (Collection $yearGrades, int|string $year): Collection {
                return collect(self::ALLOWED_SEMESTERS)
                    ->map(function (string $semester) use ($yearGrades, $year): array {
                        $grades = $yearGrades
                            ->filter(fn (Grade $grade): bool => $this->normalizeSemester((string) $grade->semester) === $semester)
                            ->values();

                        $subjects = $grades->map(function (Grade $grade): array {
                            $subject = $grade->subject;
                            $score = (int) $grade->score;

                            return [
                                'subject' => $subject?->name ?? 'Unknown Subject',
                                'code' => $subject?->code ?? 'N/A',
                                'credit' => (int) ($subject?->credit_hours ?? 0),
                                'score' => $score,
                                'grade' => $this->scoreToLetter($score),
                            ];
                        })->values();

                        return [
                            'year' => (int) $year,
                            'semester' => $semester,
                            'gpa' => $this->calculateGpa($grades),
                            'subjects' => $subjects,
                            'credits' => (int) $subjects->sum('credit'),
                        ];
                    })
                    ->values();
            })
            ->values();

        $selectedSemesterData = $semesterBreakdown->firstWhere('semester', $selectedSemester) ?? [
            'gpa' => 0.0,
            'subjects' => collect(),
            'credits' => 0,
        ];

        $semesterGpa = (float) ($selectedSemesterData['gpa'] ?? 0.0);

        $yearlyOverview = $studentGrades
            ->groupBy('year')
            ->sortKeys()
            ->map(function (Collection $yearGrades, int|string $year) use ($studentGrades): array {
                $yearInt = (int) $year;

                $sem1Grades = $yearGrades
                    ->filter(fn (Grade $grade): bool => $this->normalizeSemester((string) $grade->semester) === 'Sem 1')
                    ->values();
                $sem2Grades = $yearGrades
                    ->filter(fn (Grade $grade): bool => $this->normalizeSemester((string) $grade->semester) === 'Sem 2')
                    ->values();
                $cumulativeGrades = $studentGrades->filter(fn (Grade $grade): bool => (int) $grade->year <= $yearInt)->values();

                return [
                    'year' => $yearInt,
                    'sem1_gpa' => $this->calculateGpa($sem1Grades),
                    'sem2_gpa' => $this->calculateGpa($sem2Grades),
                    'year_gpa' => $this->calculateGpa($yearGrades),
                    'cgpa' => $this->calculateGpa($cumulativeGrades),
                ];
            })
            ->values();

        $currentYearOverview = $yearlyOverview->firstWhere('year', $selectedYear) ?? $yearlyOverview->last();
        $yearGpa = (float) ($currentYearOverview['year_gpa'] ?? 0.0);
        $computedCgpa = (float) ($yearlyOverview->last()['cgpa'] ?? 0.0);

        $cohortIds = Student::query()
            ->where('department', $student->department)
            ->where('current_year', $studentYearLevel)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values();

        if (! $cohortIds->contains((int) $student->id)) {
            $cohortIds->push((int) $student->id);
        }

        $cohortGrades = Grade::query()
            ->with('subject:id,credit_hours')
            ->whereIn('student_id', $cohortIds)
            ->where('year', $selectedYear)
            ->where('semester', $selectedSemester)
            ->get()
            ->groupBy('student_id');

        $cohortGpas = $cohortIds
            ->mapWithKeys(function (int $studentId) use ($cohortGrades): array {
                $gpa = $this->calculateGpa(collect($cohortGrades->get($studentId, [])));

                return [$studentId => $gpa];
            });

        $studentCohortGpa = (float) ($cohortGpas->get((int) $student->id) ?? 0.0);
        $higherCount = $cohortGpas->filter(fn (float $gpa): bool => $gpa > $studentCohortGpa)->count();

        $rankPosition = $higherCount + 1;
        $rankTotal = max(1, $cohortGpas->count());
        $percentile = max(1, (int) round(($rankPosition / $rankTotal) * 100));

        $status = $semesterGpa >= 3.5
            ? 'Excellent Standing'
            : ($semesterGpa >= 2.0 ? 'Good Standing' : 'At Risk');

        $category = $percentile <= 20
            ? 'Excellent Performance'
            : ($percentile <= 50 ? 'Good Performance' : 'Needs Improvement');

        $summary = [
            'cgpa' => $computedCgpa,
            'semester_gpa' => $semesterGpa,
            'year_gpa' => $yearGpa,
            'total_credits' => (int) $studentGrades->sum(fn (Grade $grade): int => (int) ($grade->subject?->credit_hours ?? 0)),
            'rank_position' => $rankPosition,
            'rank_total' => $rankTotal,
            'percentile' => $percentile,
            'status' => $status,
            'category' => $category,
        ];

        $selectedSemesterSubjects = collect(data_get($selectedSemesterData, 'subjects', []))->values();

        $strongest = $selectedSemesterSubjects->sortByDesc('score')->first();
        $weakest = $selectedSemesterSubjects->sortBy('score')->first();

        $selectedSemesterPanel = $semesterPanels->first(function (array $panel) use ($selectedYear, $selectedSemester): bool {
            return (int) ($panel['year'] ?? 0) === $selectedYear
                && (string) ($panel['semester'] ?? '') === $selectedSemester;
        }) ?? [
            'year' => $selectedYear,
            'semester' => $selectedSemester,
            'gpa' => $semesterGpa,
            'credits' => 0,
        ];

        $chronologicalPanels = $semesterPanels
            ->filter(fn (array $panel): bool => (int) ($panel['credits'] ?? 0) > 0)
            ->sortBy(fn (array $panel): int => ((int) ($panel['year'] ?? 0) * 10) + (((string) ($panel['semester'] ?? '') === 'Sem 1') ? 1 : 2))
            ->values();

        $currentPanelIndex = $chronologicalPanels->search(function (array $panel) use ($selectedYear, $selectedSemester): bool {
            return (int) ($panel['year'] ?? 0) === $selectedYear
                && (string) ($panel['semester'] ?? '') === $selectedSemester;
        });

        $previousTermPanel = is_int($currentPanelIndex) && $currentPanelIndex > 0
            ? $chronologicalPanels->get($currentPanelIndex - 1)
            : null;

        $previousYearOverview = $yearlyOverview
            ->filter(fn ($row) => $row['year'] < ($currentYearOverview['year'] ?? $selectedYear))
            ->sortByDesc('year')
            ->first();

        $yearlyChange = (float) (($currentYearOverview['year_gpa'] ?? 0) - ($previousYearOverview['year_gpa'] ?? 0));

        $hasPreviousSemesterData = is_array($previousTermPanel);
        $previousSemesterGpa = (float) ($previousTermPanel['gpa'] ?? 0.0);
        $gpaChange = round($semesterGpa - $previousSemesterGpa, 2);

        $termLabel = (string) ($selectedSemesterPanel['semester'] ?? $selectedSemester);
        $termYear = (int) ($selectedSemesterPanel['year'] ?? $selectedYear);

        $insights = [
            $hasPreviousSemesterData
                ? $termLabel.' '.$termYear.' GPA '.($gpaChange >= 0 ? 'increased' : 'decreased').' by '.($gpaChange >= 0 ? '+' : '').number_format($gpaChange, 2).' versus previous available term'
                : 'No previous academic term with grades found for comparison',
            'Strongest subject: '.((string) ($strongest['subject'] ?? 'N/A')),
            'Weakest subject: '.((string) ($weakest['subject'] ?? 'N/A')),
            $hasPreviousSemesterData
                ? 'Overall performance: '.($gpaChange > 0 ? 'Improving' : ($gpaChange < 0 ? 'Declining' : 'Stable')).' this term'
                : 'Overall performance: Baseline',
            'Year comparison: '.($yearlyChange >= 0 ? '+' : '').number_format($yearlyChange, 2).' GPA versus previous year',
        ];

        $trendLabels = $yearlyOverview->map(fn ($row): string => (string) ($row['year'] ?? ''))->values();
        $trendValues = $yearlyOverview->map(fn ($row): float => (float) ($row['year_gpa'] ?? 0.0))->values();

        $performanceLabels = $selectedSemesterSubjects->map(fn (array $subject): string => (string) ($subject['subject'] ?? 'Subject'))->values();
        $performanceValues = $selectedSemesterSubjects->map(fn (array $subject): int => (int) ($subject['score'] ?? 0))->values();

        $semesterPanels = $semesterPanels
            ->map(function (array $panel): array {
                $year = (int) ($panel['year'] ?? 0);
                $semester = (string) ($panel['semester'] ?? 'Semester');

                $title = match ($semester) {
                    'Sem 1' => '1st Semester ('.$year.')',
                    'Sem 2' => '2nd Semester ('.$year.')',
                    default => $semester.' ('.$year.')',
                };

                $panel['title'] = $title;

                return $panel;
            })
            ->values();

        $yearlyOverviewRows = $yearlyOverview
            ->values()
            ->map(function (array $row, int $index) use ($yearlyOverview): array {
                $prevRow = $index > 0 ? $yearlyOverview->get($index - 1) : null;
                $delta = is_array($prevRow) ? ((float) $row['year_gpa'] - (float) ($prevRow['year_gpa'] ?? 0.0)) : null;

                return [
                    'year' => (int) ($row['year'] ?? 0),
                    'sem1_gpa' => (float) ($row['sem1_gpa'] ?? 0.0),
                    'sem2_gpa' => (float) ($row['sem2_gpa'] ?? 0.0),
                    'year_gpa' => (float) ($row['year_gpa'] ?? 0.0),
                    'cgpa' => (float) ($row['cgpa'] ?? 0.0),
                    'delta' => $delta,
                    'delta_label' => $delta === null ? 'N/A' : (($delta >= 0 ? '+' : '').number_format($delta, 2)),
                    'delta_class' => $delta === null ? 'text-slate-400' : ($delta >= 0 ? 'text-emerald-700' : 'text-rose-700'),
                ];
            })
            ->values();

        return view('student.status', [
            'summary' => $summary,
            'semesterPanels' => $semesterPanels,
            'yearlyOverviewRows' => $yearlyOverviewRows,
            'yearOptions' => $yearOptions->values(),
            'semesterOptions' => collect(self::ALLOWED_SEMESTERS)->map(fn (string $semester): array => ['value' => $semester, 'label' => $semester])->values(),
            'viewModes' => $viewModes,
            'selectedYear' => $selectedYear,
            'selectedSemester' => $selectedSemester,
            'selectedView' => $selectedView,
            'selectedYearLabel' => (int) ($currentYearOverview['year'] ?? $selectedYear),
            'previousYearLabel' => $previousYearOverview['year'] ?? 'previous year',
            'yearlyChange' => $yearlyChange,
            'rankPosition' => $rankPosition,
            'rankTotal' => $rankTotal,
            'percentile' => $percentile,
            'statusLabel' => $status,
            'categoryLabel' => $category,
            'insights' => $insights,
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
            'performanceLabels' => $performanceLabels,
            'performanceValues' => $performanceValues,
            'selectedSemesterPanelTitle' => $termLabel.' '.$termYear,
        ]);
    }

    private function calculateGpa(Collection $grades): float
    {
        $totalCredits = 0;
        $weightedPoints = 0.0;

        foreach ($grades as $grade) {
            if (! $grade instanceof Grade) {
                continue;
            }

            $credit = (int) ($grade->subject?->credit_hours ?? 0);
            if ($credit <= 0) {
                continue;
            }

            $point = $this->scoreToPoint((int) $grade->score);
            $weightedPoints += $point * $credit;
            $totalCredits += $credit;
        }

        if ($totalCredits === 0) {
            return 0.0;
        }

        return round($weightedPoints / $totalCredits, 2);
    }

    private function scoreToPoint(int $score): float
    {
        return match (true) {
            $score >= 90 => 4.0,
            $score >= 85 => 4.0,
            $score >= 80 => 3.7,
            $score >= 75 => 3.3,
            $score >= 70 => 3.0,
            $score >= 65 => 2.7,
            $score >= 60 => 2.0,
            $score >= 50 => 1.0,
            default => 0.0,
        };
    }

    private function scoreToLetter(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A+',
            $score >= 85 => 'A',
            $score >= 80 => 'A-',
            $score >= 75 => 'B+',
            $score >= 70 => 'B',
            $score >= 65 => 'B-',
            $score >= 60 => 'C',
            $score >= 50 => 'D',
            default => 'F',
        };
    }

    private function normalizeSemester(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'sem 1', 'semester 1', 'semester i', 'i', '1' => 'Sem 1',
            'sem 2', 'semester 2', 'semester ii', 'ii', '2' => 'Sem 2',
            default => 'Sem 2',
        };
    }

    private function normalizeYearLevel(int|string|null $value): int
    {
        if (is_int($value)) {
            return max(1, $value);
        }

        $digits = preg_replace('/[^0-9]/', '', (string) $value);

        if ($digits === null || $digits === '') {
            return 1;
        }

        return max(1, (int) $digits);
    }

}
