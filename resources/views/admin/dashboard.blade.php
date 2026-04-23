@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Students</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ number_format($number_of_students) }}</p>
                <p class="mt-1 text-sm text-slate-500">Active learners this semester</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Departments</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ number_format($number_of_departments) }}</p>
                <p class="mt-1 text-sm text-slate-500">Academic units available</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Events</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ number_format($number_of_events) }}</p>
                <p class="mt-1 text-sm text-slate-500">Calendar events recorded</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Year</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ date('Y') }}</p>
                <p class="mt-1 text-sm text-slate-500">Current academic year</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-[1.4fr_0.6fr] gap-4 items-start">
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">Performance Overview</h2>
                    </div>
                </div>

                <div class="mt-4 rounded border border-slate-200 bg-slate-50 p-2">
                    <div class="overflow-x-auto">
                        <div class="relative min-w-[680px] w-full h-[280px]">
                            <canvas id="performanceChart" class="w-full h-full" aria-label="Average GPA performance by year line graph" role="img"></canvas>
                            <div id="performanceChartEmpty" class="absolute inset-0 hidden items-center justify-center text-sm text-slate-500">
                                No grade data available yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-950">Top departments</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($top_departments as $index => $department)
                            @php
                                $badgeClasses = [
                                    'bg-emerald-100 text-emerald-700',
                                    'bg-cyan-100 text-cyan-700',
                                    'bg-sky-100 text-sky-700',
                                ];
                            @endphp
                            <div class="flex items-center justify-between gap-3 rounded-md bg-slate-50 p-4">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $department->department }}</p>
                                    <p class="text-xs text-slate-500">{{ number_format($department->total_students) }} students</p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses[$index] ?? 'bg-slate-200 text-slate-700' }}">{{ $department->percentage }}%</span>
                            </div>
                        @empty
                            <div class="rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500">
                                No department data available yet.
                            </div>
                        @endforelse
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartData = @json($performance_chart ?? []);
            const canvas = document.getElementById('performanceChart');
            const emptyState = document.getElementById('performanceChartEmpty');

            if (!canvas) {
                return;
            }

            if (!chartData.length) {
                canvas.classList.add('hidden');
                emptyState?.classList.remove('hidden');
                emptyState?.classList.add('flex');
                return;
            }

            const labels = chartData.map((item) => String(item.year));
            const values = chartData.map((item) => Number(item.gpa));

            // Chart.js manages the scales and point placement; we only provide the data.
            new Chart(canvas, {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Average GPA',
                        data: values,
                        borderColor: '#0f172a',
                        backgroundColor: '#0f172a',
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        tension: 0.35,
                        fill: false,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label(context) {
                                    return ` GPA: ${context.parsed.y.toFixed(2)}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#e2e8f0',
                            },
                            ticks: {
                                color: '#64748b',
                            },
                        },
                        y: {
                            min: 0,
                            max: 4,
                            ticks: {
                                stepSize: 1,
                                color: '#64748b',
                                callback(value) {
                                    return Number(value).toFixed(1);
                                },
                            },
                            grid: {
                                color: '#e2e8f0',
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush
