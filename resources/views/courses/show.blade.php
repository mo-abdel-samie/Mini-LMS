@extends('layouts.base')

@section('title', $course->title.' | '.config('app.name'))

@section('content')
    <main class="mx-auto max-w-5xl px-6 py-10 lg:px-10">
        <div class="mb-4 flex items-center justify-end gap-2">
            @include('partials.auth-actions')
        </div>

        @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-300/40 bg-emerald-400/10 p-4 text-sm text-emerald-700 dark:text-emerald-100">
            {{ session('status') }}
        </div>
        @endif

        <a href="{{ route('home') }}" class="mb-6 inline-flex items-center rounded-lg border border-slate-300/70 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:text-cyan-200">
            Back to courses
        </a>

        <section class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur-xl md:p-8 dark:border-white/10 dark:bg-white/5">
            <div class="mb-4 flex items-center justify-between gap-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">
                    {{ $course->level?->name ?? 'Uncategorized' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ optional($course->published_at)->format('M d, Y h:i A') }}</p>
            </div>

            <h1 class="mb-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white md:text-4xl">{{ $course->title }}</h1>
            <p class="mb-6 text-sm text-slate-700 dark:text-slate-200 md:text-base">{{ $course->description ?: 'No description provided.' }}</p>

            @if($course->image_path)
            <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-slate-900/70">
                <img src="{{ asset($course->image_path) }}" alt="{{ $course->title }}" class="h-64 w-full object-cover md:h-80">
            </div>
            @endif

            <div class="mb-6 grid grid-cols-3 gap-3 text-center">
                <div class="rounded-lg bg-slate-100 p-3 dark:bg-slate-900/70">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Lessons</p>
                    <p class="text-base font-bold text-slate-900 dark:text-white">{{ $course->lessons_count }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-3 dark:bg-slate-900/70">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Enrollments</p>
                    <p class="text-base font-bold text-slate-900 dark:text-white">{{ $course->enrollments_count }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-3 dark:bg-slate-900/70">
                    <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Completions</p>
                    <p class="text-base font-bold text-emerald-300">{{ $course->completions_count }}</p>
                </div>
            </div>

            @if($courseProgress)
            <div class="mb-6 rounded-xl border border-slate-200 bg-slate-100 p-4 dark:border-white/10 dark:bg-slate-900/60">
                <div class="mb-2 flex items-center justify-between text-xs">
                    <span class="font-semibold uppercase tracking-wide text-slate-700 dark:text-slate-300">Your Progress</span>
                    <span class="text-slate-700 dark:text-slate-300">{{ $courseProgress['completed'] }}/{{ $courseProgress['total'] }}</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-700/70">
                    <div class="h-full rounded-full bg-cyan-400 transition-all duration-300"
                        style="width: {{ $courseProgress['percent'] }}%"></div>
                </div>
                <p class="mt-2 text-right text-xs text-cyan-700 dark:text-cyan-200">{{ $courseProgress['percent'] }}%</p>
            </div>
            @endif

            <div class="mb-8">
                @if($isEnrolled)
                <span class="inline-flex rounded-lg bg-emerald-300/20 px-4 py-2 text-sm font-bold text-emerald-200">
                    You are enrolled
                </span>
                @elseif(auth()->check())
                <form method="POST" action="{{ route('courses.enroll', $course->slug) }}">
                    @csrf
                    <button type="submit" class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-bold text-slate-900 transition hover:bg-cyan-300">
                        Enroll Now
                    </button>
                </form>
                @else
                @if(Route::has('login'))
                <a href="{{ route('login') }}" class="rounded-lg bg-cyan-400 px-4 py-2 text-sm font-bold text-slate-900 transition hover:bg-cyan-300">
                    Sign in to Enroll
                </a>
                @endif
                @endif
            </div>

            <section>
                <div class="mb-3 flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Course Lessons</h2>
                    @unless($isEnrolled)
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-300">
                        Only free preview lessons are playable before enrollment
                    </p>
                    @endunless
                </div>
                <div class="space-y-2">
                    @forelse($lessons as $lesson)
                    @php $isCompleted = in_array($lesson->id, $completedLessonIds ?? [], true); @endphp
                    <details class="group rounded-xl border {{ $isCompleted ? 'border-emerald-300/40 bg-emerald-400/10' : 'border-slate-200 bg-slate-100 dark:border-white/10 dark:bg-slate-900/60' }}" @if($loop->first) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 marker:content-none">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Lesson {{ $lesson->order }}</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $lesson->title }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($isCompleted)
                                <span class="rounded-md bg-emerald-300/20 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-200">
                                    Completed
                                </span>
                                @endif
                                @if($lesson->is_free_preview && !$isEnrolled)
                                <span class="rounded-md bg-amber-300/20 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-200">
                                    Free Preview
                                </span>
                                @endif
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300 transition group-open:rotate-180">▼</span>
                            </div>
                        </summary>
                        <div class="border-t border-slate-200 dark:border-white/10 px-4 py-3">
                            @if($isEnrolled || $lesson->is_free_preview)
                            <a href="{{ route('courses.lessons.show', ['course' => $course->slug, 'lesson' => $lesson->slug]) }}"
                                class="inline-flex rounded-lg border border-cyan-300/40 bg-cyan-400/10 px-3 py-2 text-xs font-semibold text-cyan-700 dark:text-cyan-200 transition hover:border-cyan-200 hover:text-cyan-100">
                                Watch Lesson Video
                            </a>
                            @else
                            <span class="inline-flex cursor-not-allowed rounded-lg border border-slate-300 bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-500 dark:border-white/20 dark:bg-slate-900/70 dark:text-slate-400">
                                Enroll to Watch
                            </span>
                            @endif
                        </div>
                    </details>
                    @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">No lessons available yet.</p>
                    @endforelse
                </div>
            </section>
        </section>
    </main>
@endsection

