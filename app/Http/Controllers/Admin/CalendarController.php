<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventBase;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
         $bases = EventBase::query()
            ->orderBy('department')
            ->orderBy('semester')
            ->orderBy('section')
            ->get();

        return view('admin.calendar.index', compact('bases'));
    }

    public function store(Request $request){
        $validated = $request->validate([
            'department' => ['required', 'string', 'max:255', ],
            'semester' => ['required', 'string','min:1', 'max:2'],
            'section' => ['required', 'integer', 'min:1'],
        ]);

        EventBase::create([
            'department' => $validated['department'],
            'semester' => $validated['semester'],
            'section' => $validated['section'],
        ]);

        return redirect()
            ->route('admin.calendar.index')
            ->with('success', 'Event base created successfully.');
    }
}
