<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Volt\Component;
use Modules\Course\Models\Course;
use Modules\Enrollment\Models\Enrollment;


new class extends Component {
    public array $enrolledCourseIds = [];

    public function mount(): void
    {
        if (! Auth::check() || ! Schema::hasTable('enrollments')) {
            return;
        }

        $this->enrolledCourseIds = Enrollment::query()
            ->where('user_id', Auth::id())
            ->pluck('course_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function with(): array
    {
        $courses =  Course::with(['level', 'lessons:id,course_id,title,order'])
                ->withCount(['lessons', 'enrollments', 'completions'])
                ->where('is_published', true)
                ->orderByDesc('published_at')
                ->get();

        $courseProgress = [];

        if (Auth::check() && $courses->isNotEmpty()) {
            $completedLessonsByCourse = DB::table('lesson_progress')
                ->selectRaw('course_id, COUNT(DISTINCT lesson_id) as completed_lessons')
                ->where('user_id', Auth::id())
                ->whereNotNull('completed_at')
                ->groupBy('course_id')
                ->pluck('completed_lessons', 'course_id');

            foreach ($courses as $course) {
                $totalLessons = $course->lessons_count;
                $completedLessons = ($completedLessonsByCourse[$course->id] ?? 0);
                $percent = $totalLessons > 0 ? min(100, round(($completedLessons / $totalLessons) * 100)) : 0;

                $courseProgress[$course->id] = [
                    'completed' => $completedLessons,
                    'total' => $totalLessons,
                    'percent' => $percent,
                ];
            }
        }

        return [
            'courses' => $courses,
            'courseProgress' => $courseProgress,
        ];
    }
};
?>

<div class="relative min-h-screen">
    <div
        class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.18),transparent_40%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.15),transparent_35%),linear-gradient(135deg,#f8fafc_0%,#e2e8f0_60%,#dbeafe_100%)] dark:bg-[radial-gradient(circle_at_20%_20%,rgba(56,189,248,0.25),transparent_40%),radial-gradient(circle_at_80%_0%,rgba(245,158,11,0.25),transparent_35%),linear-gradient(135deg,#020617_0%,#0f172a_60%,#111827_100%)]">
    </div>

    <main class="relative mx-auto max-w-7xl px-6 py-10 lg:px-10">
        <section
            class="mb-10 rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-sm backdrop-blur-xl dark:border-white/10 dark:bg-white/5">
            <p
                class="mb-3 inline-flex rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-cyan-300">
                Mini LMS
            </p>
            <h1 class="mb-3 text-4xl font-black tracking-tight text-slate-900 dark:text-white md:text-5xl">Published
                Courses</h1>
        </section>


        <section x-data="{view: 'grid'}" class="space-y-6">
            @if (session('status'))
            <div
                class="rounded-2xl border border-emerald-300/40 bg-emerald-400/10 p-4 text-sm text-emerald-700 dark:text-emerald-100">
                {{ session('status') }}
            </div>
            @endif

            <div
                class="grid gap-4 rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm md:grid-cols-[1fr_auto_auto] dark:border-white/10 dark:bg-white/5">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white my-auto">
                    All Courses ({{ $courses->count() }})
                </h2>

                <div class="block">
                    <span
                        class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">View</span>
                    <div
                        class="inline-flex rounded-xl border border-slate-300 bg-white p-1 dark:border-white/15 dark:bg-slate-900/70">
                        <button type="button" @click="view = 'grid'"
                            :class="view === 'grid' ? 'bg-cyan-400 text-slate-900' : 'text-slate-600 dark:text-slate-300'"
                            class="rounded-lg px-3 py-2 text-xs font-bold transition">
                            Grid
                        </button>
                        <button type="button" @click="view = 'list'"
                            :class="view === 'list' ? 'bg-cyan-400 text-slate-900' : 'text-slate-600 dark:text-slate-300'"
                            class="rounded-lg px-3 py-2 text-xs font-bold transition">
                            List
                        </button>
                    </div>
                </div>
            </div>

            @if($courses->isEmpty())
            <div
                class="rounded-2xl border border-amber-300/30 bg-amber-400/10 p-6 text-sm text-amber-700 dark:text-amber-100">
                No published courses match your filters yet.
            </div>
            @else
            <div x-show="view === 'grid'" x-transition class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($courses as $course)
                <article
                    class="group rounded-2xl border border-slate-200 bg-white/80 p-5 shadow-sm transition hover:-translate-y-1 hover:border-cyan-400/50 dark:border-white/10 dark:bg-white/5 dark:hover:border-cyan-300/50 dark:hover:bg-white/10">
                    <div class="mb-4 h-40 overflow-hidden rounded-xl bg-slate-200 dark:bg-slate-900/80">
                        @if($course->image_path)
                        <img src="{{ asset($course->image_path) }}" alt="{{ $course->title }}"
                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                        <div class="flex h-full items-center justify-center text-sm text-slate-500 dark:text-slate-400">
                            No image</div>
                        @endif
                    </div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-300">{{ $course->level?->name
                            ?? 'Uncategorized' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{
                            optional($course->published_at)->format('M d, Y') }}</p>
                    </div>
                    <h2 class="mb-2 line-clamp-2 text-xl font-bold text-slate-900 dark:text-white">{{ $course->title }}
                    </h2>
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-lg bg-slate-100 p-2 dark:bg-slate-900/70">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">Lessons
                            </p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $course->lessons_count }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-100 p-2 dark:bg-slate-900/70">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Enrollments</p>
                            <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $course->enrollments_count }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-slate-100 p-2 dark:bg-slate-900/70">
                            <p class="text-[10px] uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Completions</p>
                            <p class="text-sm font-bold text-emerald-300">{{ $course->completions_count }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <a href="{{ route('courses.show', $course->slug) }}"
                            class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:border-cyan-300 dark:hover:text-cyan-200">
                            Course Details
                        </a>
                        @if(in_array($course->id, $enrolledCourseIds, true))
                        <span
                            class="flex-1 rounded-lg bg-emerald-300/20 px-3 py-2 text-center text-xs font-bold text-emerald-200">Enrolled</span>
                        @else
                        @if(Route::has('login') || auth()->check())
                        <form method="POST" action="{{ route('courses.enroll', $course->slug) }}" class="flex-1">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-lg bg-cyan-400 px-3 py-2 text-xs font-bold text-slate-900 transition hover:bg-cyan-300">
                                Enroll Now
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </article>
                @endforeach
            </div>

            <div x-show="view === 'list'" x-transition class="space-y-3">
                @foreach($courses as $course)
                <article
                    class="grid gap-3 rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm md:grid-cols-[1fr_auto] md:items-center dark:border-white/10 dark:bg-white/5">
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-cyan-300">
                            {{ $course->level?->name ?? 'Uncategorized' }} • {{
                            optional($course->published_at)->format('M d, Y') }}
                        </p>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ $course->title }}</h2>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span
                            class="rounded-lg bg-slate-100 px-3 py-2 text-slate-700 dark:bg-slate-900/70 dark:text-slate-200">Lessons
                            {{
                            $course->lessons_count }}</span>
                        <span
                            class="rounded-lg bg-slate-100 px-3 py-2 text-slate-700 dark:bg-slate-900/70 dark:text-slate-200">Enrollments
                            {{
                            $course->enrollments_count }}</span>
                        <span class="rounded-lg bg-emerald-300/20 px-3 py-2 text-emerald-200">Completions {{
                            $course->completions_count }}</span>
                    </div>
                    <div class="flex items-center gap-2 md:col-span-2">
                        <a href="{{ route('courses.show', $course->slug) }}"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-cyan-400 hover:text-cyan-700 dark:border-white/20 dark:text-slate-100 dark:hover:border-cyan-300 dark:hover:text-cyan-200">
                            Course Details
                        </a>
                        @if(in_array($course->id, $enrolledCourseIds, true))
                        <span
                            class="rounded-lg bg-emerald-300/20 px-3 py-2 text-xs font-bold text-emerald-200">Enrolled</span>
                        @else
                        @if(Route::has('login') || auth()->check())
                        <form method="POST" action="{{ route('courses.enroll', $course->slug) }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-cyan-400 px-3 py-2 text-xs font-bold text-slate-900 transition hover:bg-cyan-300">
                                Enroll Now
                            </button>
                        </form>
                        @endif
                        @endif
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </section>
    </main>
</div>