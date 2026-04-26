@extends('layouts.student')

@section('title', 'Academic Status')
@section('page-title', 'Academic Status')

@section('content')
    <div class="space-y-5">

        <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('student.status') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="status-year" class="mb-1 block text-xs font-medium text-slate-600">Year</label>
                    <select id="status-year" name="year" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-slate-400 focus:outline-none">
                        @foreach($yearOptions as $year)
                            <option value="{{ $year }}" @selected($selectedYear === (int) $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status-semester" class="mb-1 block text-xs font-medium text-slate-600">Semester</label>
                    <select id="status-semester" name="semester" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-slate-400 focus:outline-none">
                        @foreach($semesterOptions as $semester)
                            <option value="{{ $semester['value'] }}" @selected($selectedSemester === (string) $semester['value'])>{{ $semester['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status-view" class="mb-1 block text-xs font-medium text-slate-600">View</label>
                    <select id="status-view" name="view" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-slate-400 focus:outline-none">
                        @foreach($viewModes as $viewMode)
                            <option value="{{ $viewMode['value'] }}" @selected($selectedView === $viewMode['value'])>{{ $viewMode['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
                    <i class="fa fa-filter text-[11px]"></i>
                    Apply Filter
                </button>
            </form>
        </section>

        <section id="status-summary" class="grid grid-cols-1 gap-2 sm:grid-cols-3 xl:grid-cols-4">
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">CGPA</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ number_format((float) ($summary['cgpa'] ?? 0), 2) }}</p>
                <p class="mt-1 text-sm text-slate-500">Cumulative performance</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Semester GPA</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">{{ number_format((float) ($summary['semester_gpa'] ?? 0), 2) }}</p>
                <p class="mt-1 text-sm text-slate-500">Selected semester</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Rank</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">Top {{ number_format((float) ($percentile ?? 100), 0) }}%</p>
                <p class="mt-1 text-sm text-slate-500">Class percentile</p>
            </div>
            <div class="rounded-md border border-slate-200 p-6 shadow-sm
            @if(($statusLabel ?? '') === 'Excellent Standing')
                bg-emerald-50 border-emerald-200
            @elseif(($statusLabel ?? '') === 'Good Standing')
                bg-amber-50 border-amber-200
            @elseif(($statusLabel ?? '') === 'At Risk')
                bg-rose-50 border-rose-200
            @else
                bg-white
            @endif
        ">
            <p class="text-xs font-semibold uppercase tracking-[0.3em]
                @if(($statusLabel ?? '') === 'Excellent Standing')
                    text-emerald-600
                @elseif(($statusLabel ?? '') === 'Good Standing')
                    text-amber-600
                @elseif(($statusLabel ?? '') === 'At Risk')
                    text-rose-600
                @else
                    text-slate-500
                @endif
            ">Academic standing</p>
            <p class="mt-1 text-2xl font-bold
                @if(($statusLabel ?? '') === 'Excellent Standing')
                    text-emerald-700
                @elseif(($statusLabel ?? '') === 'Good Standing')
                    text-amber-700
                @elseif(($statusLabel ?? '') === 'At Risk')
                    text-rose-700
                @else
                    text-slate-950
                @endif
            ">{{ $statusLabel ?? 'No Standing Data' }}</p>
            <p class="mt-1 text-sm
                @if(($statusLabel ?? '') === 'Excellent Standing')
                    text-emerald-600
                @elseif(($statusLabel ?? '') === 'Good Standing')
                    text-amber-600
                @elseif(($statusLabel ?? '') === 'At Risk')
                    text-rose-600
                @else
                    text-slate-500
                @endif
            ">Current status</p>
        </div>
        </section>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[1.4fr_0.6fr]" id="status-trend">
            <section class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-cyan-700">GPA Trend</h3>
                        <p class="text-sm text-slate-500">Year-by-year GPA performance trend</p>
                    </div>
                </div>

                <div class="mt-4 rounded border border-slate-200 bg-slate-50 p-2">
                    <div class="relative h-[280px] w-full min-w-[680px] overflow-x-auto" style="min-width: 0;">
                        <canvas id="gpaTrendChart" class="h-full w-full" aria-label="Yearly GPA trend line chart" role="img"></canvas>
                        <div id="gpaTrendChartEmpty" class="absolute inset-0 hidden items-center justify-center text-sm text-slate-500">
                            No yearly GPA trend data available yet
                        </div>
                    </div>
                </div>
            </section>

            <div class="space-y-4">
                <section id="status-insights" class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-cyan-700">Insights</h3>
                    <ul class="mt-4 space-y-3 text-sm text-slate-600">
                        @foreach(($insights ?? []) as $insight)
                            <li class="rounded-md bg-cyan-50 p-3 border border-cyan-200">{!! $insight !!}</li>
                        @endforeach
                    </ul>
                </section>

                <section id="status-ranking" class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-cyan-700">Ranking Display</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Rank:</span> {{ $rankPosition ?? 1 }} / {{ $rankTotal ?? 1 }}</p>
                        <p><span class="font-semibold text-slate-800">Percentile:</span> Top {{ number_format((float) ($percentile ?? 100), 0) }}%</p>
                        <p><span class="font-semibold text-slate-800">Category:</span> {{ $categoryLabel ?? 'N/A' }}</p>
                    </div>
                </section>
            </div>
        </div>

        <section id="status-semester-splits" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                @forelse($semesterPanels as $semData)
                    <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-4 py-3 sm:px-5 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $semData['title'] ?? (($semData['semester'] ?? 'Semester').' ('.($semData['year'] ?? '').')') }}</h3>
                            <span class="text-xs font-semibold text-cyan-700">GPA {{ number_format((float) ($semData['gpa'] ?? 0), 2) }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[660px]">
                                <thead class="bg-cyan-50 text-left">
                                    <tr>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Subject</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Code</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Mark</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Credit</th>
                                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse(($semData['subjects'] ?? []) as $subject)
                                        <tr class="hover:bg-slate-50/70">
                                            <td class="px-4 py-3 text-sm text-slate-800">{{ $subject['subject'] }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $subject['code'] }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $subject['score'] }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $subject['credit'] }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex rounded-full bg-cyan-100 px-2.5 py-1 text-xs font-semibold text-cyan-700">
                                                    {{ $subject['grade'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">No semester subject marks found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="bg-slate-50/70 border-t border-slate-100">
                                        <td colspan="3" class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">{{ $semData['semester'] ?? 'Semester' }} GPA</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ $semData['credits'] ?? 0 }} Cr</td>
                                        <td class="px-4 py-3 text-sm font-semibold text-slate-800">{{ number_format((float) ($semData['gpa'] ?? 0), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="rounded-md border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm lg:col-span-2">
                        No semester subject marks found for the selected filters.
                    </div>
                @endforelse
            </div>

        </section>

        <section id="status-yearly-overview" class="rounded-md border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <h3 class="text-sm font-semibold text-cyan-700">Yearly Overview GPA Performance</h3>
                    <p class="text-xs text-slate-500">
                        {{ $selectedYearLabel ?? $selectedYear }} vs {{ $previousYearLabel ?? 'previous year' }}:
                        <span class="font-semibold {{ (float) ($yearlyChange ?? 0) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ (float) ($yearlyChange ?? 0) >= 0 ? '+' : '' }}{{ number_format((float) ($yearlyChange ?? 0), 2) }} GPA
                        </span>
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead class="bg-cyan-50 text-left">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Year</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Sem 1 GPA</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Sem 2 GPA</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Year GPA</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">CGPA</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Change vs Prev</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($yearlyOverviewRows as $row)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 text-sm font-semibold text-cyan-700">{{ $row['year'] }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ number_format($row['sem1_gpa'], 2) }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ number_format($row['sem2_gpa'], 2) }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ number_format($row['year_gpa'], 2) }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ number_format($row['cgpa'], 2) }}</td>
                                <td class="px-4 py-3 text-sm {{ $row['delta_class'] }}">
                                    {{ $row['delta_label'] }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">No yearly GPA overview data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section id="status-performance" class="rounded-md border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-lg font-semibold text-cyan-700">Subject Performance ({{ $performanceScopeLabel ?? 'Selected Semester' }})</h3>
                
            </div>

            <div class="mt-4 rounded border border-slate-200 bg-slate-50 p-2 overflow-x-auto">
                <div class="relative" style="height: 260px; min-width: 100%;">
                    <canvas id="subjectPerformanceChart" style="height: 260px;" aria-label="Subject performance bar chart" role="img"></canvas>
                    <div id="subjectPerformanceChartEmpty" class="absolute inset-0 hidden items-center justify-center text-sm text-slate-500">
                        No subject performance data available yet
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const trendLabels = @json($trendLabels);
            const trendValues = @json($trendValues);
            const performanceLabels = @json($performanceLabels);
            const performanceValues = @json($performanceValues);
            const performanceDetails = @json($performanceDetails ?? []);

            const trendCanvas = document.getElementById('gpaTrendChart');
            const trendEmpty = document.getElementById('gpaTrendChartEmpty');

            if (trendCanvas) {
                if (!trendLabels.length || !trendValues.length) {
                    trendCanvas.classList.add('hidden');
                    trendEmpty?.classList.remove('hidden');
                    trendEmpty?.classList.add('flex');
                } else {
                    new Chart(trendCanvas, {
                        type: 'line',
                        data: {
                            labels: trendLabels,
                            datasets: [{
                                label: 'GPA',
                                data: trendValues,
                                borderColor: '#0891b2',
                                backgroundColor: '#0891b2',
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
                                            return ` GPA: ${Number(context.parsed.y).toFixed(2)}`;
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
                }
            }

            const subjectCanvas = document.getElementById('subjectPerformanceChart');
            const subjectEmpty = document.getElementById('subjectPerformanceChartEmpty');

            function scoreToColor(score) {
                if (score >= 90) return '#10b981';
                if (score >= 80) return '#06b6d4';
                if (score >= 70) return '#3b82f6';
                if (score >= 60) return '#f59e0b';
                if (score >= 50) return '#f97316';
                return '#f43f5e';
            }

            if (subjectCanvas) {
                if (!performanceLabels.length || !performanceValues.length) {
                    subjectCanvas.classList.add('hidden');
                    subjectEmpty?.classList.remove('hidden');
                    subjectEmpty?.classList.add('flex');
                } else {
                    const minChartWidth = Math.max(performanceLabels.length * 55, subjectCanvas.parentElement?.clientWidth || 0);
                    subjectCanvas.style.width = minChartWidth + 'px';

                    new Chart(subjectCanvas, {
                        type: 'bar',
                        data: {
                            labels: performanceLabels,
                            datasets: [{
                                label: 'Score',
                                data: performanceValues,
                                backgroundColor: performanceValues.map(v => scoreToColor(v)),
                                borderRadius: 6,
                                maxBarThickness: 40,
                            }],
                        },
                        options: {
                            responsive: false,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        title(items) {
                                            const item = items?.[0];
                                            if (!item) {
                                                return '';
                                            }

                                            const index = Number(item.dataIndex);
                                            const detail = performanceDetails[index] ?? null;

                                            return String(detail?.name ?? item.label ?? 'Subject');
                                        },
                                        label(context) {
                                            const index = Number(context.dataIndex);
                                            const detail = performanceDetails[index] ?? null;

                                            if (!detail) {
                                                return `Score: ${Number(context.parsed.y)}`;
                                            }

                                            return [
                                                `Score: ${detail.score}`,
                                                `Semester: ${detail.semester}`,
                                                `Year: ${detail.year}`,
                                                `Credit Hour: ${detail.credit}`,
                                            ];
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                    },
                                    ticks: {
                                        color: '#64748b',
                                        autoSkip: false,
                                        font: {
                                            size: 10,
                                        },
                                        maxRotation: performanceLabels.length > 8 ? 45 : 0,
                                        minRotation: 0,
                                    },
                                },
                                y: {
                                    min: 0,
                                    max: 100,
                                    ticks: {
                                        stepSize: 20,
                                        color: '#64748b',
                                    },
                                    grid: {
                                        color: '#e2e8f0',
                                    },
                                },
                            },
                        },
                    });
                }
            }
        });
    </script>
@endpush
