<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme_mode');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const useDark = savedTheme ? savedTheme === 'dark' : prefersDark;
            document.documentElement.classList.toggle('dark', useDark);
            localStorage.setItem('theme_mode', useDark ? 'dark' : 'light');
        })();
    </script>
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('head')
</head>
<body class="@yield('body_class', 'min-h-screen bg-slate-100 text-slate-900 transition-colors dark:bg-slate-950 dark:text-slate-100')">
    @yield('content')
    @yield('scripts')
</body>
</html>
