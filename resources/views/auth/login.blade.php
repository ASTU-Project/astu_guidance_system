<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login | ASTU Management System</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-4 py-16 sm:px-6 lg:px-10">
        <div class="rounded border border-gray-300 bg-white p-10 shadow-xl shadow-slate-200/40">
            <div class="space-y-4">
                {{-- <p class="text-sm font-semibold uppercase tracking-[0.28em] text-cyan-700">Welcome back</p> --}}
                <h1 class="text-4xl font-extrabold text-slate-950 text-center">Sign in</h1>
            </div>

            <form action="{{ url('/login') }}" method="POST" class="mt-10 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email address</label>
                    <div class="mt-2 flex items-center gap-3 rounded border-[0.5px] border-slate-300/70 bg-slate-50 px-4 py-2">
                        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@astu.edu.et" class="w-full border-0 bg-transparent text-slate-900 outline-none placeholder:text-slate-400" required />
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <div class="mt-2 flex items-center gap-3 rounded border-[0.5px] border-slate-300/70 bg-slate-50 px-4 py-2">
                        <input id="password" name="password" type="password" placeholder="Enter your password" class="w-full border-0 bg-transparent text-slate-900 outline-none placeholder:text-slate-400" required />
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-3 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <a href="#" class="font-medium text-cyan-700 hover:text-cyan-800">Forgot password?</a>
                    <span>Need help? Ask your admin.</span>
                </div>

                <button type="submit" class="w-full rounded-md bg-slate-950 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
