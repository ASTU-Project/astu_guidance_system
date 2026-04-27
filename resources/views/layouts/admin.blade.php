<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — ASTUMG</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .nav-item {
            color: #94a3b8;
            transition: color .15s, background .15s;
        }
        .nav-item:hover {
            color: #e2e8f0;
            background: rgba(255, 255, 255, .05);
        }
        .nav-item.active {
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }
        main .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        main .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        main .overflow-x-auto::-webkit-scrollbar-track {
            background: transparent;
        }
        main .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        main .overflow-x-auto > table {
            border-collapse: separate;
            border-spacing: 0;
        }
        main .overflow-x-auto > table thead th {
            white-space: nowrap;
            background: #fff;
        }
        main .overflow-x-auto > table tbody td {
            vertical-align: middle;
        }
        @media (max-width: 640px) {
            main .overflow-x-auto {
                margin-left: -0.75rem;
                margin-right: -0.75rem;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
                padding-bottom: 0.375rem;
            }
            main .overflow-x-auto > table th,
            main .overflow-x-auto > table td {
                white-space: nowrap;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-stone-50 font-sans text-slate-800">
    <div class="flex h-screen overflow-hidden">
        <div id="mobileSidebarBackdrop" class="hidden fixed inset-0 bg-black/20 z-30 lg:hidden"></div>
        <aside id="mainSidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-56 bg-slate-900 flex-shrink-0 flex flex-col transition-transform duration-200 -translate-x-full lg:translate-x-0">
            <div class="flex items-center gap-2.5 px-4 h-14 border-b border-white/[.06]">
                <span class="text-white font-bold text-sm">
                    Admin Panel
                </span>
            </div>
            <nav class="flex-1 py-3 px-2.5 overflow-y-auto space-y-0.5">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-gauge w-4 text-center text-xs"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.students.index') }}" class="nav-item {{ request()->routeIs('admin.students.index') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-user-graduate w-4 text-center text-xs"></i>
                    <span>Students</span>
                </a>
                <a href="{{ route('admin.calendar.index') }}" class="nav-item {{ request()->routeIs('admin.calendar.index') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-calendar-days w-4 text-center text-xs"></i>
                    <span>Calendar</span>
                </a>
                <a href="{{ route('admin.map') }}" class="nav-item {{ request()->routeIs('admin.map') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-map-location-dot w-4 text-center text-xs"></i>
                    <span>Map</span>
                </a>
                <a href="{{ route('admin.departments') }}" class="nav-item {{ request()->routeIs('admin.departments') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-building-columns w-4 text-center text-xs"></i>
                    <span>Fields</span>
                </a>
                <a href="{{ route('admin.subjects.index') }}" class="nav-item {{ request()->routeIs('admin.subjects.index') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-book w-4 text-center text-xs"></i>
                    <span>Subjects</span>
                </a>
                <a href="{{ route('admin.community.index') }}" class="nav-item {{ request()->routeIs('admin.community.index') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-users w-4 text-center text-xs"></i>
                    <span>Community</span>
                </a>
                <a href="{{ route('admin.policy') }}" class="nav-item {{ request()->routeIs('admin.policy') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-scale-balanced w-4 text-center text-xs"></i>
                    <span>Policy</span>
                </a>
                <a href="{{ route('admin.automate') }}" class="nav-item {{ request()->routeIs('admin.automate') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-robot w-4 text-center text-xs"></i>
                    <span>Automate</span>
                </a>
                <a href="{{ route('admin.profile.edit') }}" class="nav-item {{ request()->routeIs('admin.profile.*') ? 'active' : '' }} flex items-center gap-2.5 px-2.5 py-2 rounded-md text-[13px] font-medium">
                    <i class="fa-solid fa-user w-4 text-center text-xs"></i>
                    <span>Profile</span>
                </a>
                
            </nav>
            <div class="px-4 py-3 border-t border-white/[.06]">
                <form action="{{route('logout')}}" method="POST" class="flex items-center justify-between text-[15px] font-semibold text-slate-400 ">
                    @csrf
                    <button type="submit" class="text-[15px] font-semibold text-slate-400">
                        <span class="">
                            Logout
                        </span>
                    </button>
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </form>
            </div>
        </aside>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-14 bg-white border-b border-slate-200/80 px-3 sm:px-5 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <button id="sidebarToggleButton" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-slate-100 text-slate-400 transition">
                        <i class="fas fa-bars text-sm"></i>
                    </button>
                    <h1 class="text-sm font-semibold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    @if(request()->routeIs('admin.automate'))
                        <button
                            type="button"
                            onclick="if (typeof openToolsModal === 'function') openToolsModal();"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            title="Tools Settings"
                            aria-label="Tools Settings"
                        >
                            <i class="fa fa-gear text-xs"></i>
                        </button>
                    @endif
                    <span class="text-xs text-slate-500 hidden sm:block gap-4 flex items-center">
                        <span class="text-slate-700 font-medium">
                            @php
                                $name = auth()->user()->name ?? 'Guest';
                                $name = explode(' ', $name)[0];
                            @endphp
                            <a href="{{ route('admin.profile.edit') }}" class="bg-slate-900 font-bold p-2 px-3 rounded-full text-white">
                                {{ $name[0] }}
                            </a>
                        </span>
                    </span>
                    <span class="text-xs text-slate-400">
                        <form action="{{route('logout')}}" method="POST" class="flex items-center justify-between text-[15px] font-semibold text-slate-400 ">
                            @csrf
                            <button type="submit" class="text-[15px] font-semibold text-slate-400">
                                <span class="">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </span>
                            </button>
                        </form>
                    </span>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto overflow-x-auto p-3 sm:p-5">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('mainSidebar');
            const backdrop = document.getElementById('mobileSidebarBackdrop');
            const toggleButton = document.getElementById('sidebarToggleButton');
            const isDesktop = () => window.innerWidth >= 1024;
            const openSidebar = () => {
                sidebar.classList.remove('-translate-x-full');
                sidebar.classList.add('translate-x-0');
                if (!isDesktop()) {
                    backdrop.classList.remove('hidden');
                }
            };
            const closeSidebar = () => {
                if (isDesktop()) return;
                sidebar.classList.add('-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                backdrop.classList.add('hidden');
            };
            const syncSidebarOnResize = () => {
                if (isDesktop()) {
                    sidebar.classList.remove('-translate-x-full');
                    sidebar.classList.add('translate-x-0');
                    backdrop.classList.add('hidden');
                    return;
                }
                if (!sidebar.classList.contains('translate-x-0')) {
                    sidebar.classList.add('-translate-x-full');
                }
            };
            toggleButton?.addEventListener('click', function () {
                const isOpen = sidebar.classList.contains('translate-x-0') && !sidebar.classList.contains('-translate-x-full');
                if (isOpen && !isDesktop()) {
                    closeSidebar();
                    return;
                }
                openSidebar();
            });
            backdrop?.addEventListener('click', closeSidebar);
            window.addEventListener('resize', syncSidebarOnResize);
            syncSidebarOnResize();
        });
    </script>
    @stack('scripts')
</body>
</html>
