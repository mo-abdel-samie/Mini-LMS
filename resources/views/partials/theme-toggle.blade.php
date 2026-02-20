<button
    x-data="{
        on: false,
        label: 'Dark Mode',
        init() {
            const saved = localStorage.getItem('theme_mode');
            this.on = saved ? saved === 'dark' : document.documentElement.classList.contains('dark');
            this.label = this.on ? 'Light Mode' : 'Dark Mode';
        },
        toggleTheme() {
            this.on = !this.on;
            document.documentElement.classList.toggle('dark', this.on);
            localStorage.setItem('theme_mode', this.on ? 'dark' : 'light');
            this.label = this.on ? 'Light Mode' : 'Dark Mode';
        }
    }"
    x-init="
        init();
    "
    @click="toggleTheme()"
    class="rounded-lg border border-slate-300/70 bg-white/70 px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-cyan-300 hover:text-cyan-700 dark:border-white/20 dark:bg-slate-900/70 dark:text-slate-100 dark:hover:text-cyan-200">
    <span x-text="label">Dark Mode</span>
</button>
