<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MapLocationController extends Controller
{
    public function index(){
        $locations = MapLocation::query()
            ->orderBy('name')
            ->get();
        return view('admin.map', compact('locations'));
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'category' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_url'] = $request->file('image')->store('map-locations', 'public');
        }

        unset($validated['image']);

        MapLocation::create($validated);

        return redirect()
            ->route('admin.map')
            ->with('success', 'Map location created successfully.');
    }

    public function update(Request $request, MapLocation $location) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'category' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $oldImage = (string) $location->getRawOriginal('image_url');
            if ($oldImage !== '' && ! Str::startsWith($oldImage, ['http://', 'https://'])) {
                Storage::disk('public')->delete($oldImage);
            }

            $validated['image_url'] = $request->file('image')->store('map-locations', 'public');
        }

        unset($validated['image']);

        $location->update($validated);

        return redirect()
            ->route('admin.map')
            ->with('success', 'Map location updated successfully.');
    }

    public function destroy(MapLocation $location) {
        $oldImage = (string) $location->getRawOriginal('image_url');
        if ($oldImage !== '' && ! Str::startsWith($oldImage, ['http://', 'https://'])) {
            Storage::disk('public')->delete($oldImage);
        }

        $location->delete();

        return redirect()
            ->route('admin.map')
            ->with('success', 'Map location deleted successfully.');
    }
}
