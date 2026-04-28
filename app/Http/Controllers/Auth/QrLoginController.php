<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentCode;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class QrLoginController extends Controller
{
    public function showQrLoginForm()
    {
        return view('auth.qrlogin');
    }

    public function qrLogin(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $studentCode = StudentCode::where('code', $request->code)->first();
        if (!$studentCode) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code.'], 401);
        }
        $student = $studentCode->student;
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 401);
        }
        Auth::guard('student')->login($student);
        Session::regenerate();
        return response()->json(['success' => true, 'redirect' => url('/student/dashboard')]);
    }
}
