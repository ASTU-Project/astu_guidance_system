@extends('layouts.admin')

@section('title', 'Community')
@section('page-title', 'Community')

@section('content')
<div class="space-y-4">

    @if(session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">

        {{-- Header + filters --}}
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <h3 class="text-l font-semibold text-slate-800">Community ({{ number_format($links->total()) }})</h3>
            <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto">
                <form action="{{ route('admin.community.index') }}" method="GET" class="flex gap-2 flex-wrap">
                    <div class="relative w-full sm:w-56">
                        <i class="fa fa-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, leader…"
                            class="h-9 w-full rounded-md border border-slate-200 bg-white pl-9 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-slate-400 focus:outline-none">
                    </div>
                    <select name="type" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="">All Types</option>
                        <option value="club" @selected(request('type') === 'club')>Club</option>
                        <option value="telegram" @selected(request('type') === 'telegram')>Telegram</option>
                    </select>
                    <select name="status" class="h-9 rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-slate-400 focus:outline-none">
                        <option value="">All Status</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
                        <i class="fa fa-filter text-[11px]"></i> Filter
                    </button>
                    @if(request()->hasAny(['q','type','status']))
                        <a href="{{ route('admin.community.index') }}" class="inline-flex items-center rounded-md border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50">Clear</a>
                    @endif
                </form>
                <button type="button" onclick="openCreateModal()"
                    class="inline-flex items-center gap-1.5 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 whitespace-nowrap">
                    <i class="fa fa-plus text-[11px]"></i> Add New
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-left">
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Image</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Name</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Type</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Leader / Admin</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Category</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Status</th>
                        <th class="px-4 sm:px-5 py-3 text-[11px] font-semibold uppercase tracking-[0.08em] text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($links as $link)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 sm:px-5 py-3">
                            @if($link->image_src)
                                <img src="{{ $link->image_src }}" alt="{{ $link->name }}" class="h-10 w-16 rounded-md object-cover border border-slate-200">
                            @else
                                <div class="h-10 w-16 rounded-md bg-slate-100 flex items-center justify-center">
                                    <i class="fa {{ $link->type === 'telegram' ? 'fa-paper-plane' : 'fa-users' }} text-slate-400 text-xs"></i>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 sm:px-5 py-3">
                            <p class="text-sm font-medium text-slate-800">{{ $link->name }}</p>
                            @if($link->description)
                                <p class="text-xs text-slate-400 mt-0.5 max-w-[200px] truncate">{{ $link->description }}</p>
                            @endif
                        </td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">
                            <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-semibold
                                {{ $link->type === 'telegram' ? 'bg-sky-50 text-sky-700' : 'bg-violet-50 text-violet-700' }}">
                                {{ ucfirst($link->type) }}
                            </span>
                        </td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">{{ $link->leader ?? '—' }}</td>
                        <td class="px-4 sm:px-5 py-3 text-sm text-slate-600">{{ $link->category ? ucfirst($link->category) : '—' }}</td>
                        <td class="px-4 sm:px-5 py-3">
                            @if($link->is_active)
                                <span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>
                            @else
                                <span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-5 py-3 flex items-center gap-3">
                            <button type="button" class="text-slate-400 hover:text-slate-700"
                                onclick="openViewModal({{ json_encode([
                                    'name'        => $link->name,
                                    'type'        => $link->type,
                                    'url'         => $link->url,
                                    'leader'      => $link->leader,
                                    'description' => $link->description,
                                    'category'    => $link->category,
                                    'is_active'   => $link->is_active,
                                    'image_url'   => $link->image_src,
                                ]) }})">
                                <i class="fa fa-eye"></i>
                            </button>
                            <button type="button" class="text-slate-500 hover:text-slate-800"
                                onclick="openEditModal({{ json_encode([
                                    'id'          => $link->id,
                                    'name'        => $link->name,
                                    'type'        => $link->type,
                                    'url'         => $link->url,
                                    'leader'      => $link->leader,
                                    'description' => $link->description,
                                    'category'    => $link->category,
                                    'is_active'   => $link->is_active,
                                    'image_url'   => $link->image_src,
                                ]) }})">
                                <i class="fa fa-edit"></i>
                            </button>
                            <form action="{{ route('admin.community.destroy', $link->id) }}" method="POST" onsubmit="return confirm('Delete this entry?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 sm:px-5 py-8 text-center text-slate-400 text-sm">No entries found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3 sm:px-5 py-4 border-t border-slate-100 text-xs text-slate-500">
            {{ $links->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    </div>
</div>

{{-- Create Modal --}}
<div id="create-modal" class="{{ $errors->any() && !old('_edit_id') ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-slate-950/50" onclick="closeCreateModal()"></div>
    <div class="relative w-full max-w-lg rounded-md bg-white p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-semibold text-slate-900">Add Community Entry</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-times"></i></button>
        </div>
        <form action="{{ route('admin.community.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @include('admin.community-form', ['data' => null])
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeCreateModal()" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div id="edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-slate-950/50" onclick="closeEditModal()"></div>
    <div class="relative w-full max-w-lg rounded-md bg-white p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-semibold text-slate-900">Edit Community Entry</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-times"></i></button>
        </div>
        <form id="edit-form" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="_edit_id" value="1">
            @include('admin.community-form', ['data' => 'edit'])
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModal()" class="rounded-md border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Update</button>
            </div>
        </form>
    </div>
</div>
{{-- View Modal --}}
<div id="view-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-slate-950/60" onclick="closeViewModal()"></div>
    <div class="relative w-full max-w-sm rounded-md bg-white shadow-2xl overflow-hidden">

        {{-- Image header with bottom gradient + name --}}
        <div class="relative h-48 bg-slate-200">
            <img id="view-image" src="" alt="" class="absolute inset-0 w-full h-full object-cover">
            {{-- fallback icon when no image --}}
            <div id="view-image-fallback" class="absolute inset-0 flex items-center justify-center">
                <i id="view-fallback-icon" class="fa fa-users text-4xl text-slate-400"></i>
            </div>
            {{-- gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
            {{-- name on gradient --}}
            <div class="absolute bottom-0 left-0 right-0 px-4 pb-3">
                <p id="view-name" class="text-white font-bold text-lg leading-tight"></p>
                <span id="view-type-badge" class="inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold mt-1"></span>
            </div>
            <button type="button" onclick="closeViewModal()" class="absolute top-3 right-3 h-7 w-7 rounded-md bg-black/40 flex items-center justify-center text-white hover:bg-black/60">
                <i class="fa fa-times text-xs"></i>
            </button>
        </div>

        {{-- Details --}}
        <div class="p-4 space-y-3">
            <div class="divide-y divide-slate-100">
                <div id="view-description-row" class="py-2">
                    <span class="text-xs font-medium text-slate-400">Description</span>
                    <p id="view-description-wrap" class="text-sm text-slate-600 leading-relaxed mt-1"></p>
                </div>
                <div id="view-leader-row" class="flex items-center justify-between py-2">
                    <span id="view-leader-label" class="text-xs font-medium text-slate-400">President</span>
                    <span id="view-leader" class="text-sm text-slate-700 font-medium"></span>
                </div>
                <div id="view-category-row" class="flex items-center justify-between py-2">
                    <span class="text-xs font-medium text-slate-400">Category</span>
                    <span id="view-category" class="text-sm text-slate-700"></span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-xs font-medium text-slate-400">Status</span>
                    <span id="view-status"></span>
                </div>
            </div>

            <a id="view-link" href="#" target="_blank"
                class="flex items-center justify-center gap-2 w-full rounded-md bg-slate-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                <i class="fa fa-arrow-up-right-from-square text-xs"></i>
                <span id="view-link-label">Open Link</span>
            </a>
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

@push('scripts')
<script>
    const editActionTemplate = '{{ route('admin.community.update', ['community' => '__ID__']) }}';

    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }
    function openEditModal(data) {
        const form = document.getElementById('edit-form');
        form.action = editActionTemplate.replace('__ID__', data.id);

        form.querySelector('[name="name"]').value         = data.name ?? '';
        form.querySelector('[name="type"]').value         = data.type ?? 'club';
        form.querySelector('[name="url"]').value          = data.url ?? '';
        form.querySelector('[name="leader"]').value       = data.leader ?? '';
        form.querySelector('[name="description"]').value  = data.description ?? '';
        form.querySelector('[name="category"]').value     = data.category ?? '';
        form.querySelector('[name="is_active"]').checked  = data.is_active;

        const preview = document.getElementById('edit-image-preview');
        if (data.image_url) {
            preview.src = data.image_url;
            preview.classList.remove('hidden');
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }

        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeCreateModal(); closeEditModal(); closeViewModal(); }
    });

    function openViewModal(data) {
        const modal = document.getElementById('view-modal');
        const img   = document.getElementById('view-image');
        const fallback = document.getElementById('view-image-fallback');
        const fallbackIcon = document.getElementById('view-fallback-icon');

        document.getElementById('view-name').textContent = data.name ?? '';

        // image
        if (data.image_url) {
            img.src = data.image_url;
            img.classList.remove('hidden');
            fallback.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            fallback.classList.remove('hidden');
            fallbackIcon.className = data.type === 'telegram'
                ? 'fa fa-paper-plane text-4xl text-slate-400'
                : 'fa fa-users text-4xl text-slate-400';
        }

        // type badge
        const badge = document.getElementById('view-type-badge');
        badge.textContent = data.type === 'telegram' ? 'Telegram' : 'Club';
        badge.className = data.type === 'telegram'
            ? 'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold bg-sky-500/80 text-white mt-1'
            : 'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold bg-violet-500/80 text-white mt-1';

        // description
        const descWrap = document.getElementById('view-description-wrap');
        const descRow  = document.getElementById('view-description-row');
        descWrap.textContent = data.description ?? '';
        descRow.classList.toggle('hidden', !data.description);

        // leader
        const leaderRow = document.getElementById('view-leader-row');
        document.getElementById('view-leader-label').textContent = data.type === 'telegram' ? 'Admin' : 'President';
        document.getElementById('view-leader').textContent = data.leader ?? '—';
        leaderRow.classList.toggle('hidden', !data.leader);

        // category
        const catRow = document.getElementById('view-category-row');
        document.getElementById('view-category').textContent = data.category ? data.category.charAt(0).toUpperCase() + data.category.slice(1) : '—';
        catRow.classList.toggle('hidden', !data.category);

        // status
        const statusEl = document.getElementById('view-status');
        statusEl.innerHTML = data.is_active
            ? '<span class="inline-flex rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Active</span>'
            : '<span class="inline-flex rounded-md bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">Inactive</span>';

        // link
        document.getElementById('view-link').href = data.url ?? '#';
        document.getElementById('view-link-label').textContent = data.type === 'telegram' ? 'Open Telegram' : 'Visit Link';

        modal.classList.remove('hidden');
    }
    function closeViewModal() {
        document.getElementById('view-modal').classList.add('hidden');
    }

    // image preview for both modals
    ['create-image', 'edit-image'].forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('change', function () {
            const previewId = id === 'create-image' ? 'create-image-preview' : 'edit-image-preview';
            const preview = document.getElementById(previewId);
            if (this.files[0]) {
                preview.src = URL.createObjectURL(this.files[0]);
                preview.classList.remove('hidden');
            }
        });
    });
</script>
@endpush
