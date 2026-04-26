<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Name</label>
    <input type="text" name="name" value="{{ old('name') }}"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
        placeholder="e.g. ASTU Robotics Club" required>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Logo Image <span class="text-red-500">*</span></label>
    <input id="{{ $p }}-logo" type="file" name="logo" accept="image/png,image/jpeg,image/webp"
        onchange="previewLogo(event, '{{ $p }}-logo-preview')"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-slate-400 focus:outline-none"
        required>
    <p class="mt-1 text-[11px] text-slate-400">JPG, PNG, WEBP — max 4 MB. This is the logo for the channel.</p>
</div>
<img id="{{ $p }}-logo-preview" src="" alt="Logo Preview" class="hidden mt-2 h-16 w-16 rounded-full object-cover border border-slate-200">

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">
        Cover Image <span class="text-slate-400 font-normal">{{ $isEdit ? '(leave empty to keep current)' : '(optional)' }}</span>
    </label>
    <input id="{{ $p }}-image" type="file" name="image" accept="image/png,image/jpeg,image/webp"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs font-semibold file:text-slate-700 hover:file:bg-slate-200 focus:border-slate-400 focus:outline-none">
    <p class="mt-1 text-[11px] text-slate-400">JPG, PNG, WEBP — max 4 MB - Logo For Channel And Header for Club</p>
    <img id="{{ $p }}-image-preview" src="" alt="Preview" class="hidden mt-2 h-24 w-full rounded-md object-cover border border-slate-200">
</div>

<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Type</label>
        <select name="type" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
            <option value="club" @selected(old('type') === 'club')>Club</option>
            <option value="telegram" @selected(old('type') === 'telegram')>Telegram</option>
        </select>
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Category</label>
        <input type="text" name="category" value="{{ old('category') }}"
            class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
            placeholder="e.g. Tech, Sports" required>
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Link</label>
    <input type="url" name="url" value="{{ old('url') }}"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
        placeholder="https://t.me/..." required>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">
        President / Admin <span class="text-slate-400 font-normal">(optional)</span>
    </label>
    <input type="text" name="leader" value="{{ old('leader') }}"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
        placeholder="Full name">
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-700">Description</label>
    <textarea name="description" rows="3"
        class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none"
        placeholder="Short description…" required>{{ old('description') }}</textarea>
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" id="{{ $p }}-is-active" name="is_active" value="1" checked
        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500">
    <label for="{{ $p }}-is-active" class="text-sm font-medium text-slate-700">Active (visible to students)</label>
</div>
@push('scripts')
<script>
    function previewLogo(event, previewId) {
        const input = event.target;
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.classList.add('hidden');
        }
    }

    // Logo preview for logo input (both create and edit)
    ['create', 'edit'].forEach(p => {
        const logoInput = document.getElementById(p + '-logo');
        if (logoInput) {
            logoInput.addEventListener('change', function(e) {
                previewLogo(e, p + '-logo-preview');
            });
        }
    });

    // Cover image preview for both modals
    ['create-image', 'edit-image'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('change', function () {
                const preview = document.getElementById(id.replace('image', 'image-preview'));
                if (this.files[0]) {
                    preview.src = URL.createObjectURL(this.files[0]);
                    preview.classList.remove('hidden');
                } else {
                    preview.src = '';
                    preview.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush