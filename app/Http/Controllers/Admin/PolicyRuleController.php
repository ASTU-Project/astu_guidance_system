<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyRuleController extends Controller
{
    public function index()
    {
        $policies = Policy::query()
            ->latest()
            ->get();

        return view('admin.policy', compact('policies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Policy::create([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.policy')
            ->with('success', 'Policy created successfully.');
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $policy->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.policy')
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
        $policy->delete();

        return redirect()
            ->route('admin.policy')
            ->with('success', 'Policy deleted successfully.');
    }
}
