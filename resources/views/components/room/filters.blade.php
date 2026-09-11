<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
    <h2 class="text-base font-bold text-white flex items-center gap-2">
        <span>{{ __('app.ideas.title') }}</span>
        <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-full" x-text="ideas.length">0</span>
    </h2>

    <div class="flex items-center justify-between w-full sm:w-auto bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs">
        <!-- Most Recent -->
        <button
            @click="setSort('recent')"
            :class="sortBy === 'recent' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="flex-1 sm:flex-none flex flex-col sm:flex-row items-center justify-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg transition whitespace-nowrap">
            <span class="text-xs leading-none">⏱️</span>
            <span class="text-[10px] sm:text-xs leading-tight">{{ __('app.sort.recent') }}</span>
        </button>

        <!-- Hot / Trending -->
        <button
            @click="setSort('hot')"
            :class="sortBy === 'hot' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="flex-1 sm:flex-none flex flex-col sm:flex-row items-center justify-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg transition whitespace-nowrap">
            <span class="text-xs leading-none">🔥</span>
            <span class="text-[10px] sm:text-xs leading-tight">{{ __('app.sort.hot') }}</span>
        </button>

        <!-- Top Rated -->
        <button
            @click="setSort('top_rated')"
            :class="sortBy === 'top_rated' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="flex-1 sm:flex-none flex flex-col sm:flex-row items-center justify-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg transition whitespace-nowrap">
            <span class="text-xs leading-none">⭐</span>
            <span class="text-[10px] sm:text-xs leading-tight">{{ __('app.sort.top_rated') }}</span>
        </button>

        @auth
        <!-- My Ideas -->
        <button
            @click="toggleMine()"
            :class="filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
            class="flex-1 sm:flex-none flex flex-col sm:flex-row items-center justify-center gap-1 px-2.5 sm:px-3 py-1.5 rounded-lg transition whitespace-nowrap border-l border-slate-800 ml-1">
            <span class="text-xs leading-none">💡</span>
            <span class="text-[10px] sm:text-xs leading-tight">{{ __('app.sort.mine') }}</span>
        </button>
        @endauth
    </div>
</div>