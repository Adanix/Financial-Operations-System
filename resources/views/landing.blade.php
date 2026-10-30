<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloLedger — Precision Financial Operations for Independent Businesses</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full text-[#17211D] font-sans antialiased flex flex-col bg-[#F6F7F5]" x-data="{ mobileMenuOpen: false, copied: false }">

    <!-- Top Announcement / Contact Bar -->
    <div class="bg-[#12201B] text-slate-300 text-xs py-2 px-4 border-b border-[#1C332B]">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-[#0F6B57] text-white">
                    SoloLedger v1.0
                </span>
                <span class="text-slate-300">Modular Financial Operations & Ledger System</span>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="text-slate-400">Direct Inquiries:</span>
                <a href="mailto:{{ $contactEmail }}" class="text-emerald-400 hover:text-emerald-300 font-semibold underline decoration-emerald-400/50">
                    {{ $contactEmail }}
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-[#E3E7E4]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-[#0F6B57] flex items-center justify-center font-bold text-white text-lg shadow-sm">
                    S
                </div>
                <div>
                    <span class="text-lg font-bold text-[#17211D] tracking-tight leading-none block">SoloLedger</span>
                    <span class="text-[10px] text-[#0F6B57] font-semibold tracking-wider uppercase">Financial Operations</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-[#66736C]">
                <a href="#features" class="hover:text-[#17211D] transition-colors">Key Capabilities</a>
                <a href="#architecture" class="hover:text-[#17211D] transition-colors">Financial Engine</a>
                <a href="#exports" class="hover:text-[#17211D] transition-colors">Export Formats</a>
                <a href="#demo" class="hover:text-[#17211D] transition-colors">Live Demo</a>
                <a href="{{ route('docs') }}" class="hover:text-[#17211D] transition-colors">Documentation</a>
                <a href="#contact" class="hover:text-[#17211D] transition-colors">Contact</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="hidden md:flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 text-xs font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors">
                        Go to Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors">
                        Launch Demo &rarr;
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger Button -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="md:hidden p-2 text-slate-600 hover:text-slate-900 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        <!-- Mobile Menu Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-b border-[#E3E7E4] bg-white px-4 pt-2 pb-4 space-y-2 text-xs font-semibold">
            <a @click="mobileMenuOpen = false" href="#features" class="block py-2 text-slate-700">Key Capabilities</a>
            <a @click="mobileMenuOpen = false" href="#architecture" class="block py-2 text-slate-700">Financial Engine</a>
            <a @click="mobileMenuOpen = false" href="#exports" class="block py-2 text-slate-700">Export Formats</a>
            <a @click="mobileMenuOpen = false" href="#demo" class="block py-2 text-slate-700">Live Demo</a>
            <a href="{{ route('docs') }}" class="block py-2 text-slate-700">Documentation</a>
            <a @click="mobileMenuOpen = false" href="#contact" class="block py-2 text-slate-700">Contact</a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center px-4 py-2 text-xs font-bold text-white bg-[#0F6B57] rounded-xl">
                        Go to Dashboard &rarr;
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center px-4 py-2 text-xs font-bold text-white bg-[#0F6B57] rounded-xl">
                        Launch Demo / Sign In
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-12 pb-16 lg:pt-20 lg:pb-24 border-b border-[#E3E7E4] bg-gradient-to-b from-white via-white to-[#F6F7F5]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto space-y-6">
                    <!-- Pill Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#0F6B57] border border-emerald-200 shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-[#0F6B57] animate-pulse"></span>
                        Built for Independent Professionals & Consultants
                    </div>

                    <!-- Main Headline -->
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-[#17211D] tracking-tight leading-[1.15]">
                        Financial Clarity & Invoicing Without <span class="text-[#0F6B57]">ERP Overhead</span>
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-base sm:text-lg text-[#66736C] leading-relaxed">
                        SoloLedger is a purpose-built financial operations platform. Issue immutable PDF invoices, track operating expenses with recoverable input tax, prevent overpayments, and monitor management profitability in real time.
                    </p>

                    <!-- Hero Call to Action Buttons -->
                    <div class="flex flex-wrap items-center justify-center gap-3 pt-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-6 py-3.5 text-sm font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                                Open Dashboard &rarr;
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-3.5 text-sm font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-sm transition-all transform hover:-translate-y-0.5 cursor-pointer">
                                Try Interactive Demo &rarr;
                            </a>
                        @endauth

                        <a href="{{ route('docs') }}" class="px-5 py-3.5 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-[#E3E7E4] rounded-xl shadow-2xs transition-colors">
                            Read Architecture Guide
                        </a>

                        <a href="mailto:{{ $contactEmail }}" class="px-5 py-3.5 text-sm font-semibold text-slate-700 bg-emerald-50/60 hover:bg-emerald-100/60 border border-emerald-200 text-[#0F6B57] rounded-xl shadow-2xs transition-colors">
                            Contact: {{ $contactEmail }}
                        </a>
                    </div>
                </div>

                <!-- Product Preview Card Mockup -->
                <div class="mt-12 lg:mt-16 max-w-5xl mx-auto bg-white border border-[#E3E7E4] rounded-3xl p-4 sm:p-6 lg:p-8 shadow-xl">
                    <div class="flex items-center justify-between pb-4 mb-6 border-b border-[#E3E7E4]">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="text-xs font-mono text-[#66736C] ml-2 font-medium">SoloLedger Live Financial Dashboard</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                Single Currency: {{ $settings->currency_code ?? 'USD' }} ({{ $currencySymbol }})
                            </span>
                        </div>
                    </div>

                    <!-- Metrics Grid Mockup -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div class="p-4 rounded-2xl bg-[#F8FAF9] border border-[#E3E7E4]">
                            <span class="text-[11px] font-semibold text-[#66736C] uppercase block">Revenue (MTD)</span>
                            <span class="text-xl sm:text-2xl font-bold text-[#17211D] mt-1 block">{{ $currencySymbol }}300.00</span>
                            <span class="text-[10px] text-emerald-700 font-medium mt-1 block">Accrual invoiced sales</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#F8FAF9] border border-[#E3E7E4]">
                            <span class="text-[11px] font-semibold text-[#66736C] uppercase block">Outstanding</span>
                            <span class="text-xl sm:text-2xl font-bold text-amber-700 mt-1 block">{{ $currencySymbol }}170.00</span>
                            <span class="text-[10px] text-amber-800 font-medium mt-1 block">Active receivables</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-[#F8FAF9] border border-[#E3E7E4]">
                            <span class="text-[11px] font-semibold text-[#66736C] uppercase block">Operating Expenses</span>
                            <span class="text-xl sm:text-2xl font-bold text-[#17211D] mt-1 block">{{ $currencySymbol }}90.00</span>
                            <span class="text-[10px] text-[#66736C] font-medium mt-1 block">Ex. input tax credits</span>
                        </div>
                        <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200">
                            <span class="text-[11px] font-semibold text-emerald-800 uppercase block">Operating Profit</span>
                            <span class="text-xl sm:text-2xl font-bold text-emerald-800 mt-1 block">{{ $currencySymbol }}210.00</span>
                            <span class="text-[10px] text-emerald-700 font-medium mt-1 block">Net management margin</span>
                        </div>
                    </div>

                    <!-- Visual Snippet Bar -->
                    <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-[#0F6B57] text-white flex items-center justify-center font-bold">
                                &check;
                            </div>
                            <div>
                                <span class="font-bold text-[#17211D] block">Deterministic Precision Guarantee</span>
                                <span class="text-[#66736C]">Zero floating-point inaccuracies using Brick\Math BigDecimal with Half-Up rounding.</span>
                            </div>
                        </div>
                        <a href="{{ route('docs') }}" class="text-xs font-semibold text-[#0F6B57] hover:underline whitespace-nowrap">
                            Learn more about calculations &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Showcase -->
        <section id="features" class="py-16 lg:py-24 border-b border-[#E3E7E4]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-2xl mx-auto mb-12 lg:mb-16">
                    <h2 class="text-xs font-bold text-[#0F6B57] uppercase tracking-wider mb-2">Core Capabilities</h2>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#17211D]">Built Specifically for Solo Business Operators</h3>
                    <p class="text-sm text-[#66736C] mt-2">Every feature is designed around operational certainty, auditability, and speed.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Feature 1 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            1
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">Immutable Invoice Snapshots</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            When an invoice is issued, business coordinates, customer address, and bank remittance instructions are frozen into immutable JSON snapshots. Historical invoices never warp when master settings change.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            2
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">Exclusive & Inclusive Tax Engine</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            Support for tax-exclusive line items (added on top) and tax-inclusive lines (extracted via standard tax formula). Proportional invoice discounts are distributed across lines via the largest-remainder rule.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            3
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">Overpayment Protection & Reversals</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            Pessimistic row locking blocks concurrent overpayments. Invoices can receive multiple partial payments, and mistaken payments can be formally reversed with mandatory audit reasoning.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            4
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">Recoverable Input Tax Claims</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            Operating expenses distinguish between recoverable input tax (enters VAT claim ledger) and non-recoverable tax (enters P&L expense burden). Private receipt attachments are securely stored.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            5
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">As-Of Date Aged Receivables</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            Point-in-time historical ledger reconstruction divides receivables into Current, 1-30, 31-60, 61-90, and 90+ day overdue brackets based on payments completed as of the selected date.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 shadow-xs hover:border-[#0F6B57]/50 transition-colors">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#0F6B57] flex items-center justify-center font-bold text-lg mb-4">
                            6
                        </div>
                        <h4 class="text-base font-bold text-[#17211D] mb-2">Role-Based Access Control</h4>
                        <p class="text-xs text-[#66736C] leading-relaxed">
                            Dual-role security enforcing strict permissions. <strong>Owner</strong> has complete mutation rights; <strong>Reviewer (Viewer)</strong> enjoys read-only access for accountants with 403 Forbidden on any write attempt.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Multi-Format Export Section -->
        <section id="exports" class="py-16 lg:py-24 border-b border-[#E3E7E4] bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <span class="text-xs font-bold text-[#0F6B57] uppercase tracking-wider block mb-2">Multi-Format Data Portability</span>
                        <h3 class="text-2xl sm:text-3xl font-extrabold text-[#17211D] leading-tight">
                            Export Reports in PDF, Excel (.xlsx), and CSV at Any Time
                        </h3>
                        <p class="text-sm text-[#66736C] mt-3 leading-relaxed">
                            Never get locked into a proprietary database. SoloLedger includes streaming multi-format export engines for all financial ledgers:
                        </p>
                        <div class="mt-6 space-y-4">
                            <div class="flex items-start gap-3">
                                <span class="px-2 py-1 rounded bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">PDF</span>
                                <div>
                                    <h5 class="text-xs font-bold text-[#17211D]">Publication-Ready A4 PDF Document</h5>
                                    <p class="text-xs text-[#66736C]">Formatted corporate presentation with company header snapshot, KPI cards, itemized tables, and pagination.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-800 text-xs font-bold border border-emerald-200">XLSX</span>
                                <div>
                                    <h5 class="text-xs font-bold text-[#17211D]">Native Microsoft Excel (.xlsx) Workbook</h5>
                                    <p class="text-xs text-[#66736C]">Generated using OpenSpout streaming with real numerical types, styled header rows, and zero memory overhead.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="px-2 py-1 rounded bg-slate-100 text-slate-800 text-xs font-bold border border-slate-300">CSV</span>
                                <div>
                                    <h5 class="text-xs font-bold text-[#17211D]">Sanitized Spreadsheet CSV</h5>
                                    <p class="text-xs text-[#66736C]">Lightweight text streaming with automatic protection against CSV Formula Injection.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right visual display -->
                    <div class="bg-[#F8FAF9] border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-[#E3E7E4]">
                            <span class="text-xs font-bold text-[#17211D] uppercase">Export Capabilities Matrix</span>
                            <span class="text-[11px] text-[#0F6B57] font-semibold">5 Modules Supported</span>
                        </div>
                        <ul class="divide-y divide-slate-100 text-xs">
                            <li class="py-2.5 flex justify-between items-center">
                                <span class="font-medium text-slate-800">Management Profit & Loss (P&L)</span>
                                <span class="text-[11px] text-[#0F6B57] font-mono">PDF &bull; XLSX &bull; CSV</span>
                            </li>
                            <li class="py-2.5 flex justify-between items-center">
                                <span class="font-medium text-slate-800">Tax & VAT Filing Summary</span>
                                <span class="text-[11px] text-[#0F6B57] font-mono">PDF &bull; XLSX &bull; CSV</span>
                            </li>
                            <li class="py-2.5 flex justify-between items-center">
                                <span class="font-medium text-slate-800">Revenue & Invoiced Sales Ledger</span>
                                <span class="text-[11px] text-[#0F6B57] font-mono">PDF &bull; XLSX &bull; CSV</span>
                            </li>
                            <li class="py-2.5 flex justify-between items-center">
                                <span class="font-medium text-slate-800">Operating Expense Breakdown Ledger</span>
                                <span class="text-[11px] text-[#0F6B57] font-mono">PDF &bull; XLSX &bull; CSV</span>
                            </li>
                            <li class="py-2.5 flex justify-between items-center">
                                <span class="font-medium text-slate-800">Historical Aged Receivables Ledger</span>
                                <span class="text-[11px] text-[#0F6B57] font-mono">PDF &bull; XLSX &bull; CSV</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Demo Credentials Section -->
        <section id="demo" class="py-16 lg:py-24 border-b border-[#E3E7E4] bg-[#F6F7F5]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto bg-white border border-[#E3E7E4] rounded-3xl p-6 sm:p-10 shadow-sm text-center space-y-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-amber-100 text-amber-900">
                        Interactive Demonstration
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-[#17211D]">
                        Experience Both Roles Instantly
                    </h3>
                    <p class="text-xs sm:text-sm text-[#66736C]">
                        SoloLedger is seeded with realistic business data, multi-month revenue and expenses, partial payments, and overdue tracking.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left pt-2">
                        <!-- Owner Card -->
                        <div class="p-5 rounded-2xl border border-emerald-200 bg-emerald-50/30">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-emerald-900 uppercase">Owner Account</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">Full Access</span>
                            </div>
                            <p class="text-xs font-mono text-slate-800 mb-1">Email: <strong>owner@example.com</strong></p>
                            <p class="text-xs font-mono text-slate-800 mb-4">Password: <strong>password</strong></p>
                            <form action="{{ route('demo.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="owner">
                                <button type="submit" class="w-full py-2 px-3 text-xs font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-xs transition-colors cursor-pointer">
                                    Login as Owner &rarr;
                                </button>
                            </form>
                        </div>

                        <!-- Viewer Card -->
                        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-900 uppercase">Reviewer Account</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-200 text-slate-800">Read-Only</span>
                            </div>
                            <p class="text-xs font-mono text-slate-800 mb-1">Email: <strong>viewer@example.com</strong></p>
                            <p class="text-xs font-mono text-slate-800 mb-4">Password: <strong>password</strong></p>
                            <form action="{{ route('demo.login') }}" method="POST">
                                @csrf
                                <input type="hidden" name="role" value="viewer">
                                <button type="submit" class="w-full py-2 px-3 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl shadow-xs transition-colors cursor-pointer">
                                    Login as Reviewer &rarr;
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact & Developer Inquiries Section -->
        <section id="contact" class="py-16 lg:py-24 bg-white border-b border-[#E3E7E4]">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <span class="text-xs font-bold text-[#0F6B57] uppercase tracking-wider block">Get in Touch</span>
                <h3 class="text-2xl sm:text-4xl font-extrabold text-[#17211D]">
                    Custom Development & Inquiries
                </h3>
                <p class="text-sm text-[#66736C] max-w-2xl mx-auto leading-relaxed">
                    Have a custom business requirement, need integration with proprietary billing APIs, or require a private deployment of SoloLedger? Contact me directly:
                </p>

                <!-- Contact Box -->
                <div class="max-w-md mx-auto p-6 rounded-3xl bg-[#12201B] text-white shadow-xl space-y-4">
                    <span class="text-xs text-emerald-400 font-semibold uppercase tracking-wider block">Direct Contact Email</span>
                    <div class="text-lg sm:text-xl font-bold font-mono text-white select-all">
                        {{ $contactEmail }}
                    </div>

                    <div class="flex items-center justify-center gap-3 pt-2">
                        <a href="mailto:{{ $contactEmail }}?subject=SoloLedger%20Inquiry" class="px-5 py-2.5 text-xs font-bold text-white bg-[#0F6B57] hover:bg-[#0B5546] rounded-xl shadow-sm transition-colors cursor-pointer">
                            Send Email &rarr;
                        </a>

                        <button @click="navigator.clipboard.writeText('{{ $contactEmail }}'); copied = true; setTimeout(() => copied = false, 2000)" type="button" class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-[#1C332B] hover:bg-[#254238] rounded-xl transition-colors cursor-pointer">
                            <span x-show="!copied">Copy Address</span>
                            <span x-show="copied" x-cloak class="text-emerald-300">Copied!</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-[#12201B] text-slate-400 text-xs py-12 border-t border-[#1C332B]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-[#1C332B]">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#0F6B57] flex items-center justify-center font-bold text-white text-base">
                        S
                    </div>
                    <div>
                        <span class="text-base font-bold text-white">SoloLedger</span>
                        <span class="text-[10px] text-emerald-400 block font-semibold uppercase">Financial Operations System</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-xs font-medium">
                    <a href="{{ route('docs') }}" class="hover:text-white transition-colors">Documentation</a>
                    <a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a>
                    <a href="mailto:{{ $contactEmail }}" class="text-emerald-400 hover:text-emerald-300 font-semibold">{{ $contactEmail }}</a>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-400">
                <p>&copy; 2026 SoloLedger. All rights reserved.</p>
                <p>Engineered for deterministic precision, compliance, and speed.</p>
            </div>
        </div>
    </footer>

</body>
</html>
