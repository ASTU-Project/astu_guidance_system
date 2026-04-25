@extends('layouts.admin')

@section('title', 'Subjects')
@section('page-title', 'Subjects')

@section('content')
<div class="space-y-4">
    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">

        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="text-l font-semibold text-slate-800">
                    Subjects ({{ number_format($subjects->total()) }})
                </h3>
            </div>
            <form action="{{ route('admin.subjects.index') }}" method="GET" class="w-full lg:w-auto flex flex-col sm:flex-row gap-2">
                <div class="relative w-full sm:w-64">
                    <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search by name or code"
                        class="h-9 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none"
                    >
                </div>
                <div class="flex gap-2 flex-wrap">
                    <select name="year" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="">All Years</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>Year {{ $year }}</option>
                        @endforeach
                    </select>
                    <select name="semester" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="">All Semesters</option>
                        <option value="1" @selected(request('semester') === '1')>Sem 1</option>
                        <option value="2" @selected(request('semester') === '2')>Sem 2</option>
                    </select>
                    <select name="credit_hours" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="">All Credits</option>
                        @foreach($creditHours as $ch)
                            <option value="{{ $ch }}" @selected((string) request('credit_hours') === (string) $ch)>{{ $ch }} cr</option>
                        @endforeach
                    </select>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
                        <i class="fa fa-filter text-[11px]"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['q','year','semester','credit_hours']))
                        <a href="{{ route('admin.subjects.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-left">
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Code</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Year</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Semester</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Credit Hours</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subjects as $subject)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 sm:px-5 py-3 text-sm font-mono text-slate-500">{{ $subject->code }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm font-medium text-slate-800">{{ $subject->name }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">Year {{ $subject->year }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">Sem {{ $subject->semester }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                {{ $subject->credit_hours }} cr
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 sm:px-5 py-8 text-center text-slate-400 text-sm">
                            No subjects found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3 sm:px-5 py-4 border-t border-slate-100 text-xs text-slate-500">
            {{ $subjects->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination { display:flex; align-items:center; gap:.375rem; flex-wrap:wrap; }
    .pagination li { list-style:none; }
    .pagination a, .pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:2rem; height:2rem; padding:0 .625rem; border:1px solid #e2e8f0; border-radius:.375rem; background:#fff; color:#475569; font-size:.75rem; text-decoration:none; }
    .pagination a:hover { background:#f8fafc; border-color:#cbd5e1; color:#0f172a; }
    .pagination .active span { background:#0f172a; border-color:#0f172a; color:#fff; }
    .pagination .disabled span { background:#f8fafc; color:#94a3b8; border-color:#e2e8f0; }
</style>
@endpush
