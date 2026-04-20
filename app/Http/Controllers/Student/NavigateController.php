<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use Illuminate\View\View;

class NavigateController extends Controller
{
    public function index(): View
    {
        $locations = MapLocation::query()
            ->orderBy('name')
            ->get();

        return view('student.navigate', compact('locations'));
    }
}