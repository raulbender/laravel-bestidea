<div
    x-show="activeIdea !== null"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    @keydown.escape.window="closeIdeaDetails()">

    <div
        @click.outside="closeIdeaDetails()"
        class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">

        <div class="p-5 border-b border-slate-800">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-xl">
                        👤
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-200" x-text="activeIdea?.author_name || '{{ __('Anônimo') }}'"></p>
                        <p class="text-xs text-slate-500" x-text="activeIdea?.created_at_human"></p>
                    </div>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5 bg-slate-950 border border-slate-800 px-3 py-1.5 rounded-xl">
                        <span class="text-amber-400 text-sm">⭐</span>
                        <span class="font-bold text-slate-200" x-text="activeIdea?.avg_score || activeIdea?.ratings_avg || '0.00'"></span>
                    </div>
                    <button @click="closeIdeaDetails()" class="text-slate-400 hover:text-white p-2 rounded-xl bg-slate-950 border border-slate-800 transition">✕</button>
                </div>
            </div>
            
            <p class="text-slate-100 font-medium text-base leading-relaxed break-words" x-text="activeIdea?.content"></p>
        </div>

        <div class="p-3 bg-slate-900 border-b border-slate-800 flex justify-center">
            <div class="flex p-1 bg-slate-950 border border-slate-800 rounded-xl w-full max-w-md">
                <button
                    @click="activeTab = 'comments'"
                    :class="activeTab === 'comments' ? 'bg-slate-800 text-amber-400 shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                    class="flex-1 py-1.5 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2">
                    💬 Comentários (<span x-text="activeIdea?.comments_count || 0"></span>)
                </button>
                <button
                    @click="activeTab = 'ratings'"
                    :class="activeTab === 'ratings' ? 'bg-slate-800 text-amber-400 shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                    class="flex-1 py-1.5 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2">
                    ⭐ Avaliações (<span x-text="activeIdea?.ratings_count || 0"></span>)
                </button>
            </div>
        </div>

        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <div x-show="activeTab === 'comments'" class="space-y-5">
                <div class="flex gap-2">
                    <input
                        type="text"
                        x-model="newComment"
                        @keydown.enter="submitComment()"
                        placeholder="Escreva um comentário..."
                        class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 transition">
                    <button
                        @click="submitComment()"
                        :disabled="!newComment.trim()"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold text-sm rounded-xl transition">
                        Enviar
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="comment in comments" :key="comment.id">
                        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-amber-400" x-text="comment.author_name || 'Anônimo'"></span>
                                <span class="text-slate-500" x-text="comment.created_at_human"></span>
                            </div>
                            <p class="text-slate-300 text-sm leading-relaxed" x-text="comment.content"></p>
                        </div>
                    </template>
                    <template x-if="comments.length === 0">
                        <p class="text-sm text-slate-500 text-center py-6 bg-slate-950/40 rounded-xl border border-dashed border-slate-800">Seja o primeiro a comentar!</p>
                    </template>
                </div>
            </div>

            <div x-show="activeTab === 'ratings'" class="space-y-5">
                <div class="p-5 bg-gradient-to-br from-slate-950 to-slate-900 rounded-2xl border border-amber-500/20 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg">
                    <div class="text-center sm:text-left">
                        <h4 class="text-sm font-bold text-slate-200">Deixe sua nota</h4>
                        <p class="text-xs text-slate-500">O que você achou dessa ideia?</p>
                    </div>
                    <div class="flex gap-2">
                        <template x-for="star in [1,2,3,4,5]" :key="star">
                            <button @click="rateIdea(activeIdea.id, star)" class="text-2xl hover:scale-125 transition-transform drop-shadow-md">⭐</button>
                        </template>
                    </div>
                </div>

                <hr class="border-slate-800">

                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Avaliações Recentes</h4>
                    
                    <template x-for="rating in ratings" :key="rating.id">
                        <div class="bg-slate-950/80 border border-slate-800 rounded-xl p-4 flex flex-col gap-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-slate-300" x-text="rating.author_name || 'Anônimo'"></span>
                                <span class="bg-slate-900 border border-slate-700 px-2 py-0.5 rounded-md text-amber-400 font-bold" x-text="'⭐ ' + rating.score"></span>
                            </div>
                            <template x-if="rating.feedback">
                                <p class="text-slate-400 text-sm mt-1" x-text="rating.feedback"></p>
                            </template>
                        </div>
                    </template>
                    <template x-if="ratings.length === 0">
                        <p class="text-sm text-slate-500 text-center py-6 bg-slate-950/40 rounded-xl border border-dashed border-slate-800">Nenhuma nota registrada ainda.</p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>