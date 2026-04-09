@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="">
        <div class="rounded bg-white p-8 shadow-xl shadow-slate-200/50">
            <h1 class="text-3xl font-semibold text-slate-950">Admin dashboard</h1>
            <p class="mt-4 text-slate-600">
                The admin area is now rendered as a Blade view with the requested header and sidebar layout.
            </p>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                <div class="rounded-3xl bg-slate-100 p-6">
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Status</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-950">Ready for Blade</p>
                </div>
                <div class="rounded-3xl bg-slate-100 p-6">
                    <p class="text-sm uppercase tracking-[0.24em] text-slate-500">Next step</p>
                    <p class="mt-3 text-2xl font-semibold text-slate-950">Build the admin UI with Blade templates</p>
                </div>
            </div>
        </div>
    </div>
@endsection
