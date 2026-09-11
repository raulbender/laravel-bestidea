<div
    @click="openIdeaDetails(idea)"
    class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 cursor-pointer transition flex flex-col justify-between gap-4">

    <p class="text-slate-200 text-sm leading-relaxed break-words" x-text="idea.content"></p>

    <div class="flex items-center justify-between pt-3 border-t border-slate-800/60 text-xs gap-2">
        <span class="text-slate-500" x-text="idea.created_at_human"></span>

        <div class="flex items-center gap-2">
            <span class="flex items-center gap-1 text-slate-400 bg-slate-950/60 border border-slate-800 px-2.5 py-1 rounded-lg">
                💬 <span x-text="idea.comments_count || 0"></span>
            </span>

            <span class="flex items-center gap-1 bg-slate-950 border border-slate-800 px-2.5 py-1 rounded-lg text-slate-300">
                <span class="text-amber-400">⭐</span>
                <span class="font-bold" x-text="idea.avg_score || idea.ratings_avg || '0.00'"></span>
            </span>
        </div>
    </div>
</div>