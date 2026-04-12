@extends('layouts.admin')

@section('title', 'Policy Management')
@section('page-title', 'Policy')

@section('content')
    <div class="space-y-5">
        <div class="rounded-md border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Policies ({{ number_format($policies->count()) }})</h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        onclick="document.getElementById('policy-modal').classList.remove('hidden')"
                        class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        <i class="fa fa-plus text-[11px]"></i>
                        Add Policy
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Title</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Category</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Summary</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Updated</th>
                            <th class="px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($policies as $policy)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">{{ $policy->title }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 uppercase">
                                        {{ $policy->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600 max-w-md">
                                    <p class="line-clamp-2">{{ \Illuminate\Support\Str::limit($policy->content, 120) }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    @if($policy->is_active)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ optional($policy->created_at)->format('M d, Y') }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600 gap-3 flex">
                                    <button
                                        type="button"
                                        class="js-policy-edit"
                                        data-id="{{ $policy->id }}"
                                        data-update-url="{{ route('admin.policy.update', $policy->id) }}"
                                        data-title="{{ $policy->title }}"
                                        data-category="{{ $policy->category }}"
                                        data-content="{{ $policy->content }}"
                                        data-status="{{ $policy->is_active ? '1' : '0' }}"
                                    >
                                        <i class="fa fa-edit"></i>    
                                    </button>
                                     <button
                                        type="button"
                                        class="js-policy-view"
                                        data-title="{{ $policy->title }}"
                                        data-category="{{ $policy->category }}"
                                        data-summary="{{ $policy->content }}"
                                        data-status="{{ $policy->is_active ? 'Active' : 'Inactive' }}"
                                        data-updated="{{ optional($policy->created_at)->format('M d, Y') }}"
                                    >
                                        <i class="fa fa-eye"></i>    
                                    </button>
                                    <form action="{{ route('admin.policy.destroy', $policy->id) }}" method="POST" onsubmit="return confirm('Delete this Policy or Rule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td> 
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-400">
                                    No policies found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="policy-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-950/50" onclick="document.getElementById('policy-modal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-2xl rounded-md bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Add Policy</h3>
                </div>
                <button type="button" onclick="document.getElementById('policy-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <form action="{{ route('admin.policy.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="policy-title" class="mb-1 block text-sm font-medium text-slate-700">Policy Title</label>
                    <input id="policy-title" name="title" type="text" value="{{ old('title') }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="e.g. Academic Leave of Absence Policy">
                </div>

                <div>
                    <label for="policy-category" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                    <select id="policy-category" name="category" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="academic">Academic</option>
                        <option value="attendance">Attendance</option>
                        <option value="financial">Financial</option>
                        <option value="conduct">Conduct</option>
                    </select>
                </div>

                <div>
                    <label for="policy-content" class="mb-1 block text-sm font-medium text-slate-700">Content</label>
                    <textarea id="policy-content" name="content" rows="6" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" placeholder="Write the full policy content here...">{{ old('content') }}</textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input id="policy-is-active" name="is_active" value="1" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Active policy
                </label>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('policy-modal').classList.add('hidden')" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Save Policy
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="policy-view-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-950/50" onclick="document.getElementById('policy-view-modal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-2xl rounded-md bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">View Policy</h3>
                </div>
                <button type="button" onclick="document.getElementById('policy-view-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <div class="mt-5 space-y-4 text-sm">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Title</p>
                    <p id="policy-view-title" class="mt-1 text-slate-800"></p>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Category</p>
                        <p id="policy-view-category" class="mt-1 text-slate-800 uppercase"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Status</p>
                        <p id="policy-view-status" class="mt-1 text-slate-800"></p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Updated</p>
                        <p id="policy-view-updated" class="mt-1 text-slate-800"></p>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Summary</p>
                    <p id="policy-view-summary" class="mt-1"></p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('policy-view-modal').classList.add('hidden')" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="policy-edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-slate-950/50" onclick="document.getElementById('policy-edit-modal').classList.add('hidden')"></div>
        <div class="relative w-full max-w-2xl rounded-md bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950">Edit Policy</h3>
                </div>
                <button type="button" onclick="document.getElementById('policy-edit-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-700">
                    <i class="fa fa-times"></i>
                </button>
            </div>

            <form id="policy-edit-form" action="#" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="policy-edit-title" class="mb-1 block text-sm font-medium text-slate-700">Policy Title</label>
                    <input id="policy-edit-title" name="title" type="text" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                </div>

                <div>
                    <label for="policy-edit-category" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
                    <select id="policy-edit-category" name="category" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="academic">Academic</option>
                        <option value="attendance">Attendance</option>
                        <option value="financial">Financial</option>
                        <option value="conduct">Conduct</option>
                    </select>
                </div>

                <div>
                    <label for="policy-edit-content" class="mb-1 block text-sm font-medium text-slate-700">Content</label>
                    <textarea id="policy-edit-content" name="content" rows="5" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"></textarea>
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input id="policy-edit-status" name="is_active" value="1" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-400">
                    Active policy
                </label>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="document.getElementById('policy-edit-modal').classList.add('hidden')" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Update Policy
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
        const closePolicyModal = (id) => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
            }
        };

        const openPolicyModal = (id) => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
            }
        };

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closePolicyModal('policy-modal');
                closePolicyModal('policy-view-modal');
                closePolicyModal('policy-edit-modal');
            }
        });

        document.querySelectorAll('.js-policy-view').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('policy-view-title').textContent = this.dataset.title || '-';
                document.getElementById('policy-view-category').textContent = this.dataset.category || '-';
                document.getElementById('policy-view-summary').textContent = this.dataset.summary || '-';
                document.getElementById('policy-view-status').textContent = this.dataset.status || '-';
                document.getElementById('policy-view-updated').textContent = this.dataset.updated || '-';
                openPolicyModal('policy-view-modal');
            });
        });

        document.querySelectorAll('.js-policy-edit').forEach(function (button) {
            button.addEventListener('click', function () {
                document.getElementById('policy-edit-form').action = this.dataset.updateUrl || '#';
                document.getElementById('policy-edit-title').value = this.dataset.title || '';
                document.getElementById('policy-edit-category').value = this.dataset.category || 'academic';
                document.getElementById('policy-edit-content').value = this.dataset.content || '';
                document.getElementById('policy-edit-status').checked = this.dataset.status === '1';
                openPolicyModal('policy-edit-modal');
            });
        });
    </script>
@endpush
