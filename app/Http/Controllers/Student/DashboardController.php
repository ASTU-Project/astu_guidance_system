<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Student $student */
        $student = $request->user('student');
        
        // Get all grades for this student with their subjects
        $grades = Grade::with('subject')
            ->where('student_id', $student->id)
            ->get();
        
        // Calculate total credit hours from all taken subjects
        $totalCreditHours = $grades->sum(function ($grade) {
            return $grade->subject->credit_hours ?? 0;
        });
        
        // Calculate total unique subjects taken
        $totalSubjects = $grades->unique('subject_id')->count();
        
        // Calculate current year GPA using the same logic as StatusController
        $currentYear = $student->current_year;
        $currentYearGrades = $grades->where('year', $currentYear);
        
        $currentYearGPA = $this->calculateGpa($currentYearGrades);
        
        // Determine academic status based on GPA (matching StatusController logic)
        $academicStatus = $this->determineAcademicStatus($currentYearGPA);
        
        // Get recent grades for display
        $recentGrades = $grades->sortByDesc('created_at')->take(5);
        
        // Calculate some additional metrics
        $completedSubjects = $grades->where('score', '>=', 50)->count();
        $subjectProgress = $totalSubjects > 0 ? round(($completedSubjects / $totalSubjects) * 100) : 0;
        
        // For demonstration, set some placeholder values
        $attendanceRate = 95;
        $assignmentCompletion = 88;
        $semesterProgress = 65;
        $creditCompletion = 83;
        $creditProgress = 83;
        $gpaTrend = '+0.07';
        
        return view('student.dashboard', [
            'totalCreditHours' => $totalCreditHours,
            'totalSubjects' => $totalSubjects,
            'currentGPA' => $currentYearGPA,
            'academicStatus' => $academicStatus,
            'recentGrades' => $recentGrades,
            'completedSubjects' => $completedSubjects,
            'subjectProgress' => $subjectProgress,
            'attendanceRate' => $attendanceRate,
            'assignmentCompletion' => $assignmentCompletion,
            'semesterProgress' => $semesterProgress,
            'creditCompletion' => $creditCompletion,
            'creditProgress' => $creditProgress,
            'gpaTrend' => $gpaTrend,
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
    
    private function determineAcademicStatus(float $gpa): string
    {
        if ($gpa >= 3.5) {
            return 'Excellent Standing';
        } elseif ($gpa >= 2.0) {
            return 'Good Standing';
        } else {
            return 'At Risk';
        }
    }
}