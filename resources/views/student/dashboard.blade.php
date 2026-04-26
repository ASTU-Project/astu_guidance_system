@extends('layouts.student')

@section('title', 'Student Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-5">
        <!-- Welcome Section -->
        <div class="rounded-md bg-gradient-to-r from-slate-900 to-slate-800 border border-slate-700 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-white">Welcome, {{ auth('student')->user()->name ?? 'Student' }}</h2>
                    <p class="mt-1 text-slate-300">Guide your university life with smart system!</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <!-- Total Credit Hours Card -->
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Credit Hours</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $totalCreditHours ?? '15' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Total taken this semester</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fa-solid fa-clock text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Current Year GPA Card -->
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Current GPA</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $currentGPA ?? '3.75' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Year {{ auth('student')->user()->current_year ?? 'N/A' }} performance</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                        <i class="fa-solid fa-chart-line text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Subjects Card -->
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Total Subjects</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $totalSubjects ?? '6' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Registered this semester</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                        <i class="fa-solid fa-book-open text-amber-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Academic Status Card -->
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Academic Status</p>
                        <p class="mt-1 text-2xl font-bold text-slate-950">{{ $academicStatus ?? 'Excellent' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Based on your performance</p>
                    </div>
                    <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                        <i class="fa-solid fa-trophy text-emerald-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    {{-- Page specific styles --}}
@endpush

@push('scripts')
    {{-- Page specific scripts --}}
@endpush
