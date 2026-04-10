@extends('layouts.admin')

@section('title', 'Students Managment')
@section('page-title', 'Students')

@section('content')
    <div class="space-y-4">
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium mb-1">Please fix the following:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <!-- Header -->
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="text-l font-semibold text-slate-800">
                    Students ({{count($students)}})
                </h3>
            </div>
            <div class="w-full lg:w-auto flex flex-col sm:flex-row gap-2">
                <form action="{{ route('admin.students.index') }}" method="GET" class="relative w-full sm:w-72">
                    <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search by name or ID"
                        class="h-9 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none"
                    >
                </form>
                <form action="{{ route('admin.students.index') }}" method="GET" class="flex gap-2">
                    <select
                        name="department"
                        class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
                    >
                        <option value="">All Departments</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                    </select>
                    <select
                        name="department"
                        class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
                    >
                        <option value="">Year</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
                        <i class="fa fa-filter text-[11px]"></i>
                        Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-left">
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Student Id</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Current Year</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Current Section</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Department</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">GPA</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-500">{{ $student->student_id }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm font-medium text-slate-800">{{ $student->name }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">Year {{ $student->current_year }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">{{ $student->current_section }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">{{ $student->department }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm">
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {{ number_format((float) $student->cgpa, 1) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 sm:px-5 py-8 text-center text-slate-400 text-sm">
                            No students found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 sm:px-5 py-4 border-t border-slate-100 text-xs text-slate-500">
            {{ $students->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        flex-wrap: wrap;
    }

    .pagination li {
        list-style: none;
    }

    .pagination a,
    .pagination span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0 0.625rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.375rem;
        background: #ffffff;
        color: #475569;
        font-size: 0.75rem;
        text-decoration: none;
    }

    .pagination a:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #0f172a;
    }

    .pagination .active span {
        background: #0f172a;
        border-color: #0f172a;
        color: #ffffff;
    }

    .pagination .disabled span {
        background: #f8fafc;
        color: #94a3b8;
        border-color: #e2e8f0;
    }
</style>
@endpush

@push('scripts')
{{-- Page specific scripts --}}
@endpush
