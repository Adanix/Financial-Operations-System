<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} — SoloLedger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-[#17211D] flex flex-col font-sans" x-data="{ mobileNavOpen: false }">

    <!-- Top Announcement Bar for Demo Mode -->
    @if(config('app.demo_mode'))
    <div class="bg-amber-100 border-b border-amber-200 px-4 py-1.5 text-xs text-amber-900 flex flex-wrap items-center justify-between gap-2 no-print">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-200 text-amber-950">Demo Mode Active</span>
            <span>You are viewing SoloLedger in an interactive demonstration environment.</span>
        </div>
        @auth
        <div class="flex items-center gap-2">
            <span class="font-medium">Active Role:</span>
            <span class="inline-flex items-center px-2 py-0.5 rounded font-bold uppercase text-[11px] {{ auth()->user()->isOwner() ? 'bg-emerald-200 text-emerald-950' : 'bg-slate-200 text-slate-900' }}">
                {{ auth()->user()->role }}
            </span>
            <!-- Fast Role Switcher -->
            <form action="{{ route('demo.login') }}" method="POST" class="inline-flex gap-1.5">
                @csrf
                @if(auth()->user()->isOwner())
                    <input type="hidden" name="role" value="viewer">
                    <button type="submit" class="text-xs bg-white hover:bg-amber-50 text-amber-900 font-semibold px-2 py-0.5 border border-amber-300 rounded shadow-xs cursor-pointer">
                        Switch to Reviewer (Read-Only)
                    </button>
                @else
                    <input type="hidden" name="role" value="owner">
                    <button type="submit" class="text-xs bg-white hover:bg-amber-50 text-amber-900 font-semibold px-2 py-0.5 border border-amber-300 rounded shadow-xs cursor-pointer">
                        Switch to Owner (Full Access)
                    </button>
                @endif
            </form>
        </div>
        @endauth
    </div>
    @endif

    <div class="flex-1 flex overflow-hidden">
        <!-- Desktop Fixed Sidebar -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-[#12201B] text-slate-300 shrink-0 select-none no-print">
            <!-- Brand Header -->
            <div class="h-16 flex items-center gap-3 px-6 border-b border-[#1C332B]">
                <div class="w-8 h-8 rounded-lg bg-[#0F6B57] flex items-center justify-center font-bold text-white text-base shadow-xs">
                    S
                </div>
                <div>
                    <div class="text-base font-bold text-white tracking-tight leading-none">SoloLedger</div>
                    <div class="text-[10px] text-emerald-400 font-medium tracking-wide uppercase mt-0.5">Financial Operations</div>
                </div>
            </div>

            <!-- Primary Nav -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('customers.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Customers</span>
                </a>

                <a href="{{ route('invoices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('invoices.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Invoices</span>
                </a>

                <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('payments.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Payments</span>
                </a>

                <a href="{{ route('expenses.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('expenses.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Expenses</span>
                </a>

                <a href="{{ route('taxes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('taxes.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    <span>Taxes</span>
                </a>

                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Reports</span>
                </a>

                <div class="pt-4 mt-4 border-t border-[#1C332B]">
                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('settings.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Settings</span>
                    </a>

                    <a href="{{ route('docs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('docs') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300 hover:bg-[#182B24] hover:text-white' }}">
                        <svg class="w-5 h-5 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Documentation</span>
                    </a>
                </div>
            </nav>

            <!-- User Info & Logout footer -->
            <div class="p-4 border-t border-[#1C332B] bg-[#0E1A16] flex items-center justify-between">
                @auth
                    <div class="overflow-hidden pr-2">
                        <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 capitalize">{{ auth()->user()->role }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Sign out" class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-[#182B24] transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                @else
                    <div class="overflow-hidden pr-2">
                        <div class="text-xs font-semibold text-white">Guest</div>
                        <a href="{{ route('login') }}" class="text-[11px] text-emerald-400 hover:underline">Sign In &rarr;</a>
                    </div>
                @endauth
            </div>
        </aside>

        <!-- Mobile Drawer (Alpine.js slideover) -->
        <div x-cloak x-show="mobileNavOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
            <!-- Backdrop -->
            <div x-show="mobileNavOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/60" @click="mobileNavOpen = false"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileNavOpen" x-transition:enter="transition ease-in-out duration-200 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-200 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-[#12201B] text-slate-300">
                    <div class="h-16 flex items-center justify-between px-6 border-b border-[#1C332B]">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#0F6B57] flex items-center justify-center font-bold text-white text-base">S</div>
                            <span class="text-base font-bold text-white">SoloLedger</span>
                        </div>
                        <button type="button" @click="mobileNavOpen = false" class="text-slate-400 hover:text-white p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                        <a href="{{ route('dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Dashboard</a>
                        <a href="{{ route('customers.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('customers.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Customers</a>
                        <a href="{{ route('invoices.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('invoices.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Invoices</a>
                        <a href="{{ route('payments.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('payments.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Payments</a>
                        <a href="{{ route('expenses.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('expenses.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Expenses</a>
                        <a href="{{ route('taxes.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('taxes.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Taxes</a>
                        <a href="{{ route('reports.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('reports.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Reports</a>
                        <div class="pt-3 border-t border-[#1C332B]">
                            <a href="{{ route('settings.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Settings</a>
                            <a href="{{ route('docs') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('docs') ? 'bg-[#1C332B] text-emerald-300 font-semibold' : 'text-slate-300' }}">Documentation</a>
                        </div>
                    </nav>

                    <div class="p-4 border-t border-[#1C332B] bg-[#0E1A16] flex items-center justify-between">
                        @auth
                            <div>
                                <div class="text-xs font-semibold text-white">{{ auth()->user()->name }}</div>
                                <div class="text-[11px] text-slate-400 capitalize">{{ auth()->user()->role }}</div>
                            </div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-rose-300 hover:text-rose-100 font-semibold">Logout</button>
                            </form>
                        @else
                            <div>
                                <div class="text-xs font-semibold text-white">Guest</div>
                            </div>
                            <a href="{{ route('login') }}" class="text-xs text-emerald-400 hover:underline font-semibold">Sign In</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-[#E3E7E4] px-4 sm:px-6 flex items-center justify-between shrink-0 no-print">
                <div class="flex items-center gap-3">
                    <button type="button" @click="mobileNavOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h1 class="text-lg sm:text-xl font-bold tracking-tight text-[#17211D]">
                        {{ $header ?? 'SoloLedger' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isViewer())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Reviewer Mode (Read-Only)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Owner
                            </span>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-[#0F6B57] hover:underline">
                            Sign In
                        </a>
                    @endauth

                    <a href="{{ route('docs') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Help & Docs</span>
                    </a>
                </div>
            </header>

            <!-- Flash Feedback Messages -->
            <div class="px-4 sm:px-6 pt-4 max-w-7xl w-full mx-auto no-print">
                @if(session('success'))
                    <div class="mb-4 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('info'))
                    <div class="mb-4 p-3.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-900 text-sm flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-sky-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0118 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                            <span>{{ session('info') }}</span>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm">
                        <div class="font-semibold mb-1">Please correct the following errors:</div>
                        <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-800">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 max-w-7xl w-full mx-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

</body>
</html>
