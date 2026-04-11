<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MapLocation;
use Illuminate\Http\Request;

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
        ]);

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
        ]);

        $location->update($validated);

        return redirect()
            ->route('admin.map')
            ->with('success', 'Map location updated successfully.');
    }

    public function destroy(MapLocation $location) {
        $location->delete();

        return redirect()
            ->route('admin.map')
            ->with('success', 'Map location deleted successfully.');
    }
}
