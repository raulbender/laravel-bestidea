<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
    <h2 class="text-base font-bold text-white flex items-center gap-2">
        <span>{{ __('app.ideas.title') }}</span>
        <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-full" x-text="ideas.length">0</span>
    </h2>

    <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs overflow-x-auto no-scrollbar max-w-full">
        <button
            @click="setSort('recent')"
            :class="sortBy === 'recent' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
            {{ __('app.sort.recent') }}
        </button>

        <button
            @click="setSort('hot')"
            :class="sortBy === 'hot' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
            {{ __('app.sort.hot') }}
        </button>

        <button
            @click="setSort('top_rated')"
            :class="sortBy === 'top_rated' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
            {{ __('app.sort.top_rated') }}
        </button>

        @auth
        <button
            @click="toggleMine()"
            :class="filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 border-l border-slate-800 ml-1">
            {{ __('app.sort.mine') }}
        </button>
        @endauth
    </div>
</div>