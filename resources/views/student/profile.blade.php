@extends('layouts.student')

@section('title', 'Profile Settings')
@section('page-title', 'Profile')

@section('content')
    <div class="space-y-5">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
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
                        <input id="profile-name" name="name" type="text" value="{{ old('name', $student->name) }}" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:border-slate-400 focus:outline-none" required>
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
        </div>
    </div>
@endsection

@push('styles')
    {{-- Page specific styles --}}
@endpush

@push('scripts')
    {{-- Page specific scripts --}}
@endpush
