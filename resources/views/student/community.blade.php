@extends('layouts.student')

@section('title', 'Community')
@section('page-title', 'Community')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="mb-1 text-left">
        <h1 class="text-xl font-semibold text-slate-800">Channels</h1>
        <p class="text-sm text-slate-500 mt-1">Connect with your peers through our community channels</p>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach(App\Models\CommunityLink::where('is_active', true)->orderBy('name')->get() as $community)
        <div onclick="openCommunityModal({{ $community->toJson() }})"
            class="bg-white border border-slate-200 rounded-md p-6 flex flex-col gap-4 cursor-pointer transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-slate-300 shadow-sm">
            <div class="flex flex-col items-start gap-2">
                <img src="{{ $community->logo_src ?? 'https://ui-avatars.com/api/?name=' . urlencode($community->name) . '&background=f1f5f9&color=1f2937&bold=true&size=56' }}"
                     alt="{{ $community->name }}"
                     class="w-14 h-14 rounded-full border-2 border-gray-800 object-cover flex-shrink-0 shadow-sm mb-1">
                <h2 class="text-lg font-bold text-gray-900">{{ $community->name }}</h2>
            </div>
            <p class="text-sm text-gray-600 leading-relaxed">{{ \Illuminate\Support\Str::limit($community->description, 120) }}</p>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal -->
<div id="modalOverlay"
     onclick="closeModalOnOverlay(event)"
     class="hidden fixed inset-0 bg-black/50 z-[9999] items-center justify-center p-5 backdrop-blur-sm">
    <div onclick="event.stopPropagation()"
                   class="bg-white rounded-md w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col shadow-2xl modal-animate">

        <!-- Modal Header -->
        <div id="modalHeader" class="relative h-44 flex-shrink-0 bg-cover bg-center" style="background: #f1f5f9;">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/80"></div>
            <button onclick="closeModal()"
                    class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center text-white text-xl transition z-10">
                &times;
            </button>
            <div class="absolute left-6 bottom-6 flex flex-col items-start">
                <img id="modalLogo" src="" alt="Channel Logo" class="w-16 h-16 rounded-full border-2 border-white shadow-lg mb-2 object-cover bg-white" style="display:none;">
                <h2 id="modalTitle" class="text-2xl font-bold text-white [text-shadow:0_2px_4px_rgba(0,0,0,0.2)] mb-1">Channel Name</h2>
                <span id="modalBadge" class="inline-block bg-violet-600 text-white text-xs font-semibold px-3 py-1 rounded-full mt-1">Type</span>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-content p-6 overflow-y-auto flex-1">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Description</p>
            <p id="modalDescription" class="text-sm text-gray-700 leading-relaxed mb-6"></p>
            <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                <span class="info-label text-sm text-gray-500">Admin</span>
                <span class="info-value text-sm font-semibold text-gray-900">—</span>
            </div>
            <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                <span class="info-label text-sm text-gray-500">Category</span>
                <span id="modalCategory" class="info-value text-sm font-semibold text-gray-900">General</span>
            </div>
            <div class="info-row flex justify-between items-center py-3 border-b border-slate-200">
                <span class="info-label text-sm text-gray-500">Status</span>
                <span class="info-value text-sm font-semibold text-emerald-600">Active</span>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex-shrink-0">
            <button onclick="visitLink()"
                    class="w-full flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold py-3 rounded-lg transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                Visit Channel
            </button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .modal-animate { animation: modalIn 0.25s ease-out; }
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
    const overlay = document.getElementById('modalOverlay');


    let currentCommunityUrl = '';

    function openCommunityModal(data) {
        document.getElementById('modalTitle').textContent = data.name;
        document.getElementById('modalBadge').textContent = data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : '';
        document.getElementById('modalCategory').textContent = data.category || '';
        document.getElementById('modalDescription').textContent = data.description || '';
        // Set header background image (use cover image if available, else fallback)
        const header = document.getElementById('modalHeader');
        if (data.image_src) {
            header.style.backgroundImage = `url('${data.image_src}')`;
        } else {
            header.style.backgroundImage = 'none';
        }
        // Set logo image
        const logo = document.getElementById('modalLogo');
        if (data.logo_src) {
            logo.src = data.logo_src;
            logo.style.display = '';
        } else {
            logo.src = '';
            logo.style.display = 'none';
        }
        // Set admin/leader
        document.querySelector('.modal-content .info-row .info-value').textContent = data.leader || '—';
        // Set status
        document.querySelectorAll('.modal-content .info-row .info-value')[2].textContent = data.is_active ? 'Active' : 'Inactive';
        // Store URL for visitLink
        currentCommunityUrl = data.url || '#';
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function closeModalOnOverlay(e) {
        if (e.target === e.currentTarget) closeModal();
    }

    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    function visitLink() {
        if (currentCommunityUrl && currentCommunityUrl !== '#') {
            window.open(currentCommunityUrl, '_blank');
        } else {
            alert('No link available.');
        }
    }
</script>
@endpush
@endsection
