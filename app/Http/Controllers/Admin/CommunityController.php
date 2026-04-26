<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityLink::query();

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($b) use ($search) {
                $b->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('leader', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $links = $query->orderBy('type')->orderBy('name')->paginate(50)->withQueryString();

        return view('admin.community', compact('links'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:club,telegram'],
            'url'         => ['required', 'url', 'max:500'],
            'leader'      => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category'    => ['required', 'string', 'max:100'],
            'is_active'   => ['boolean'],
            'logo'        => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo_url'] = $request->file('logo')->store('community_logos', 'public');
        }
        unset($validated['logo']);

        // Handle cover image upload (optional, legacy)
        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('community', 'public');
        }
        unset($validated['image']);

        $validated['is_active'] = $request->boolean('is_active', true);

        CommunityLink::create($validated);

        return redirect()->route('admin.community.index')->with('success', 'Entry created successfully.');
    }

    public function update(Request $request, CommunityLink $community)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:club,telegram'],
            'url'         => ['required', 'url', 'max:500'],
            'leader'      => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category'    => ['nullable', 'string', 'max:100'],
            'is_active'   => ['boolean'],
            'logo'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'image'       => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // Handle logo upload (update)
        if ($request->hasFile('logo')) {
            if ($community->logo_url) {
                Storage::disk('public')->delete($community->logo_url);
            }
            $validated['logo_url'] = $request->file('logo')->store('community_logos', 'public');
        }

        // Handle cover image upload (update)
        if ($request->hasFile('image')) {
            if ($community->image_url) {
                Storage::disk('public')->delete($community->image_url);
            }
            $validated['image_url'] = $request->file('image')->store('community', 'public');
        }

        unset($validated['logo']);
        unset($validated['image']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $community->update($validated);

        return redirect()->route('admin.community.index')->with('success', 'Entry updated successfully.');
    }

    public function destroy(CommunityLink $community)
    {
        if ($community->image_url) {
            Storage::disk('public')->delete($community->image_url);
        }

        $community->delete();

        return redirect()->route('admin.community.index')->with('success', 'Entry deleted successfully.');
    }
}
