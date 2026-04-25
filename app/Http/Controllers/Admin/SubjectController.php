<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        if ($request->filled('semester')) {
            $query->where('semester', (int) $request->input('semester'));
        }

        if ($request->filled('credit_hours')) {
            $query->where('credit_hours', (int) $request->input('credit_hours'));
        }

        $subjects = $query->orderBy('year')->orderBy('semester')->orderBy('name')->paginate(50)->withQueryString();

        $years        = Subject::query()->distinct()->orderBy('year')->pluck('year');
        $creditHours  = Subject::query()->distinct()->orderBy('credit_hours')->pluck('credit_hours');

        return view('admin.subjects', compact('subjects', 'years', 'creditHours'));
    }
}
