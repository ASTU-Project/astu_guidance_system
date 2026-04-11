@extends('layouts.admin')

@section('title', 'Mange Event')
@section('page-title', 'Manage Event')

@section('content')
    <div class="space-y-5">
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

                <div class="min-w-0 p-2 bg-slate-50/80">
                    <div class="space-y-2 border bg-white p-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Edit Details</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Name</label>
                                <input type="text" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition " placeholder="Applied Maths I">
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Day</label>
                                <select class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition ">
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Start</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" min="0" max="23" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition " placeholder="10">
                                        <input type="number" min="0" max="59" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition " placeholder="00">
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">End</label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" min="0" max="23" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition " placeholder="12">
                                        <input type="number" min="0" max="59" class="w-full rounded-md border border-slate-300 px-4 py-2 text-sm outline-none transition " placeholder="00">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Color</label>
                                <div class="flex flex-wrap gap-3">
                                    <button type="button" class="h-10 w-10 rounded-md ring-2 ring-offset-2 ring-offset-white" style="background-color:#083D77; --tw-ring-color:#083D77"></button>
                                    <button type="button" class="h-10 w-10 rounded-md" style="background-color:#0CCE6B"></button>
                                    <button type="button" class="h-10 w-10 rounded-md" style="background-color:#DCED31"></button>
                                    <button type="button" class="h-10 w-10 rounded-md" style="background-color:#EF2D56"></button>
                                    <button type="button" class="h-10 w-10 rounded-md" style="background-color:#ED7D3A"></button>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-4 sm:flex-row sm:justify-end xl:hidden">
                <button type="button" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                <button type="button" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save Event</button>
            </div>
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
        const staticEvents = [
            { title: 'Applied Maths I', day: 0, startHour: 1, startMin: 0, endHour: 1, endMin: 40, tone: '#083D77' },
            { title: 'English', day: 0, startHour: 8, startMin: 0, endHour: 9, endMin: 30, tone: '#083D77' },
            { title: 'Chemistry', day: 4, startHour: 13, startMin: 0, endHour: 14, endMin: 0, tone: '#083D77' },
            { title: 'Physics', day: 1, startHour: 15, startMin: 0, endHour: 16, endMin: 0, tone: '#083D77' },
        ];

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
                    const event = staticEvents.find((item) => item.day === dayIndex && item.startHour === slot.hour && item.startMin === slot.minute);
                    const slotClass = dayIndex === todayIndex ? 'calendar-preview-slot calendar-today-col' : 'calendar-preview-slot';

                    if (!event) {
                        return `<div class="${slotClass}"></div>`;
                    }

                    const height = Math.max(1, eventDuration(event)) * 28;
                    const eventClass = event.muted ? 'calendar-event-card muted' : 'calendar-event-card default-tone';
                    const eventStyle = event.muted ? '' : `background-color:${event.tone || '#083D77'};`;

                    return `
                        <div class="${slotClass}">
                            <div class="${eventClass}" style="height:${Math.max(26, height - 4)}px;${eventStyle}">
                                <div class="calendar-event-title">${event.title}</div>
                            </div>
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

        document.addEventListener('DOMContentLoaded', () => {
            const prevWeekBtn = document.getElementById('prevWeekBtn');
            const todayWeekBtn = document.getElementById('todayWeekBtn');
            const nextWeekBtn = document.getElementById('nextWeekBtn');

            if (prevWeekBtn) {
                prevWeekBtn.addEventListener('click', () => changeWeek(-1));
            }

            if (todayWeekBtn) {
                todayWeekBtn.addEventListener('click', goToToday);
            }

            if (nextWeekBtn) {
                nextWeekBtn.addEventListener('click', () => changeWeek(1));
            }

            goToToday();
        });
    </script>
@endpush