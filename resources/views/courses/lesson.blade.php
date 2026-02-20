@extends('layouts.base')

@section('title', $lesson->title.' | '.$course->title)

@section('content')
    @php
        $videoUrl = trim((string) $lesson->video_url);
        $provider = null;
        $embedId = null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/i', $videoUrl, $matches)) {
            $provider = 'youtube';
            $embedId = $matches[1];
        } elseif (preg_match('/vimeo\.com\/(\d+)/i', $videoUrl, $matches)) {
            $provider = 'vimeo';
            $embedId = $matches[1];
        }
    @endphp

    <main class="mx-auto max-w-5xl px-6 py-10 lg:px-10">
        <div class="mb-4 flex items-center justify-end gap-2">
            @include('partials.auth-actions')
        </div>

        <a href="{{ route('courses.show', $course->slug) }}" class="mb-6 inline-flex items-center rounded-lg border border-slate-300/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:text-cyan-200">
            Back to course
        </a>

        <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-white/10 dark:bg-white/5">
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <p class="rounded-md bg-slate-200 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-700 dark:bg-slate-900/70 dark:text-slate-300">
                    {{ $course->title }}
                </p>
                @if($lesson->is_free_preview && !$isEnrolled)
                <p class="rounded-md bg-amber-300/20 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-200">
                    Free Preview
                </p>
                @elseif($isEnrolled)
                <p class="rounded-md bg-emerald-300/20 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-200">
                    Enrolled Access
                </p>
                @endif
            </div>

            <h1 class="mb-1 text-2xl font-black tracking-tight text-slate-900 dark:text-white md:text-3xl">{{ $lesson->title }}</h1>
            <p class="mb-6 text-xs text-slate-500 dark:text-slate-400">Lesson {{ $lesson->order }}</p>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-black dark:border-white/10">
                @if($provider && $embedId)
                <div class="aspect-video w-full">
                    <div class="js-plyr h-full w-full" data-plyr-provider="{{ $provider }}" data-plyr-embed-id="{{ $embedId }}"></div>
                </div>
                @elseif($videoUrl !== '')
                <video controls playsinline class="js-plyr aspect-video w-full" preload="metadata">
                    <source src="{{ $videoUrl }}">
                    Your browser does not support the video tag.
                </video>
                @else
                <div class="flex aspect-video items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                    No video URL is configured for this lesson yet.
                </div>
                @endif
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @if($previousLesson)
                <a href="{{ route('courses.lessons.show', ['course' => $course->slug, 'lesson' => $previousLesson->slug]) }}"
                    class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 transition hover:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:hover:border-cyan-300/60">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Previous lesson</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $previousLesson->title }}</p>
                </a>
                @else
                <div class="rounded-xl border border-slate-200 bg-slate-100/70 px-4 py-3 dark:border-white/10 dark:bg-slate-900/40">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-500">Previous lesson</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-500">This is the first available lesson.</p>
                </div>
                @endif

                @if($nextLesson)
                <a href="{{ route('courses.lessons.show', ['course' => $course->slug, 'lesson' => $nextLesson->slug]) }}"
                    class="rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-right transition hover:border-cyan-400/60 dark:border-white/15 dark:bg-slate-900/70 dark:hover:border-cyan-300/60">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Next lesson</p>
                    <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $nextLesson->title }}</p>
                </a>
                @else
                <div class="rounded-xl border border-slate-200 bg-slate-100/70 px-4 py-3 dark:border-white/10 dark:bg-slate-900/40 text-right">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-500">Next lesson</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-500">You reached the last available lesson.</p>
                </div>
                @endif
            </div>
        </section>
    </main>
@endsection

@section('scripts')
@auth
<script>
    (() => {
        const progressUrl = @json(route('courses.lessons.progress', ['course' => $course->slug, 'lesson' => $lesson->slug]));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const playerContainer = document.querySelector('.js-plyr');
        if (!progressUrl || !csrfToken || !playerContainer) {
            return;
        }

        let maxSeconds = 0;
        let sentCompleted = false;

        const sendProgress = (payload, keepalive = false) => {
            const body = JSON.stringify(payload);

            fetch(progressUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body,
                credentials: 'same-origin',
                keepalive,
            }).catch(() => {});
        };

        window.addEventListener('plyr:ready', (event) => {
            const { element, player } = event.detail || {};
            if (element !== playerContainer || !player) {
                return;
            }

            const reportWatch = () => {
                const current = Math.max(0, Math.floor(player.currentTime || 0));
                if (current > maxSeconds) {
                    maxSeconds = current;
                    sendProgress({ watch_seconds: maxSeconds });
                }
            };

            player.on('timeupdate', reportWatch);
            player.on('pause', reportWatch);
            player.on('seeking', reportWatch);
            player.on('seeked', reportWatch);
            player.on('ended', () => {
                reportWatch();
                if (!sentCompleted) {
                    sentCompleted = true;
                    sendProgress({ watch_seconds: maxSeconds, completed: true });
                }
            });
        });

        window.addEventListener('beforeunload', () => {
            if (maxSeconds > 0 && !sentCompleted) {
                sendProgress({ watch_seconds: maxSeconds }, true);
            }
        });
    })();
</script>
@endauth
@endsection

