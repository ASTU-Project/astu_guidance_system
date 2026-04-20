<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventBase;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Student $student */
        $student = $request->user('student');
        $base = $this->resolveCalendarBase($student);

        $calendarEvents = collect();

        if ($base) {
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
                ->where(function ($query) use ($student): void {
                    $query->whereNull('student_id')
                        ->orWhere('student_id', $student->id);
                })
                ->orderBy('event_date')
                ->orderBy('day')
                ->orderBy('start_hour')
                ->orderBy('start_min')
                ->get()
                ->map(function (Event $event) use ($student, $dayToIndex) {
                    return $this->mapEventForView($event, $student, $dayToIndex);
                })
                ->values();
        }

        $specificEvents = $calendarEvents
            ->filter(fn (array $event): bool => ! empty($event['eventDate']))
            ->values();

        return view('student.calendar', compact('base', 'calendarEvents', 'specificEvents'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->user('student');
        $base = $this->resolveCalendarBase($student);

        if (! $base) {
            return redirect()
                ->route('student.calendar')
                ->withErrors(['calendar' => 'No calendar base matches your current profile.']);
        }

        $validated = $this->validateEventPayload($request);
        $this->ensureEventDoesNotOverlap($base, $validated);

        Event::create([
            'event_id' => $base->event_id,
            'task' => $validated['task'],
            'event_date' => $validated['event_date'] ?? null,
            'day' => $validated['day'],
            'start_hour' => $validated['start_hour'],
            'start_min' => $validated['start_min'],
            'end_hour' => $validated['end_hour'],
            'end_min' => $validated['end_min'],
            'source' => 'student',
            'editable' => true,
            'deletable' => true,
            'color' => $validated['color'],
            'student_id' => $student->id,
        ]);

        return redirect()
            ->route('student.calendar')
            ->with('status', 'Event added successfully.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        /** @var Student $student */
        $student = $request->user('student');

        if ((int) $event->student_id !== (int) $student->id) {
            abort(403);
        }

        $base = $this->resolveCalendarBase($student);

        if (! $base || (int) $event->event_id !== (int) $base->event_id) {
            abort(404);
        }

        $validated = $this->validateEventPayload($request);
        $this->ensureEventDoesNotOverlap($base, $validated, $event->id);

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
            ->route('student.calendar')
            ->with('status', 'Event updated successfully.');
    }

    private function resolveCalendarBase(Student $student): ?EventBase
    {
        $semester = $this->normalizeSemester((string) $student->current_semester);
        $section = $this->normalizeSection((string) $student->current_section);

        return EventBase::query()
            ->where('department', $student->department)
            ->where('semester', $semester)
            ->where('section', $section)
            ->first();
    }

    private function validateEventPayload(Request $request): array
    {
        $validated = $request->validate([
            'task' => ['bail', 'required', 'string', 'max:255'],
            'event_date' => ['nullable', 'date'],
            'day' => ['bail', 'required_without:event_date', 'nullable', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'start_hour' => ['bail', 'required', 'integer', 'between:0,23'],
            'start_min' => ['bail', 'required', 'integer', 'between:0,59'],
            'end_hour' => ['bail', 'required', 'integer', 'between:0,23'],
            'end_min' => ['bail', 'required', 'integer', 'between:0,59'],
            'color' => ['bail', 'required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if (! empty($validated['event_date'])) {
            $validated['day'] = strtolower(Carbon::parse($validated['event_date'])->englishDayOfWeek);
        }

        $validated['start_hour'] = (int) $validated['start_hour'];
        $validated['start_min'] = (int) $validated['start_min'];
        $validated['end_hour'] = (int) $validated['end_hour'];
        $validated['end_min'] = (int) $validated['end_min'];

        return $validated;
    }

    private function ensureEventDoesNotOverlap(EventBase $base, array $candidate, ?int $ignoreEventId = null): void
    {
        /** @var Student $student */
        $student = request()->user('student');

        $existingEvents = $base->events()
            ->where(function ($query) use ($student): void {
                $query->whereNull('student_id')
                    ->orWhere('student_id', $student?->id);
            })
            ->when($ignoreEventId, fn ($query) => $query->where('id', '!=', $ignoreEventId))
            ->get();

        foreach ($existingEvents as $existingEvent) {
            if ($this->eventsOverlap($candidate, $existingEvent)) {
                throw ValidationException::withMessages([
                    'start_hour' => 'This time overlaps with an existing calendar event.',
                ]);
            }
        }
    }

    private function eventsOverlap(array $candidate, Event $existingEvent): bool
    {
        $candidateHasDate = ! empty($candidate['event_date']);
        $existingHasDate = ! empty($existingEvent->event_date);

        if ($candidateHasDate && $existingHasDate) {
            return $this->dateSegmentsOverlap(
                $this->buildDateSegments($candidate['event_date'], $candidate['start_hour'], $candidate['start_min'], $candidate['end_hour'], $candidate['end_min']),
                $this->buildDateSegments(optional($existingEvent->event_date)->toDateString(), (int) $existingEvent->start_hour, (int) $existingEvent->start_min, (int) $existingEvent->end_hour, (int) $existingEvent->end_min)
            );
        }

        if ($candidateHasDate || $existingHasDate) {
            $datedEvent = $candidateHasDate
                ? $this->buildDateSegments($candidate['event_date'], $candidate['start_hour'], $candidate['start_min'], $candidate['end_hour'], $candidate['end_min'])
                : $this->buildDateSegments(optional($existingEvent->event_date)->toDateString(), (int) $existingEvent->start_hour, $existingEvent->start_min, (int) $existingEvent->end_hour, (int) $existingEvent->end_min);

            $recurringEvent = $candidateHasDate
                ? $this->buildRecurringSegments($this->dayToIndex($existingEvent->day), (int) $existingEvent->start_hour, (int) $existingEvent->start_min, (int) $existingEvent->end_hour, (int) $existingEvent->end_min)
                : $this->buildRecurringSegments($this->dayToIndex($candidate['day']), (int) $candidate['start_hour'], (int) $candidate['start_min'], (int) $candidate['end_hour'], (int) $candidate['end_min']);

            return $this->daySegmentsOverlap($datedEvent, $recurringEvent);
        }

        return $this->daySegmentsOverlap(
            $this->buildRecurringSegments($this->dayToIndex($candidate['day']), (int) $candidate['start_hour'], (int) $candidate['start_min'], (int) $candidate['end_hour'], (int) $candidate['end_min']),
            $this->buildRecurringSegments($this->dayToIndex($existingEvent->day), (int) $existingEvent->start_hour, (int) $existingEvent->start_min, (int) $existingEvent->end_hour, (int) $existingEvent->end_min)
        );
    }

    private function daySegmentsOverlap(array $leftSegments, array $rightSegments): bool
    {
        foreach ($leftSegments as $leftSegment) {
            foreach ($rightSegments as $rightSegment) {
                if ((int) $leftSegment['dayIndex'] !== (int) $rightSegment['dayIndex']) {
                    continue;
                }

                if ($this->minuteRangesOverlap(
                    (int) $leftSegment['startMinute'],
                    (int) $leftSegment['endMinute'],
                    (int) $rightSegment['startMinute'],
                    (int) $rightSegment['endMinute']
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function dateSegmentsOverlap(array $leftSegments, array $rightSegments): bool
    {
        foreach ($leftSegments as $leftSegment) {
            foreach ($rightSegments as $rightSegment) {
                if ($leftSegment['date'] !== $rightSegment['date']) {
                    continue;
                }

                if ($this->minuteRangesOverlap(
                    (int) $leftSegment['startMinute'],
                    (int) $leftSegment['endMinute'],
                    (int) $rightSegment['startMinute'],
                    (int) $rightSegment['endMinute']
                )) {
                    return true;
                }
            }
        }

        return false;
    }

    private function minuteRangesOverlap(int $leftStart, int $leftEnd, int $rightStart, int $rightEnd): bool
    {
        return $leftStart < $rightEnd && $rightStart < $leftEnd;
    }

    private function buildRecurringSegments(int $dayIndex, int $startHour, int $startMin, int $endHour, int $endMin): array
    {
        $startMinute = ($startHour * 60) + $startMin;
        $endMinute = ($endHour * 60) + $endMin;

        $segments = [
            [
                'dayIndex' => $dayIndex,
                'startMinute' => $startMinute,
                'endMinute' => $endMinute > $startMinute ? $endMinute : 24 * 60,
            ],
        ];

        if ($endMinute <= $startMinute) {
            $segments[] = [
                'dayIndex' => ($dayIndex + 1) % 7,
                'startMinute' => 0,
                'endMinute' => $endMinute,
            ];
        }

        return $segments;
    }

    private function buildDateSegments(string $date, int $startHour, int $startMin, int $endHour, int $endMin): array
    {
        $startMinute = ($startHour * 60) + $startMin;
        $endMinute = ($endHour * 60) + $endMin;
        $startDate = Carbon::parse($date);

        $segments = [
            [
                'date' => $startDate->toDateString(),
                'dayIndex' => ((int) $startDate->dayOfWeekIso) - 1,
                'startMinute' => $startMinute,
                'endMinute' => $endMinute > $startMinute ? $endMinute : 24 * 60,
            ],
        ];

        if ($endMinute <= $startMinute) {
            $nextDate = $startDate->copy()->addDay();

            $segments[] = [
                'date' => $nextDate->toDateString(),
                'dayIndex' => ((int) $nextDate->dayOfWeekIso) - 1,
                'startMinute' => 0,
                'endMinute' => $endMinute,
            ];
        }

        return $segments;
    }

    private function dayToIndex(string $day): int
    {
        return match (strtolower($day)) {
            'monday' => 0,
            'tuesday' => 1,
            'wednesday' => 2,
            'thursday' => 3,
            'friday' => 4,
            'saturday' => 5,
            'sunday' => 6,
            default => 0,
        };
    }

    private function normalizeSemester(string $semester): string
    {
        $semester = trim($semester);

        return match (strtolower($semester)) {
            'semester i', 'sem 1', '1' => '1',
            'semester ii', 'sem 2', '2' => '2',
            default => $semester,
        };
    }

    private function normalizeSection(string $section): string
    {
        $section = trim($section);

        if (preg_match('/(\d+)/', $section, $matches)) {
            return $matches[1];
        }

        return $section;
    }

    private function mapEventForView(Event $event, Student $student, array $dayToIndex): array
    {
        $mine = (int) $event->student_id === (int) $student->id;
        $eventDate = $event->event_date ? Carbon::parse($event->event_date) : null;
        $start = $this->formatClock((int) $event->start_hour, (int) $event->start_min);
        $end = $this->formatClock((int) $event->end_hour, (int) $event->end_min);
        $isOvernight = ((int) $event->end_hour * 60 + (int) $event->end_min) <= ((int) $event->start_hour * 60 + (int) $event->start_min);

        return [
            'id' => $event->id,
            'title' => $event->task,
            'day' => $dayToIndex[$event->day] ?? 0,
            'dayName' => ucfirst((string) $event->day),
            'startHour' => (int) $event->start_hour,
            'startMin' => (int) $event->start_min,
            'endHour' => (int) $event->end_hour,
            'endMin' => (int) $event->end_min,
            'tone' => $event->color,
            'eventDate' => $eventDate?->toDateString(),
            'displayDateLabel' => $eventDate ? $eventDate->format('M j, Y') : 'Every ' . ucfirst((string) $event->day),
            'displayRangeLabel' => $eventDate
                ? $eventDate->format('M j, Y') . ' · ' . $start . ' - ' . $end
                : ucfirst((string) $event->day) . ' · ' . $start . ' - ' . $end,
            'displayTimeLabel' => $start . ' - ' . $end,
            'isOvernight' => $isOvernight,
            'sourceLabel' => $mine ? 'Your event' : (strtolower((string) $event->source) === 'admin' ? 'Admin event' : 'Shared event'),
            'canEdit' => $mine,
            'mine' => $mine,
        ];
    }

    private function formatClock(int $hour, int $minute): string
    {
        return sprintf('%02d:%02d', $hour, $minute);
    }
}