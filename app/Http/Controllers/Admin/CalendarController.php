<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
// use App\Models\Event;
use App\Models\EventBase;

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

    public function edit($id)
    {
        $base = EventBase::findOrFail($id);
        return view('admin.calendar.edit', compact('base'));
    }
}
