@extends('layouts.auth')

@section('auth_title', 'Login')
@section('auth_subtitle', 'Sign in to access your courses and progress.')

@section('auth_form')
<form method="POST" action="{{ route('login.store') }}" class="space-y-4">
    @csrf
    <label class="block">
        <span class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Email</span>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 outline-none ring-cyan-400/40 transition focus:ring-2 dark:border-white/15 dark:bg-slate-900/70 dark:text-white">
        @error('email')
        <span class="mt-1 block text-xs text-rose-500 dark:text-rose-300">{{ $message }}</span>
        @enderror
    </label>

    <label class="block">
        <span class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">Password</span>
        <input type="password" name="password" required
            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-900 outline-none ring-cyan-400/40 transition focus:ring-2 dark:border-white/15 dark:bg-slate-900/70 dark:text-white">
        @error('password')
        <span class="mt-1 block text-xs text-rose-500 dark:text-rose-300">{{ $message }}</span>
        @enderror
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 bg-white dark:border-white/20 dark:bg-slate-900/70">
        Remember me
    </label>

    <button type="submit"
        class="w-full rounded-xl bg-cyan-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-cyan-600 dark:bg-cyan-400 dark:text-slate-900 dark:hover:bg-cyan-300">
        Login
    </button>
</form>
@endsection

@section('auth_footer')
@if(Route::has('register'))
<p class="mt-5 text-sm text-slate-700 dark:text-slate-300">
    New user?
    <a href="{{ route('register') }}" class="font-semibold text-cyan-700 hover:text-cyan-600 dark:text-cyan-300 dark:hover:text-cyan-200">Create an account</a>
</p>
@endif
@endsection
