@extends('layouts.base')

@section('title', trim($__env->yieldContent('auth_title')).' | '.config('app.name'))

@section('content')
<main class="mx-auto flex min-h-screen w-full max-w-md items-center px-6 py-10">
    <section class="w-full rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-white/10 dark:bg-white/5">
        <h1 class="mb-2 text-2xl font-black tracking-tight text-slate-900 dark:text-white">@yield('auth_title')</h1>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-300">@yield('auth_subtitle')</p>
        @yield('auth_form')
        @yield('auth_footer')
    </section>
</main>
@endsection
