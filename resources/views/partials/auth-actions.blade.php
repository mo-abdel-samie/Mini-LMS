<div class="flex items-center gap-2">
    @auth
    <span class="text-xs text-slate-600 dark:text-slate-300">{{ auth()->user()->name }}</span>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
            class="rounded-lg border border-slate-300/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:text-cyan-200">
            Logout
        </button>
    </form>
    @else
    <a href="{{ route('login') }}"
        class="rounded-lg border border-slate-300/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:text-cyan-200">
        Login
    </a>
    <a href="{{ route('register') }}"
        class="rounded-lg bg-cyan-400 px-3 py-2 text-xs font-bold text-slate-900 transition hover:bg-cyan-300">
        Register
    </a>
    @endauth
    @include('partials.theme-toggle')
</div>
