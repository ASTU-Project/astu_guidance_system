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
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
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
            <div class="rounded-md border border-slate-200 p-6 shadow-sm
                @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                    bg-emerald-50 border-emerald-200
                @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                    bg-amber-50 border-amber-200
                @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                    bg-rose-50 border-rose-200
                @else
                    bg-white
                @endif
            ">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em]
                            @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                                text-emerald-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                                text-amber-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                                text-rose-600
                            @else
                                text-slate-500
                            @endif
                        ">Academic Status</p>
                        <p class="mt-1 text-2xl font-bold
                            @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                                text-emerald-700
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                                text-amber-700
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                                text-rose-700
                            @else
                                text-slate-950
                            @endif
                        ">{{ $academicStatus ?? 'Excellent Standing' }}</p>
                        <p class="mt-1 text-sm
                            @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                                text-emerald-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                                text-amber-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                                text-rose-600
                            @else
                                text-slate-500
                            @endif
                        ">Based on your performance</p>
                    </div>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                        @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                            bg-emerald-100
                        @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                            bg-amber-100
                        @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                            bg-rose-100
                        @else
                            bg-slate-100
                        @endif
                    ">
                        <i class="fa-solid
                            @if(($academicStatus ?? 'Excellent Standing') === 'Excellent Standing')
                                fa-trophy text-emerald-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'Good Standing')
                                fa-check-circle text-amber-600
                            @elseif(($academicStatus ?? 'Excellent Standing') === 'At Risk')
                                fa-exclamation-triangle text-rose-600
                            @else
                                fa-user-graduate text-slate-600
                            @endif
                            text-xl"></i>
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
