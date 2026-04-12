@extends('layouts.admin')

@section('title', 'Calendar Management')
@section('page-title', 'Calendar')

@section('content')
    <div class="space-y-5">
        @if(session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-medium">Please fix the following:</p>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Calendars ({{ $bases->count() }})</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="openCalendarModal()"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        <i class="fa fa-plus text-[11px]"></i>
                        Add Calendar
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Id</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Field</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Semester</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Section</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bases as $base)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-4 text-sm text-slate-600">{{$base->event_id}}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{$base->department}}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{$base->semester}}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">{{$base->section}}</td>
                            <td class="px-5 py-4 text-sm text-slate-600">
                                <div class="flex items-center gap-3">
                                    <a href="{{route('admin.calendar.events', $base->event_id)}}" class="hover:opacity-80" title="Edit">
                                        <i class="fa fa-edit text-green-500"></i>
                                    </a>
                                    <a href="#" class="hover:opacity-80" title="Delete">
                                        <i class="fa fa-trash text-red-500"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-4 text-center text-sm text-slate-600">No calendars Event found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div
        id="calendar-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 "
        role="dialog"
        aria-modal="true"
        aria-labelledby="calendar-modal-title"
    >
        <div class="w-full max-w-2xl rounded-l bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h4 id="calendar-modal-title" class="text-base font-semibold text-slate-900">Create Calendar</h4>
                <button
                    type="button"
                    onclick="closeCalendarModal()"
                    class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                >
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.calendar.store') }}" class="space-y-4 px-5 py-5">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Field</label>
                        <input
                            name="department"
                            type="text"
                            placeholder="e.g. Computer Science"
                            value="{{ old('department') }}"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400"
                            required
                        >
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Semester</label>
                        <select name="semester" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                            <option value="">Select semester</option>
                            <option value="1" @selected(old('semester') == '1')>1</option>
                            <option value="2" @selected(old('semester') == '2')>2</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Section</label>
                        <select name="section" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 outline-none focus:border-slate-400" required>
                            <option value="">Select section</option>
                            <option value="1" @selected(old('section') == '1')>1</option>
                            <option value="2" @selected(old('section') == '2')>2</option>
                            <option value="3" @selected(old('section') == '3')>3</option>
                        </select>
                    </div>
                    
                </div>
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button
                        type="button"
                        onclick="closeCalendarModal()"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Save Calendar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Page specific styles --}}
@endpush

@push('scripts')
    <script>
        @if($errors->any())
            window.addEventListener('DOMContentLoaded', () => {
                openCalendarModal();
            });
        @endif

        function openCalendarModal() {
            const modal = document.getElementById('calendar-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCalendarModal() {
            const modal = document.getElementById('calendar-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endpush