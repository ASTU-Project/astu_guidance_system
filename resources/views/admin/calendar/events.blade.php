@extends('layouts.admin')

@section('title', 'Mange Event')
@section('page-title', 'Manage Event')

@section('content')
    <div class="space-y-5">
        @if (session('status'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="grid xl:grid-cols-[minmax(0,1.35fr)_minmax(0,0.85fr)]">
                <div class="min-w-0 border-b border-slate-200 xl:border-b-0 xl:border-r">
                    <div class="flex flex-col gap-3 border-b border-slate-100 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex items-center gap-2">
                            <button type="button" id="prevWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">‹</button>
                            <button type="button" id="todayWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Today</button>
                            <button type="button" id="nextWeekBtn" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">›</button>
                        </div>
                        <div>
                            <div id="staticDateRange">May 18 – May 24, 2026</div>
                        </div>
                    </div>

                    <div class="w-full overflow-x-auto">
                        <div class="min-w-[860px]sm:min-w-[940px]">
                            <div id="calendarPreview" class="border border-slate-200 bg-white shadow-sm"></div>
                        </div>
                    </div>
                </div>

                <form id="createEventForm" method="POST" action="{{ route('admin.calendar.events.store', $base->event_id) }}" class="min-w-0 p-2 bg-slate-50/80">
                    @csrf
                    @php($selectedCreateColor = old('color', '#083D77'))
                    <div class="space-y-2 border bg-white p-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Edit Details</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                                <input name="task" type="text" value="{{ old('task') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="Applied Maths I" required>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Date</label>
                                <input name="event_date" type="date" value="{{ old('event_date') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition">
                                <p class="mt-1 text-xs text-slate-500">Leave it empty for recurring weekly events.</p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Day</label>
                                <select name="day" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" required>
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
                                        <input name="start_hour" type="number" min="0" max="23" value="{{ old('start_hour') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="10" required>
                                        <input name="start_min" type="number" min="0" max="59" value="{{ old('start_min') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="00" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">End</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input name="end_hour" type="number" min="0" max="23" value="{{ old('end_hour') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="12" required>
                                        <input name="end_min" type="number" min="0" max="59" value="{{ old('end_min') }}" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="00" required>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Color</label>
                                <input id="createEventColor" name="color" type="hidden" value="{{ $selectedCreateColor }}">
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#083D77' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#083D77" style="background-color:#083D77; --tw-ring-color:#083D77"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#0CCE6B' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#0CCE6B" style="background-color:#0CCE6B"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#DCED31' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#DCED31" style="background-color:#DCED31"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#EF2D56' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#EF2D56" style="background-color:#EF2D56"></button>
                                    <button type="button" class="create-color-swatch h-10 w-10 rounded-md {{ $selectedCreateColor === '#ED7D3A' ? 'ring-2 ring-offset-2 ring-offset-white' : '' }}" data-color="#ED7D3A" style="background-color:#ED7D3A"></button>
                                </div>
                            </div>
                            

                            <div class="flex flex-col gap-3 border-t border-slate-100 sm:flex-row">
                                <button type="reset" form="createEventForm" class="w-full inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                                <button type="submit" form="createEventForm" class="w-full inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Event</button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="eventEditModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-md bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">Update Event</h3>
                </div>
                <button type="button" id="closeEventEditModal" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Close</button>
            </div>

            <form id="updateEventForm" method="POST" action="{{ route('admin.calendar.events.update', ['id' => $base->event_id, 'event' => '__EVENT_ID__']) }}">
                @csrf
                @method('PUT')
                <input type="hidden" id="popupEventColor" name="color" value="#083D77">

            <div class="grid gap-0 xl:grid-cols-[1fr_0.9fr]">
                <div class="border-b border-slate-200 p-5 xl:border-b-0 xl:border-r sm:p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                            <input id="popupEventTitle" name="task" type="text" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="Applied Maths I" required>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Date</label>
                            {{-- Leave empty for recurring weekly events. --}}
                            <input id="popupEventDate" name="event_date" type="date" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition">
                            
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Day</label>
                            <select id="popupEventDay" name="day" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Start</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input id="popupEventStartHour" name="start_hour" type="number" min="0" max="23" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="10" required>
                                    <input id="popupEventStartMin" name="start_min" type="number" min="0" max="59" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="00" required>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">End</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input id="popupEventEndHour" name="end_hour" type="number" min="0" max="23" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="12" required>
                                    <input id="popupEventEndMin" name="end_min" type="number" min="0" max="59" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition" placeholder="00" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-3 sm:p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Color</label>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" class="event-color-swatch h-10 w-10 rounded-md ring-2 ring-offset-2 ring-offset-white" data-color="#083D77" style="background-color:#083D77; --tw-ring-color:#083D77"></button>
                                <button type="button" class="event-color-swatch h-10 w-10 rounded-md" data-color="#0CCE6B" style="background-color:#0CCE6B"></button>
                                <button type="button" class="event-color-swatch h-10 w-10 rounded-md" data-color="#DCED31" style="background-color:#DCED31"></button>
                                <button type="button" class="event-color-swatch h-10 w-10 rounded-md" data-color="#EF2D56" style="background-color:#EF2D56"></button>
                                <button type="button" class="event-color-swatch h-10 w-10 rounded-md" data-color="#ED7D3A" style="background-color:#ED7D3A"></button>
                            </div>
                        </div>
                        
                        <div id="popupEventPreview" class="mt-3 rounded-lg px-4 py-3 text-white shadow-sm" style="background-color:#083D77">
                            <div id="popupEventPreviewTitle" class="text-sm font-semibold">Applied Maths I</div>
                            <div id="popupEventPreviewMeta" class="mt-1 text-xs text-white/85">Mon • 01:00 - 01:40</div>
                        </div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" id="cancelEventEditModal" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="submit" form="updateEventForm" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Event</button>
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
            background: rgb(248 250 260);
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
            cursor: pointer;
            z-index: 10;
        }

        .calendar-event-card.default-tone {
            background: #083D77;
        }

        .calendar-event-card.muted {
            color: rgb(51 65 85);
            background: rgb(248 250 252);
            border: 1px dashed rgb(203 213 225);
            box-shadow: none;
        }

        .calendar-event-title {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }

    </style>
@endpush

@push('scripts')
    <script>
        const calendarDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        const baseWeekStart = new Date('2026-05-18T00:00:00');
        let currentWeekOffset = 0;
        const calendarEvents = @json($calendarEvents ?? []);

        function padTime(value) {
            return String(value).padStart(2, '0');
        }   

        function formatTime(hour, minute) {
            return `${padTime(hour)}:${padTime(minute)}`;
        }

        function eventDuration(event) {
            return ((event.endHour * 60 + event.endMin) - (event.startHour * 60 + event.startMin)) / 10;
        }

        function getStartOfWeek(date) {
            const weekStart = new Date(date);
            weekStart.setHours(0, 0, 0, 0);
            const day = weekStart.getDay();
            const diff = day === 0 ? -6 : 1 - day;
            weekStart.setDate(weekStart.getDate() + diff);
            return weekStart;
        }

        function getTodayWeekOffset() {
            const weekInMs = 7 * 24 * 60 * 60 * 1000;
            const baseStart = getStartOfWeek(baseWeekStart);
            const todayStart = getStartOfWeek(new Date());
            return Math.round((todayStart.getTime() - baseStart.getTime()) / weekInMs);
        }

        function buildSlots() {
            const slots = [];

            for (let hour = 1; hour < 24; hour++) {
                for (let minute = 0; minute < 60; minute += 10) {
                    slots.push({ hour, minute });
                }
            }

            return slots;
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

        function renderCalendarPreview() {
            const preview = document.getElementById('calendarPreview');
            const slots = buildSlots();
            const dateRange = document.getElementById('staticDateRange');
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
                        const timeMatch = item.startHour === slot.hour && item.startMin === slot.minute;

                        if (!timeMatch) {
                            return false;
                        }

                        if (item.eventDate) {
                            return item.eventDate === currentIsoDate;
                        }

                        return item.day === dayIndex;
                    });
                    const slotClass = dayIndex === todayIndex ? 'calendar-preview-slot calendar-today-col' : 'calendar-preview-slot';

                    if (!event) {
                        return `<div class="${slotClass}"></div>`;
                    }

                    const height = Math.max(1, eventDuration(event)) * 28;
                    const eventClass = event.muted ? 'calendar-event-card muted' : 'calendar-event-card default-tone';
                    const eventStyle = event.muted ? '' : `background-color:${event.tone || '#083D77'};`;
                    const eventPayload = encodeURIComponent(JSON.stringify(event));

                    return `
                        <div class="${slotClass}">
                            <button type="button" class="${eventClass} js-event-card" data-event="${eventPayload}" style="height:${Math.max(26, height - 4)}px;${eventStyle}">
                                <div class="calendar-event-title">${event.title}</div>
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
            currentWeekOffset = getTodayWeekOffset();
            renderCalendarPreview();
        }

        function formatEventTime(hour, minute) {
            return `${padTime(hour)}:${padTime(minute)}`;
        }

        function openEventModal(event) {
            const modal = document.getElementById('eventEditModal');
            const updateForm = document.getElementById('updateEventForm');
            const titleInput = document.getElementById('popupEventTitle');
            const dateInput = document.getElementById('popupEventDate');
            const dayInput = document.getElementById('popupEventDay');
            const startHourInput = document.getElementById('popupEventStartHour');
            const startMinInput = document.getElementById('popupEventStartMin');
            const endHourInput = document.getElementById('popupEventEndHour');
            const endMinInput = document.getElementById('popupEventEndMin');
            const colorInput = document.getElementById('popupEventColor');
            const preview = document.getElementById('popupEventPreview');
            const previewTitle = document.getElementById('popupEventPreviewTitle');
            const previewMeta = document.getElementById('popupEventPreviewMeta');
            const dayNames = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            if (!modal) {
                return;
            }

            titleInput.value = event.title || '';
            dateInput.value = event.eventDate || '';
            dayInput.value = dayNames[event.day] || 'monday';
            startHourInput.value = event.startHour;
            startMinInput.value = event.startMin;
            endHourInput.value = event.endHour;
            endMinInput.value = event.endMin;

            const color = event.tone || '#083D77';
            if (colorInput) {
                colorInput.value = color;
            }

            if (updateForm && event.id) {
                updateForm.action = `{{ route('admin.calendar.events.update', ['id' => $base->event_id, 'event' => '__EVENT_ID__']) }}`.replace('__EVENT_ID__', String(event.id));
            }

            preview.style.backgroundColor = color;
            previewTitle.textContent = event.title || 'Untitled Event';
            previewMeta.textContent = `${dayInput.value.slice(0, 1).toUpperCase()}${dayInput.value.slice(1)} • ${formatEventTime(event.startHour, event.startMin)} - ${formatEventTime(event.endHour, event.endMin)}`;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEventModal() {
            const modal = document.getElementById('eventEditModal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const prevWeekBtn = document.getElementById('prevWeekBtn');
            const todayWeekBtn = document.getElementById('todayWeekBtn');
            const nextWeekBtn = document.getElementById('nextWeekBtn');
            const calendarPreview = document.getElementById('calendarPreview');
            const modal = document.getElementById('eventEditModal');
            const closeModalBtn = document.getElementById('closeEventEditModal');
            const cancelModalBtn = document.getElementById('cancelEventEditModal');
            const createEventColorInput = document.getElementById('createEventColor');

            document.querySelectorAll('.create-color-swatch').forEach((button) => {
                button.addEventListener('click', () => {
                    const color = button.dataset.color || '#083D77';

                    if (createEventColorInput) {
                        createEventColorInput.value = color;
                    }

                    document.querySelectorAll('.create-color-swatch').forEach((swatch) => {
                        swatch.classList.remove('ring-2', 'ring-offset-2', 'ring-offset-white');
                    });

                    button.classList.add('ring-2', 'ring-offset-2', 'ring-offset-white');
                });
            });

            document.querySelectorAll('.event-color-swatch').forEach((button) => {
                button.addEventListener('click', () => {
                    const color = button.dataset.color || '#083D77';
                    const preview = document.getElementById('popupEventPreview');
                    const popupColorInput = document.getElementById('popupEventColor');

                    if (preview) {
                        preview.style.backgroundColor = color;
                    }

                    if (popupColorInput) {
                        popupColorInput.value = color;
                    }

                    document.querySelectorAll('.event-color-swatch').forEach((swatch) => {
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

            if (calendarPreview) {
                calendarPreview.addEventListener('click', (event) => {
                    const eventCard = event.target.closest('.js-event-card');

                    if (!eventCard) {
                        return;
                    }

                    try {
                        const payload = JSON.parse(decodeURIComponent(eventCard.dataset.event || '{}'));
                        openEventModal(payload);
                    } catch (error) {
                        return;
                    }
                });
            }

            if (modal) {
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeEventModal();
                    }
                });
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeEventModal);
            }

            if (cancelModalBtn) {
                cancelModalBtn.addEventListener('click', closeEventModal);
            }

            goToToday();
        });
    </script>
@endpush