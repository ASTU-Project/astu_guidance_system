@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Students</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">5,000</p>
                <p class="mt-1 text-sm text-slate-500">Active learners this semester</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Departments</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">17</p>
                <p class="mt-1 text-sm text-slate-500">Academic units available</p>
            </div>
            <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Reserves</p>
                <p class="mt-1 text-2xl font-bold text-slate-950">10</p>
                <p class="mt-1 text-sm text-slate-500">Reserved classrooms & labs</p>
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
                        <svg viewBox="0 0 680 280" class="min-w-[680px] w-full h-auto" role="img" aria-label="Average GPA performance by year line graph">
                            <line x1="60" y1="40" x2="60" y2="220" stroke="#cbd5e1" stroke-width="1" />
                            <line x1="60" y1="220" x2="640" y2="220" stroke="#cbd5e1" stroke-width="1" />

                            <line x1="60" y1="220" x2="640" y2="220" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="60" y1="180" x2="640" y2="180" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="60" y1="140" x2="640" y2="140" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="60" y1="100" x2="640" y2="100" stroke="#e2e8f0" stroke-width="1" />
                            <line x1="60" y1="60" x2="640" y2="60" stroke="#e2e8f0" stroke-width="1" />

                            <text x="36" y="224" font-size="12" fill="#64748b">2.0</text>
                            <text x="36" y="184" font-size="12" fill="#64748b">2.5</text>
                            <text x="36" y="144" font-size="12" fill="#64748b">3.0</text>
                            <text x="36" y="104" font-size="12" fill="#64748b">3.5</text>
                            <text x="36" y="64" font-size="12" fill="#64748b">4.0</text>

                            <polyline fill="none" stroke="#0f172a" stroke-width="3" points="100,188 190,176 280,160 370,148 460,128 550,120" />

                            <circle cx="100" cy="188" r="4" fill="#0f172a" />
                            <circle cx="190" cy="176" r="4" fill="#0f172a" />
                            <circle cx="280" cy="160" r="4" fill="#0f172a" />
                            <circle cx="370" cy="148" r="4" fill="#0f172a" />
                            <circle cx="460" cy="128" r="4" fill="#0f172a" />
                            <circle cx="550" cy="120" r="4" fill="#0f172a" />

                            <text x="88" y="245" font-size="12" fill="#64748b">2019</text>
                            <text x="178" y="245" font-size="12" fill="#64748b">2020</text>
                            <text x="268" y="245" font-size="12" fill="#64748b">2021</text>
                            <text x="358" y="245" font-size="12" fill="#64748b">2022</text>
                            <text x="448" y="245" font-size="12" fill="#64748b">2023</text>
                            <text x="538" y="245" font-size="12" fill="#64748b">2024</text>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Recent messages</h3>
                        </div>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div class="rounded-md bg-slate-50 p-4 border border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">Samuel Muluken</p>
                            <p class="mt-1 text-sm text-slate-500">Lorem, ipsum dolor sit amet consectetur adipisi...</p>
                            <p class="mt-1 text-xs text-slate-400">10 minutes ago</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4 border border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">Samuel Muluken</p>
                            <p class="mt-1 text-sm text-slate-500">Lorem, ipsum dolor sit amet consectetur adipisi...</p>
                            <p class="mt-1 text-xs text-slate-400">10 minutes ago</p>
                        </div>
                        <div class="rounded-md bg-slate-50 p-4 border border-slate-200">
                            <p class="text-sm font-semibold text-slate-900">Samuel Muluken</p>
                            <p class="mt-1 text-sm text-slate-500">Lorem, ipsum dolor sit amet consectetur adipisi...</p>
                            <p class="mt-1 text-xs text-slate-400">10 minutes ago</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-md bg-white border border-slate-200 p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-950">Top departments</h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-3 rounded-md bg-slate-50 p-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Engineering</p>
                                <p class="text-xs text-slate-500">1,420 students</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">28%</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-md bg-slate-50 p-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Business</p>
                                <p class="text-xs text-slate-500">980 students</p>
                            </div>
                            <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">19%</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-md bg-slate-50 p-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">Health sciences</p>
                                <p class="text-xs text-slate-500">760 students</p>
                            </div>
                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">15%</span>
                        </div>
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
