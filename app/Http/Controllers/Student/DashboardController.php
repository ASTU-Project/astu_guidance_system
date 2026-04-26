<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Http\Request;
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
        
        // Calculate current year GPA (average of scores for current year)
        $currentYear = $student->current_year;
        $currentYearGrades = $grades->where('year', $currentYear);
        
        $currentYearGPA = 0;
        if ($currentYearGrades->count() > 0) {
            $currentYearGPA = $currentYearGrades->avg('score') / 25; // Convert 0-100 scale to 0-4 GPA
            $currentYearGPA = round($currentYearGPA, 2);
        }
        
        // Determine academic status based on GPA
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
    
    private function determineAcademicStatus(float $gpa): string
    {
        if ($gpa >= 3.5) {
            return 'Excellent';
        } elseif ($gpa >= 2.5) {
            return 'Good';
        } elseif ($gpa >= 2.0) {
            return 'Satisfactory';
        } else {
            return 'At Risk';
        }
    }
}