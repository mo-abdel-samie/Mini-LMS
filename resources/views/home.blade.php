@extends('layouts.base')

@section('title', config('app.name').' | Home')

@section('head')
    @livewireStyles
@endsection

@section('content')
<header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-between px-6 py-4 lg:px-10">
    <a href="{{ route('home') }}" class="text-sm font-bold uppercase tracking-wider text-cyan-300">
        {{ config('app.name') }}
    </a>
    @include('partials.auth-actions')
</header>
<livewire:home-courses />
@endsection

@section('scripts')
    @livewireScripts
@endsection
