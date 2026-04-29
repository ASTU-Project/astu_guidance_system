@extends('layouts.student')

@section('title', 'Profile Settings')
@section('page-title', 'Profile')

@section('content')
    <div class="space-y-5">
        <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
            <h4 class="text-base font-semibold text-slate-900">Student Profile Information</h4>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Full Name</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->name ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Student ID</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->student_id ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Department</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->department ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Current Year</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->current_year ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Current Semester</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->current_semester ?? 'N/A' }}</p>
                </div>

                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Current Section</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900">{{ $student->current_section ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <h4 class="text-base font-semibold text-slate-900">Account Information</h4>

                @if(session('profile_success'))
                    <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('profile_success') }}
                    </div>
                @endif

                @if($errors->profileUpdate->any())
                    <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-medium">Please fix the following:</p>
                        <ul class="mt-1 list-disc pl-5 space-y-0.5">
                            @foreach($errors->profileUpdate->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('student.profile.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="profile-name" class="mb-1 block text-sm font-medium text-slate-700">Full Name</label>
                        <input id="profile-name" type="text" value="{{ $student->name }}" class="w-full cursor-not-allowed rounded-md border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600" disabled>
                        <p class="mt-1 text-xs text-slate-500">Name updates are managed by the administration office.</p>
                    </div>

                    <div>
                        <label for="profile-email" class="mb-1 block text-sm font-medium text-slate-700">Email Address</label>
                        <input id="profile-email" name="email" type="email" value="{{ old('email', $student->email) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Save Profile
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <h4 class="text-base font-semibold text-slate-900">Update Password</h4>

                @if(session('password_success'))
                    <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('password_success') }}
                    </div>
                @endif

                @if($errors->passwordUpdate->any())
                    <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-medium">Please fix the following:</p>
                        <ul class="mt-1 list-disc pl-5 space-y-0.5">
                            @foreach($errors->passwordUpdate->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('student.profile.password.update') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="current-password" class="mb-1 block text-sm font-medium text-slate-700">Current Password</label>
                        <input id="current-password" name="current_password" type="password" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
                    </div>

                    <div>
                        <label for="new-password" class="mb-1 block text-sm font-medium text-slate-700">New Password</label>
                        <input id="new-password" name="password" type="password" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
                    </div>

                    <div>
                        <label for="confirm-password" class="mb-1 block text-sm font-medium text-slate-700">Confirm New Password</label>
                        <input id="confirm-password" name="password_confirmation" type="password" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
                <h4 class="text-base font-semibold text-slate-900">QR Code Login</h4>
                <p class="mt-1 text-sm text-slate-500">Enable or disable logging in with your ID card QR code.</p>

                @if(session('qr_success'))
                    <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('qr_success') }}
                    </div>
                @endif

                <form action="{{ route('student.profile.qr-login.update') }}" method="POST" class="mt-5">
                    @csrf
                    @method('PUT')

                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="qr_login_enabled" value="1" class="peer sr-only" @checked($student->qr_login_enabled)>
                            <div class="h-6 w-11 rounded-full bg-slate-200 transition peer-checked:bg-slate-900"></div>
                            <div class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition peer-checked:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-medium text-slate-700">Allow QR code login</span>
                    </label>

                    <div class="flex justify-end mt-5">
                        <button type="submit" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                            Save Preference
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    {{-- Page specific styles --}}
@endpush

@push('scripts')
    {{-- Page specific scripts --}}
@endpush
