@extends('layouts.student')

@section('title', 'Department Guide')
@section('page-title', 'Department Guide')

@section('content')
    @if(isset($isFirstYear) && $isFirstYear)
        @foreach($groupedDepartments as $school => $departments)
            <h2 class="text-xl font-bold text-slate-800 mt-8 mb-4">{{ $school }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
                @forelse($departments as $department)
                    <div onclick="openDepartmentModal({{ $department->toJson() }})"
                        class="bg-white border border-slate-200 rounded-md p-6 flex flex-col gap-1 cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300 shadow-sm">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($department->name) }}&background=f1f5f9&color=1f2937&bold=true&size=56"
                                alt="{{ $department->name }}"
                                class="w-14 h-14 rounded-full border-2 border-slate-200 object-cover flex-shrink-0 shadow-sm">
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 leading-tight mb-1">{{ $department->name }}</h2>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-semibold text-blue-700 bg-blue-100 px-2.5 py-0.5 rounded-md border border-blue-200">{{ $department->code }}</span>
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">
                                        Min GPA: <span class="text-slate-900 font-bold">{{ $department->min_gpa }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed mb-1 mt-2">
                            {{ Str::limit($department->description ?? 'No description available for this department.', 120) }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No departments found for this school.</p>
                @endforelse
            </div>
        @endforeach
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($departments as $department)
                <div onclick="openDepartmentModal({{ $department->toJson() }})"
                    class="bg-white border border-slate-200 rounded-md p-6 flex flex-col gap-1 cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300 shadow-sm">
                    <div class="flex items-center gap-3 mb-2">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($department->name) }}&background=f1f5f9&color=1f2937&bold=true&size=56"
                            alt="{{ $department->name }}"
                            class="w-14 h-14 rounded-full border-2 border-slate-200 object-cover flex-shrink-0 shadow-sm">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 leading-tight mb-1">{{ $department->name }}</h2>
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-semibold text-blue-700 bg-blue-100 px-2.5 py-0.5 rounded-md border border-blue-200">{{ $department->code }}</span>
                                <span
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">
                                    Min GPA: <span class="text-slate-900 font-bold">{{ $department->min_gpa }}</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-slate-600 leading-relaxed mb-1 mt-2">
                        {{ Str::limit($department->description ?? 'No description available for this department.', 120) }}</p>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal -->
    <div id="modalOverlay" onclick="closeModalOnOverlay(event)"
        class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] items-center justify-center p-4">

        <div onclick="event.stopPropagation()"
            class="bg-white rounded-md w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col shadow-2xl modal-animate border border-slate-100">

            <!-- Modal Header -->
            <div class="relative p-6 pb-5 flex gap-4 items-start border-b border-slate-100 bg-slate-50/50">
                <button onclick="closeModal()"
                    class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md w-8 h-8 flex items-center justify-center transition focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>

                <img id="modalLogo" src="" alt="Icon"
                    class="w-16 h-16 rounded-full border-2 border-slate-200 object-cover shadow-sm bg-white shrink-0">
                <div class="pt-1">
                    <h2 id="modalTitle" class="text-xl font-bold text-slate-900 leading-tight mb-2">Department</h2>
                    <div class="flex items-center gap-2">
                        <span id="modalBadge"
                            class="inline-block px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-100 text-blue-700 border border-blue-200">CODE</span>
                        <span
                            class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200">
                            Min GPA: <span id="modalGpa" class="text-slate-900 font-bold">0.00</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6 flex flex-col gap-6">
                <!-- Description -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">About the Department</h3>
                    <p id="modalDescription" class="text-sm text-slate-600 leading-relaxed text-justify"></p>
                </div>

                <!-- Prediction Progress Bar -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="text-slate-400">
                                <path d="m12 14 4-4" />
                                <path d="M3.34 19a10 10 0 1 1 17.32 0" />
                            </svg>
                            Admission Chance Score
                        </h3>
                        <span id="modalChanceText" class="text-sm font-bold text-slate-700">0%</span>
                    </div>

                    <div
                        class="h-3 w-full bg-slate-100 rounded-full overflow-hidden border border-slate-200/60 shadow-inner">
                        <div id="modalChanceBar"
                            class="h-full bg-gradient-to-r from-emerald-400 to-emerald-500 rounded-full transition-all duration-1000 ease-out"
                            style="width: 0%;"></div>
                    </div>
                    <p id="modalChanceHint" class="text-xs text-slate-500 mt-2 text-right">Based on your current academic
                        standing</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .modal-animate {
            animation: modalFadeInUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes modalFadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        const overlay = document.getElementById('modalOverlay');

        function openDepartmentModal(data) {
            // Set basic details
            document.getElementById('modalTitle').textContent = data.name;
            document.getElementById('modalBadge').textContent = data.code;
            document.getElementById('modalDescription').textContent = data.description;
            document.getElementById('modalGpa').textContent = data.min_gpa;

            // Set dynamic logo using UI Avatars
            const logoUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name)}&background=f1f5f9&color=1f2937&bold=true&size=56`;
            document.getElementById('modalLogo').src = logoUrl;

            // Reset progress bar width before animating
            const bar = document.getElementById('modalChanceBar');
            bar.style.width = '0%';
            bar.classList.remove('from-emerald-400', 'to-emerald-500', 'from-amber-400', 'to-amber-500', 'from-red-400', 'to-red-500');

            // Render chance text
            document.getElementById('modalChanceText').textContent = data.chance + '%';

            // Set color logic based on chance
            if (data.chance >= 75) {
                bar.classList.add('from-emerald-400', 'to-emerald-500');
                document.getElementById('modalChanceHint').textContent = "Excellent chance based on your current GPA!";
                document.getElementById('modalChanceText').classList.add('text-emerald-600');
                document.getElementById('modalChanceText').classList.remove('text-slate-700', 'text-amber-600', 'text-red-500');
            } else if (data.chance >= 40) {
                bar.classList.add('from-amber-400', 'to-amber-500');
                document.getElementById('modalChanceHint').textContent = "Moderate chance. Pushing your GPA slightly could help.";
                document.getElementById('modalChanceText').classList.add('text-amber-600');
                document.getElementById('modalChanceText').classList.remove('text-slate-700', 'text-emerald-600', 'text-red-500');
            } else {
                bar.classList.add('from-red-400', 'to-red-500');
                document.getElementById('modalChanceHint').textContent = "Low chance. Target a higher GPA to meet the threshold.";
                document.getElementById('modalChanceText').classList.add('text-red-500');
                document.getElementById('modalChanceText').classList.remove('text-slate-700', 'text-emerald-600', 'text-amber-600');
            }

            // Show Modal
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            document.body.style.overflow = 'hidden';

            // Trigger progress bar animation after a short delay for smooth effect
            setTimeout(() => {
                bar.style.width = data.chance + '%';
            }, 50);
        }

        function closeModal() {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function closeModalOnOverlay(e) {
            if (e.target === e.currentTarget) closeModal();
        }

        // Escape key handling to close modal
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !overlay.classList.contains('hidden')) {
                closeModal();
            }
        });
    </script>
@endpush