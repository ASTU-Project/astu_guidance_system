@extends('layouts.student')

@section('title', 'Calendar')
@section('page-title', 'Calendar')

@section('content')
    <div class="space-y-5">
        @if(session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-medium">Please fix the following:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Student Calendar</p>
                    <h3 class="text-lg font-semibold text-slate-950">Weekly schedule</h3>
                    @if($base)
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $base->department }} · Semester {{ $base->semester }} · Section {{ $base->section }}
                        </p>
                    @else
                        <p class="mt-1 text-sm text-amber-600">
                            No calendar base matches your profile yet.
                        </p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="openCalendarFormModal()"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(! $base)
                    >
                        <i class="fa fa-plus text-[11px]"></i>
                        Add Event
                    </button>
                    <button
                        type="button"
                        onclick="openSpecificEventsModal()"
                        class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                        @disabled(! $base)
                    >
                        <i class="fa fa-calendar-days text-[11px]"></i>
                        Specific Events
                    </button>
                </div>
            </div>

            <div class="border-b border-slate-100 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" id="prevWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">‹</button>
                        <button type="button" id="todayWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Today</button>
                        <button type="button" id="nextWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">›</button>
                    </div>

                    <div>
                        <div id="staticDateRange" class="text-sm font-medium text-slate-700"></div>
                    </div>
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <div class="min-w-[940px]">
                    <div id="calendarPreview" class="border border-slate-200 bg-white shadow-sm"></div>
                </div>
            </div>
        </div>

    </div>

    <div
        id="specific-events-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="specific-events-title"
    >
        <div class="w-full max-w-2xl overflow-hidden rounded-md bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Specific events</p>
                    <h3 id="specific-events-title" class="text-lg font-semibold text-slate-900">Click any event for details</h3>
                </div>
                <button type="button" onclick="closeSpecificEventsModal()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <div class="max-h-[70vh] divide-y divide-slate-100 overflow-y-auto">
                @forelse($specificEvents ?? [] as $event)
                    <button
                        type="button"
                        class="flex w-full flex-col gap-1 px-5 py-4 text-left hover:bg-slate-50"
                        onclick="openEventDetails({{ $event['id'] }})"
                    >
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-sm font-semibold text-slate-900">{{ $event['title'] }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $event['displayRangeLabel'] }}</div>
                            </div>
                            <span class="rounded-full border border-slate-200 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                {{ $event['sourceLabel'] }}
                            </span>
                        </div>
                    </button>
                @empty
                    <div class="px-5 py-6 text-sm text-slate-500">No date-specific events found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div
        id="event-detail-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="event-detail-title"
    >
        <div class="w-full max-w-2xl overflow-hidden rounded-md bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Event details</p>
                    <h3 id="event-detail-title" class="text-lg font-semibold text-slate-900"></h3>
                </div>
                <button type="button" onclick="closeEventDetailModal()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <div class="space-y-4 px-5 py-5 sm:px-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span id="event-detail-source" class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700"></span>
                    <span id="event-detail-ownership" class="rounded-full border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600"></span>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">When</div>
                        <div id="event-detail-when" class="mt-2 text-sm font-medium text-slate-900"></div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Time</div>
                        <div id="event-detail-time" class="mt-2 text-sm font-medium text-slate-900"></div>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-400">Notes</div>
                    <div id="event-detail-notes" class="mt-2 text-sm text-slate-600"></div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" id="event-detail-edit-btn" class="hidden inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Edit Event</button>
                </div>
            </div>
        </div>
    </div>

    <div
        id="calendar-form-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="calendar-form-title"
    >
        <div class="w-full max-w-3xl overflow-hidden rounded-md bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <p id="calendar-form-kicker" class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Create Event</p>
                    <h3 id="calendar-form-title" class="text-lg font-semibold text-slate-900">Add to your calendar</h3>
                </div>
                <button type="button" onclick="closeCalendarFormModal()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <form id="calendarForm" method="POST" action="{{ route('student.calendar.store') }}">
                @csrf
                @php($selectedCreateColor = old('color', '#083D77'))
                @php($selectedEventId = old('event_id'))
                <input type="hidden" id="calendarFormMethod" name="_method" value="">
                <div class="grid gap-0 xl:grid-cols-[1fr_0.9fr]">
                    <div class="border-b border-slate-200 p-5 xl:border-b-0 xl:border-r sm:p-6">
                        <div class="space-y-4">
                            <input type="hidden" id="calendarFormEventId" name="event_id" value="{{ $selectedEventId }}">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                                <input id="calendarFormTask" name="task" type="text" value="{{ old('task') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" placeholder="Applied Maths I" required>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Date</label>
                                <input id="calendarFormDate" name="event_date" type="date" value="{{ old('event_date') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400">
                                <p class="mt-1 text-xs text-slate-500">Leave it empty for a weekly recurring event.</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Day</label>
                                <select id="calendarFormDay" name="day" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" required>
                                    <option value="monday" @selected(old('day', 'monday') === 'monday')>Monday</option>
                                    <option value="tuesday" @selected(old('day') === 'tuesday')>Tuesday</option>
                                    <option value="wednesday" @selected(old('day') === 'wednesday')>Wednesday</option>
                                    <option value="thursday" @selected(old('day') === 'thursday')>Thursday</option>
                                    <option value="friday" @selected(old('day') === 'friday')>Friday</option>
                                    <option value="saturday" @selected(old('day') === 'saturday')>Saturday</option>
                                    <option value="sunday" @selected(old('day') === 'sunday')>Sunday</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Start</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input id="calendarFormStartHour" name="start_hour" type="number" min="0" max="23" value="{{ old('start_hour') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" placeholder="10" required>
                                        <input id="calendarFormStartMin" name="start_min" type="number" min="0" max="59" value="{{ old('start_min') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" placeholder="00" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">End</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input id="calendarFormEndHour" name="end_hour" type="number" min="0" max="23" value="{{ old('end_hour') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" placeholder="12" required>
                                        <input id="calendarFormEndMin" name="end_min" type="number" min="0" max="59" value="{{ old('end_min') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition focus:border-slate-400" placeholder="00" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-5 sm:p-6">
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Color</label>
                                <input id="calendarFormColor" name="color" type="hidden" value="{{ $selectedCreateColor }}">
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#083D77' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#083D77" style="background-color:#083D77; --tw-ring-color:#083D77"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#0CCE6B' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#0CCE6B" style="background-color:#0CCE6B"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#DCED31' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#DCED31" style="background-color:#DCED31"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#EF2D56' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#EF2D56" style="background-color:#EF2D56"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#ED7D3A' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#ED7D3A" style="background-color:#ED7D3A"></button>
                                </div>
                            </div>

                            <div id="popupEventPreview" class="rounded-lg px-4 py-3 text-white shadow-sm" style="background-color:#083D77">
                                <div id="popupEventPreviewTitle" class="text-sm font-semibold">Applied Maths I</div>
                                <div id="popupEventPreviewMeta" class="mt-1 text-xs text-white/85">Mon • 01:00 - 01:40</div>
                                <div id="popupEventPreviewHint" class="mt-2 text-xs text-white/75">Choose a color and keep an eye on overnight events.</div>
                            </div>

                            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-600">
                                Only your events can be edited. Shared or admin events are read-only.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                    <button type="button" onclick="closeCalendarFormModal()" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                    <button id="calendarFormSubmit" type="submit" class="inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Save Event</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .calendar-preview-grid {
            display: grid;
            grid-template-columns: 78px repeat(7, minmax(120px, 1fr));
        }

        .calendar-preview-slot {
            position: relative;
            min-height: 26px;
            border-right: 1px solid rgb(226 232 240);
            border-bottom: 1px solid rgb(226 232 240);
            background: white;
            overflow: visible;
        }

        .calendar-preview-slot:nth-child(8n) {
            border-right: 0;
        }

        .calendar-preview-hour {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: 26px;
            border-right: 1px solid rgb(226 232 240);
            border-bottom: 1px solid rgb(226 232 240);
            background: rgb(248 250 252);
            padding-top: 10px;
            font-size: 11px;
            font-weight: 600;
            color: rgb(100 116 139);
        }

        .calendar-preview-head {
            display: grid;
            grid-template-columns: 78px repeat(7, minmax(120px, 1fr));
            border-bottom: 1px solid rgb(226 232 240);
            background: rgb(248 250 252);
        }

        .calendar-preview-head > div {
            padding: 14px 10px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.16em;
            color: rgb(100 116 139);
            border-right: 1px solid rgb(226 232 240);
        }

        .calendar-preview-head > div:last-child {
            border-right: 0;
        }

        .calendar-preview-head .calendar-today-head {
            background: rgb(224 231 255);
            color: rgb(67 56 202);
        }

        .calendar-preview-slot.calendar-today-col {
            background: rgb(238 242 255);
        }

        .calendar-event-card {
            position: absolute;
            inset: 2px 2px auto 2px;
            padding: 6px 8px;
            color: white;
            z-index: 10;
            border-radius: 0.375rem;
            box-shadow: 0 10px 20px -16px rgba(15, 23, 42, 0.55);
        }

        .calendar-event-card.default-tone {
            background: #083D77;
        }

        .calendar-event-card.student-tone {
            background: #0f172a;
        }

        .calendar-event-title {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

        .calendar-event-meta {
            margin-top: 4px;
            font-size: 10px;
            line-height: 1.2;
            opacity: 0.85;
        }

        .calendar-event-card.has-overflow {
            border-style: dashed;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const calendarDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const calendarEvents = @json($calendarEvents ?? []);
        const calendarEventsById = Object.fromEntries(calendarEvents.map((event) => [String(event.id), event]));
        const baseWeekStart = getStartOfWeek(new Date());
        let currentWeekOffset = 0;
        let activeDetailEvent = null;
        let activeEditEvent = null;

        function padTime(value) {
            return String(value).padStart(2, '0');
        }

        function formatTime(hour, minute) {
            return `${padTime(hour)}:${padTime(minute)}`;
        }

        function getStartOfWeek(date) {
            const weekStart = new Date(date);
            weekStart.setHours(0, 0, 0, 0);

            const day = weekStart.getDay();
            const diff = day === 0 ? -6 : 1 - day;
            weekStart.setDate(weekStart.getDate() + diff);

            return weekStart;
        }

        function getWeekDates(offset) {
            const startOfWeek = new Date(baseWeekStart);
            startOfWeek.setDate(baseWeekStart.getDate() + (offset * 7));

            return Array.from({ length: 7 }, (_, index) => {
                const date = new Date(startOfWeek);
                date.setDate(startOfWeek.getDate() + index);
                return date;
            });
        }

        function formatDateRange(dates) {
            const options = { month: 'short', day: 'numeric' };
            return `${dates[0].toLocaleDateString('en-US', options)} – ${dates[6].toLocaleDateString('en-US', options)}, ${dates[6].getFullYear()}`;
        }

        function toIsoDate(date) {
            return `${date.getFullYear()}-${padTime(date.getMonth() + 1)}-${padTime(date.getDate())}`;
        }

        function buildSlots() {
            const slots = [];

            for (let hour = 0; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 10) {
                    slots.push({ hour, minute });
                }
            }

            return slots;
        }

        function eventDuration(event) {
            const start = (event.startHour * 60) + event.startMin;
            const end = (event.endHour * 60) + event.endMin;

            return Math.max(1, ((end > start ? end : end + (24 * 60)) - start) / 10);
        }

        function eventSegments(event) {
            const start = (event.startHour * 60) + event.startMin;
            const end = (event.endHour * 60) + event.endMin;
            const isOvernight = end <= start;
            const dayIndex = event.eventDate
                ? (new Date(`${event.eventDate}T00:00:00`).getDay() + 6) % 7
                : event.day;
            const segments = [
                {
                    dayIndex,
                    startMinute: start,
                    endMinute: isOvernight ? 24 * 60 : end,
                    date: event.eventDate || null,
                },
            ];

            if (isOvernight) {
                segments.push({
                    dayIndex: (dayIndex + 1) % 7,
                    startMinute: 0,
                    endMinute: end,
                    date: event.eventDate ? nextDateString(event.eventDate) : null,
                });
            }

            return segments;
        }

        function nextDateString(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            date.setDate(date.getDate() + 1);

            return toIsoDate(date);
        }

        function formatEventDay(event) {
            if (event.eventDate) {
                return event.displayDateLabel;
            }

            return `Every ${event.dayName}`;
        }

        function formatEventNotes(event) {
            const notes = [];

            notes.push(event.sourceLabel || 'Calendar event');

            if (event.isOvernight) {
                notes.push('Overnight event spans into the next day.');
            }

            if (event.canEdit) {
                notes.push('You can edit this event.');
            } else {
                notes.push('This event is read-only.');
            }

            return notes.join(' ');
        }

        function renderSegmentStyle(event, segment) {
            const color = event.tone || '#083D77';
            const suffix = segment.endMinute === 24 * 60 ? ' border-r-0' : '';

            return { color, suffix };
        }

        function renderCalendarPreview() {
            const preview = document.getElementById('calendarPreview');
            const dateRange = document.getElementById('staticDateRange');
            const slots = buildSlots();
            const weekDates = getWeekDates(currentWeekOffset);
            const today = new Date();
            const todayKey = `${today.getFullYear()}-${today.getMonth()}-${today.getDate()}`;
            const todayIndex = weekDates.findIndex((date) => `${date.getFullYear()}-${date.getMonth()}-${date.getDate()}` === todayKey);

            if (dateRange) {
                dateRange.textContent = formatDateRange(weekDates);
            }

            const header = `
                <div class="calendar-preview-head">
                    <div>Time</div>
                    ${calendarDays.map((day, index) => {
                        const date = weekDates[index];
                        const dayNumber = date.getDate();
                        const dayClass = index === todayIndex ? 'calendar-today-head' : '';

                        return `<div class="${dayClass}">${day}<div class="mt-1 text-[10px] font-medium normal-case tracking-normal text-slate-400">${dayNumber}</div></div>`;
                    }).join('')}
                </div>
            `;

            const body = slots.map((slot) => {
                const rowCells = calendarDays.map((day, dayIndex) => {
                    const currentDate = weekDates[dayIndex];
                    const currentIsoDate = toIsoDate(currentDate);
                    const event = calendarEvents.find((item) => {
                        return eventSegments(item).some((segment) => {
                            const slotMinute = (slot.hour * 60) + slot.minute;

                            if (item.eventDate) {
                                return segment.dayIndex === dayIndex
                                    && segment.date === currentIsoDate
                                    && segment.startMinute === slotMinute;
                            }

                            return segment.dayIndex === dayIndex && segment.startMinute === slotMinute;
                        });
                    });

                    const slotClass = dayIndex === todayIndex ? 'calendar-preview-slot calendar-today-col' : 'calendar-preview-slot';

                    if (!event) {
                        return `<div class="${slotClass}"></div>`;
                    }

                    const segment = eventSegments(event).find((item) => item.dayIndex === dayIndex && item.startMinute === (slot.hour * 60) + slot.minute);
                    const segmentHeight = segment ? Math.max(1, (segment.endMinute - segment.startMinute) / 10) * 28 : eventDuration(event) * 28;
                    const eventClass = event.mine ? 'calendar-event-card student-tone' : 'calendar-event-card default-tone';
                    const eventStyle = event.tone ? `background-color:${event.tone};` : '';
                    const badge = event.mine ? '<div class="calendar-event-meta">Personal</div>' : '';
                    const overflowClass = segment && segment.endMinute === 24 * 60 ? ' has-overflow' : '';

                    return `
                        <div class="${slotClass}">
                            <button type="button" class="${eventClass}${overflowClass}" onclick="openEventDetails(${event.id})" style="height:${Math.max(26, segmentHeight - 4)}px;${eventStyle}">
                                <div class="calendar-event-title">${event.title}</div>
                                <div class="calendar-event-meta">${event.displayTimeLabel}${event.isOvernight ? ' +1' : ''}</div>
                                ${badge}
                            </button>
                        </div>
                    `;
                });

                return `
                    <div class="calendar-preview-grid">
                        <div class="calendar-preview-hour">${slot.minute === 0 ? formatTime(slot.hour, 0) : '&nbsp;'}</div>
                        ${rowCells.join('')}
                    </div>
                `;
            }).join('');

            preview.innerHTML = header + body;
        }

        function changeWeek(direction) {
            currentWeekOffset += direction;
            renderCalendarPreview();
        }

        function goToToday() {
            currentWeekOffset = 0;
            renderCalendarPreview();
        }

        function openCalendarFormModal(event = null) {
            const modal = document.getElementById('calendar-form-modal');
            const form = document.getElementById('calendarForm');
            const kicker = document.getElementById('calendar-form-kicker');
            const title = document.getElementById('calendar-form-title');
            const submit = document.getElementById('calendarFormSubmit');
            const methodInput = document.getElementById('calendarFormMethod');
            const eventIdInput = document.getElementById('calendarFormEventId');
            const taskInput = document.getElementById('calendarFormTask');
            const dateInput = document.getElementById('calendarFormDate');
            const dayInput = document.getElementById('calendarFormDay');
            const startHourInput = document.getElementById('calendarFormStartHour');
            const startMinInput = document.getElementById('calendarFormStartMin');
            const endHourInput = document.getElementById('calendarFormEndHour');
            const endMinInput = document.getElementById('calendarFormEndMin');
            const colorInput = document.getElementById('calendarFormColor');
            const preview = document.getElementById('popupEventPreview');
            const previewTitle = document.getElementById('popupEventPreviewTitle');
            const previewMeta = document.getElementById('popupEventPreviewMeta');
            const previewHint = document.getElementById('popupEventPreviewHint');

            if (!modal || !form) {
                return;
            }

            activeEditEvent = event;

            if (event) {
                form.action = `{{ route('student.calendar.update', ['event' => '__EVENT__']) }}`.replace('__EVENT__', String(event.id));
                if (methodInput) {
                    methodInput.value = 'PUT';
                }
                kicker.textContent = 'Edit Event';
                title.textContent = 'Update your event';
                submit.textContent = 'Save Changes';
                eventIdInput.value = event.id;
                taskInput.value = event.title || '';
                dateInput.value = event.eventDate || '';
                dayInput.value = dayNameToValue(event.dayName || 'Monday');
                startHourInput.value = event.startHour;
                startMinInput.value = event.startMin;
                endHourInput.value = event.endHour;
                endMinInput.value = event.endMin;

                if (colorInput) {
                    colorInput.value = event.tone || '#083D77';
                }

                if (preview) {
                    preview.style.backgroundColor = event.tone || '#083D77';
                }

                if (previewTitle) {
                    previewTitle.textContent = event.title || 'Untitled Event';
                }

                if (previewMeta) {
                    previewMeta.textContent = event.displayRangeLabel || '';
                }

                if (previewHint) {
                    previewHint.textContent = event.isOvernight ? 'This event spans overnight and will render across two days.' : 'Choose a color and keep the event details up to date.';
                }
            } else {
                form.action = "{{ route('student.calendar.store') }}";
                if (methodInput) {
                    methodInput.value = '';
                }
                form.reset();
                kicker.textContent = 'Create Event';
                title.textContent = 'Add to your calendar';
                submit.textContent = 'Save Event';
                eventIdInput.value = '';
                taskInput.value = '';
                dateInput.value = '';
                dayInput.value = 'monday';
                startHourInput.value = '';
                startMinInput.value = '';
                endHourInput.value = '';
                endMinInput.value = '';

                if (colorInput) {
                    colorInput.value = '#083D77';
                }

                if (preview) {
                    preview.style.backgroundColor = '#083D77';
                }

                if (previewTitle) {
                    previewTitle.textContent = 'Applied Maths I';
                }

                if (previewMeta) {
                    previewMeta.textContent = 'Mon • 01:00 - 01:40';
                }

                if (previewHint) {
                    previewHint.textContent = 'Choose a color and keep an eye on overnight events.';
                }
            }

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCalendarFormModal() {
            const modal = document.getElementById('calendar-form-modal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openSpecificEventsModal() {
            const modal = document.getElementById('specific-events-modal');

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeSpecificEventsModal() {
            const modal = document.getElementById('specific-events-modal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEventDetails(eventId) {
            const event = calendarEventsById[String(eventId)];
            const modal = document.getElementById('event-detail-modal');
            const title = document.getElementById('event-detail-title');
            const source = document.getElementById('event-detail-source');
            const ownership = document.getElementById('event-detail-ownership');
            const when = document.getElementById('event-detail-when');
            const time = document.getElementById('event-detail-time');
            const notes = document.getElementById('event-detail-notes');
            const editButton = document.getElementById('event-detail-edit-btn');

            if (!event || !modal) {
                return;
            }

            activeDetailEvent = event;

            if (title) {
                title.textContent = event.title || 'Untitled Event';
            }

            if (source) {
                source.textContent = event.sourceLabel || 'Calendar event';
            }

            if (ownership) {
                ownership.textContent = event.canEdit ? 'Editable' : 'Read only';
            }

            if (when) {
                when.textContent = formatEventDay(event);
            }

            if (time) {
                time.textContent = `${event.displayTimeLabel}${event.isOvernight ? ' (overnight)' : ''}`;
            }

            if (notes) {
                notes.textContent = formatEventNotes(event);
            }

            if (editButton) {
                if (event.canEdit) {
                    editButton.classList.remove('hidden');
                    editButton.onclick = () => {
                        closeEventDetailModal();
                        openCalendarFormModal(event);
                    };
                } else {
                    editButton.classList.add('hidden');
                    editButton.onclick = null;
                }
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEventDetailModal() {
            const modal = document.getElementById('event-detail-modal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function dayNameToValue(dayName) {
            const map = {
                Monday: 'monday',
                Tuesday: 'tuesday',
                Wednesday: 'wednesday',
                Thursday: 'thursday',
                Friday: 'friday',
                Saturday: 'saturday',
                Sunday: 'sunday',
            };

            return map[dayName] || 'monday';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const prevWeekBtn = document.getElementById('prevWeekBtn');
            const todayWeekBtn = document.getElementById('todayWeekBtn');
            const nextWeekBtn = document.getElementById('nextWeekBtn');
            const formModal = document.getElementById('calendar-form-modal');
            const detailModal = document.getElementById('event-detail-modal');
            const createEventColorInput = document.getElementById('calendarFormColor');

            document.querySelectorAll('.create-color-swatch').forEach((button) => {
                button.addEventListener('click', () => {
                    const color = button.dataset.color || '#083D77';

                    if (createEventColorInput) {
                        createEventColorInput.value = color;
                    }

                    const preview = document.getElementById('popupEventPreview');

                    if (preview) {
                        preview.style.backgroundColor = color;
                    }

                    document.querySelectorAll('.create-color-swatch').forEach((swatch) => {
                        swatch.classList.remove('ring-2', 'ring-offset-2', 'ring-offset-white');
                    });

                    button.classList.add('ring-2', 'ring-offset-2', 'ring-offset-white');
                });
            });

            if (prevWeekBtn) {
                prevWeekBtn.addEventListener('click', () => changeWeek(-1));
            }

            if (todayWeekBtn) {
                todayWeekBtn.addEventListener('click', goToToday);
            }

            if (nextWeekBtn) {
                nextWeekBtn.addEventListener('click', () => changeWeek(1));
            }

            if (formModal) {
                formModal.addEventListener('click', (event) => {
                    if (event.target === formModal) {
                        closeCalendarFormModal();
                    }
                });
            }

            if (detailModal) {
                detailModal.addEventListener('click', (event) => {
                    if (event.target === detailModal) {
                        closeEventDetailModal();
                    }
                });
            }

            const specificEventsModal = document.getElementById('specific-events-modal');

            if (specificEventsModal) {
                specificEventsModal.addEventListener('click', (event) => {
                    if (event.target === specificEventsModal) {
                        closeSpecificEventsModal();
                    }
                });
            }

            @if($errors->any())
                openCalendarFormModal();
            @endif

            goToToday();
        });
    </script>
@endpush
