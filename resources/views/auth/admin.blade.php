<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login | ASTU Management System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-white to-cyan-50 text-slate-900">
    <div class="relative mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-14 sm:px-6 lg:px-10">
        <div class="pointer-events-none absolute -top-8 -left-4 h-36 w-36 rounded-full bg-cyan-200/40 blur-2xl"></div>
        <div class="pointer-events-none absolute -right-10 -bottom-10 h-44 w-44 rounded-full bg-slate-300/35 blur-2xl"></div>

        <div class="relative w-full max-w-md rounded-md border border-slate-200/80 bg-white/95 p-8 shadow-2xl shadow-slate-200/70 backdrop-blur sm:p-10">
            <div class="space-y-3 text-center">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-700">ASTU Management</p>
                <h1 class="text-3xl font-black text-slate-950 sm:text-4xl">Admin Sign in</h1>
            </div>

            @if ($errors->any())
                <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    Login failed. Please check your credentials.
                </div>
            @endif

            <form action="{{ route('admin.login.submit') }}" method="POST" class="mt-3 space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@astu.edu.et" class="block w-full rounded-md border border-slate-320 bg-slate-50 px-3 py-2 text-slate-900 outline-none transition focus:border-cyan-500" required />
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter your password" class="block w-full rounded-md border border-slate-320 bg-slate-50 px-3 py-2 text-slate-900 outline-none transition focus:border-cyan-500" required />
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:ring-offset-2">Sign in as admin</button>
            </form>

            <p class="mt-6 text-center text-sm text-slate-600">
                Student?
                <a href="{{ route('student.login') }}" class="font-semibold text-cyan-700 hover:text-cyan-800">Use student login</a>
            </p>
        </div>
    </div>
</body>
</html>
