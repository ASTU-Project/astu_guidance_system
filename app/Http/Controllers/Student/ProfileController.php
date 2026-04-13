<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('student.profile', [
            'student' => $request->user('student'),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $student = $request->user('student');

        $validated = $request->validateWithBag('profileUpdate', [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('students', 'email')->ignore($student->id),
            ],
        ]);

        $student->update($validated);

        return redirect()
            ->route('student.profile.edit')
            ->with('profile_success', 'Profile information updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $student = $request->user('student');

        $validated = $request->validateWithBag('passwordUpdate', [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $student->password)) {
            return redirect()
                ->route('student.profile.edit')
                ->withErrors(['current_password' => 'The current password is incorrect.'], 'passwordUpdate');
        }

        $student->update([
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('student.profile.edit')
            ->with('password_success', 'Password updated successfully.');
    }
}
