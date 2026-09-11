<div
    x-show="activeIdea !== null"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    @keydown.escape.window="closeIdeaComments()">

    <div
        @click.outside="closeIdeaComments()"
        class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">

        <!-- Cabeçalho do Modal -->
        <div class="p-5 border-b border-slate-800 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-lg" x-text="activeIdea?.author_name || 'Anônimo'"></span>
                    <span class="text-xs text-slate-500" x-text="activeIdea?.created_at_human"></span>
                </div>
                
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-950 border border-slate-800 px-3 py-1 rounded-xl text-xs font-bold">
                        <span class="text-amber-400">⭐</span>
                        <span class="text-slate-200" x-text="activeIdea?.avg_score || '0.00'"></span>
                    </div>
                    <button @click="closeIdeaComments()" class="text-slate-400 hover:text-white p-1.5 rounded-xl bg-slate-950 border border-slate-800 transition">✕</button>
                </div>
            </div>
            
            <p class="text-slate-100 font-medium text-base leading-relaxed break-words" x-text="activeIdea?.content"></p>
        </div>

        <!-- Área Central de Comentários / Discussão -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">
            <div class="space-y-3">
                <template x-for="comment in comments" :key="comment.id">
                    <div class="bg-slate-950/80 border border-slate-800/80 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-bold text-amber-400" x-text="comment.author_name || 'Anônimo'"></span>
                            <span class="text-slate-500" x-text="comment.created_at_human"></span>
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed break-words" x-text="comment.content"></p>
                    </div>
                </template>

                <template x-if="comments.length === 0">
                    <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-dashed border-slate-800">
                        <p class="text-sm text-slate-500">Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Rodapé Fixo para Escrever Novo Comentário -->
        <div class="p-4 bg-slate-950 border-t border-slate-800">
            <div class="flex gap-2">
                <input
                    type="text"
                    x-model="newComment"
                    @keydown.enter="submitComment()"
                    placeholder="Escreva um comentário no debate..."
                    class="flex-1 bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white focus:outline-none focus:border-amber-500 transition">
                <button
                    @click="submitComment()"
                    :disabled="!newComment.trim()"
                    class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold text-sm rounded-xl transition">
                    Enviar
                </button>
            </div>
        </div>
    </div>
</div>