@extends('layouts.admin')

@section('title', 'Field Managment')
@section('page-title', 'Field')

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
                    <h3 class="text-lg font-semibold text-slate-950">Field ({{ number_format($departments->count()) }})</h3>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Code</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Spot Limit</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Min GPA</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Description</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Students</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Load</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($departments as $department)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ $department->name }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $department->code }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ number_format($department->spot_limit) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ number_format((float) $department->min_gpa, 2) }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <span>{{ Str::limit($department->description, 60) }}</span>
                                        <button type="button" class="text-blue-600 hover:underline text-xs font-semibold" onclick="openDescriptionModal({{ $department->id }}, '{{ addslashes($department->name) }}', '{{ addslashes(Str::replace("'", "&#39;", $department->description ?? '')) }}')">Edit</button>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ number_format($department->student_count) }}</td>
                                <td class="px-5 py-4">
                                        <!-- Description Edit Modal -->
                                        <div id="description-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
                                            <div class="absolute inset-0 bg-slate-950/50" onclick="closeDescriptionModal()"></div>
                                            <div class="relative w-full max-w-lg rounded-md bg-white p-6 shadow-2xl">
                                                <div class="flex items-start justify-between gap-4 mb-2">
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-slate-950">Edit Department Description</h3>
                                                        <div class="text-xs text-slate-500 mt-1" id="modal-department-name"></div>
                                                    </div>
                                                </div>
                                                <form id="description-form" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-4">
                                                        <label for="modal-description" class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                                                        <textarea id="modal-description" name="description" rows="4" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"></textarea>
                                                    </div>
                                                    <div class="flex items-center justify-end gap-2 pt-2">
                                                        <button type="button" onclick="closeDescriptionModal()" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                                                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @php
                                        $occupancy = $department->spot_limit > 0
                                            ? min(100, round(($department->student_count / $department->spot_limit) * 100))
                                            : 0;
                                    @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full bg-slate-900" style="width: {{ $occupancy }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-slate-500">{{ $occupancy }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                                    No departments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Page specific styles --}}
@endpush

@push('scripts')
    <script>
        function openDescriptionModal(id, name, description) {
            document.getElementById('description-modal').classList.remove('hidden');
            document.getElementById('modal-department-name').textContent = name;
            document.getElementById('modal-description').value = description;
            document.getElementById('description-form').action = '/admin/departments/' + id;
        }
        function closeDescriptionModal() {
            document.getElementById('description-modal').classList.add('hidden');
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeDescriptionModal();
            }
        });
    </script>
@endpush