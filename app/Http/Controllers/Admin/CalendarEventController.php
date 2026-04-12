<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBase;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarEventController extends Controller
{
    public function index($id)
    {
        $base = EventBase::findOrFail($id);

        $dayToIndex = [
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
        ];

        $calendarEvents = $base->events()
            ->orderBy('event_date')
            ->orderBy('day')
            ->orderBy('start_hour')
            ->orderBy('start_min')
            ->get()
            ->map(function (Event $event) use ($dayToIndex) {
                return [
                    'id' => $event->id,
                    'title' => $event->task,
                    'day' => $dayToIndex[$event->day] ?? 0,
                    'startHour' => (int) $event->start_hour,
                    'startMin' => (int) $event->start_min,
                    'endHour' => (int) $event->end_hour,
                    'endMin' => (int) $event->end_min,
                    'tone' => $event->color,
                    'eventDate' => optional($event->event_date)->format('Y-m-d'),
                ];
            })
            ->values();

        return view('admin.calendar.events', compact('base', 'calendarEvents'));
    }

    public function store(Request $request, $id)
    {
        $base = EventBase::findOrFail($id);

        $validated = $this->validateEventPayload($request);

        Event::create([
            'event_id' => $base->event_id,
            'task' => $validated['task'],
            'event_date' => $validated['event_date'] ?? null,
            'day' => $validated['day'],
            'start_hour' => $validated['start_hour'],
            'start_min' => $validated['start_min'],
            'end_hour' => $validated['end_hour'],
            'end_min' => $validated['end_min'],
            'source' => 'admin',
            'editable' => false,
            'deletable' => false,
            'color' => $validated['color'],
            'student_id' => null,
        ]);

        return redirect()
            ->route('admin.calendar.events', $base->event_id)
            ->with('status', 'Event created successfully.');
    }

    public function update(Request $request, $id, Event $event)
    {
        $base = EventBase::findOrFail($id);

        if ((int) $event->event_id !== (int) $base->event_id) {
            abort(404);
        }

        $validated = $this->validateEventPayload($request);

        $event->update([
            'task' => $validated['task'],
            'event_date' => $validated['event_date'] ?? null,
            'day' => $validated['day'],
            'start_hour' => $validated['start_hour'],
            'start_min' => $validated['start_min'],
            'end_hour' => $validated['end_hour'],
            'end_min' => $validated['end_min'],
            'color' => $validated['color'],
        ]);

        return redirect()
            ->route('admin.calendar.events', $base->event_id)
            ->with('status', 'Event updated successfully.');
    }

    private function validateEventPayload(Request $request): array
    {
        $validated = $request->validate([
            'task' => ['bail', 'required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'day' => ['bail', 'required_without:event_date', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_hour' => ['bail', 'required'],
            'start_min' => ['bail', 'required'],
            'end_hour' => ['bail', 'required'],
            'end_min' => ['bail', 'required'],
            'color' => ['bail', 'required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if (!empty($validated['event_date'])) {
            $validated['day'] = strtolower(Carbon::parse($validated['event_date'])->englishDayOfWeek);
        }

        return $validated;
    }
}
