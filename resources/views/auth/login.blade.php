<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F6F7F5]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — SoloLedger</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center p-4 font-sans text-[#17211D]">

    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-12 h-12 rounded-2xl bg-[#0F6B57] text-white items-center justify-center font-bold text-2xl shadow-sm mb-3">
                S
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[#17211D]">SoloLedger</h1>
            <p class="text-xs text-[#66736C] mt-1">Single-business invoicing & financial operations</p>
        </div>

        <!-- Flash messages -->
        @if(session('info'))
            <div class="mb-4 p-3 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs">
                {{ session('info') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                {{ session('error') }}
            </div>
        @endif

        <!-- Card Container -->
        <div class="bg-white border border-[#E3E7E4] rounded-2xl p-6 sm:p-8 shadow-xs">
            <h2 class="text-base font-semibold text-[#17211D] mb-4">Account Sign In</h2>

            <form action="{{ url('/login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-[#17211D] mb-1">Email Address</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] focus:border-transparent transition-all">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-[#17211D] mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-[#E3E7E4] bg-white focus:outline-none focus:ring-2 focus:ring-[#0F6B57] focus:border-transparent transition-all">
                    @error('password')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-[#E3E7E4] text-[#0F6B57] focus:ring-[#0F6B57]">
                        <span class="text-[#66736C]">Remember session</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl font-semibold text-sm text-white bg-[#0F6B57] hover:bg-[#0B5546] shadow-xs transition-colors cursor-pointer">
                    Sign In
                </button>
            </form>

            @if($isDemoMode)
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-[#E3E7E4]"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-[#66736C] font-semibold">Or One-Click Demo</span></div>
                </div>

                <div class="space-y-2.5">
                    <form action="{{ route('demo.login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="owner">
                        <button type="submit"
                            class="w-full flex items-center justify-between p-3 rounded-xl border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-950 transition-colors text-left cursor-pointer">
                            <div>
                                <div class="text-xs font-bold">Sign In as Demo Owner</div>
                                <div class="text-[11px] text-emerald-800">Full control: create & issue invoices, record payments, expenses</div>
                            </div>
                            <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </form>

                    <form action="{{ route('demo.login') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="viewer">
                        <button type="submit"
                            class="w-full flex items-center justify-between p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-900 transition-colors text-left cursor-pointer">
                            <div>
                                <div class="text-xs font-bold">Sign In as Demo Reviewer</div>
                                <div class="text-[11px] text-slate-600">Auditor mode: read-only access to records, PDFs, reports & CSV</div>
                            </div>
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <div class="text-center mt-6 text-xs text-[#66736C]">
            <a href="{{ route('docs') }}" class="font-medium text-[#0F6B57] hover:underline">View SoloLedger Documentation & PRD Specification</a>
        </div>
    </div>

</body>
</html>
