<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentLoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.student');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (Auth::guard('student')->attempt([
            'student_id' => $validated['student_id'],
            'password' => $validated['password'],
        ])) {
            $request->session()->regenerate();

            return redirect()->intended(route('student.dashboard'));
        }

        return back()
            ->withErrors([
                'student_id' => 'The provided credentials do not match our records.',
            ])
            ->onlyInput('student_id');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('student.login');
    }
}
