<div
    x-show="activeIdea !== null"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
    @keydown.escape.window="closeIdeaComments()">

    <div
        @click.outside="closeIdeaComments()"
        class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden">

        <!-- Cabeçalho do Modal -->
        <div class="p-4 sm:p-5 border-b border-slate-800 space-y-3">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-full bg-slate-950 border border-slate-800 flex items-center justify-center shrink-0 shadow-inner">
                        <span class="text-lg leading-none" x-text="activeIdea?.author_avatar || '👤'"></span>
                    </div>

                    <div class="flex flex-col min-w-0">
                        <span class="font-bold text-amber-400 text-xs sm:text-sm truncate leading-tight" x-text="activeIdea?.author_name || 'Anônimo'"></span>
                        <span class="text-[11px] text-slate-500 truncate leading-tight mt-0.5" x-text="activeIdea?.created_at_human"></span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-950 border border-slate-800 px-2.5 sm:px-3 py-1 rounded-xl text-xs font-bold">
                        <span class="text-amber-400">⭐</span>
                        <span class="text-slate-200" x-text="activeIdea?.avg_score || '0.00'"></span>
                    </div>
                    <button @click="closeIdeaComments()" class="text-slate-400 hover:text-white p-1.5 rounded-xl bg-slate-950 border border-slate-800 transition">✕</button>
                </div>
            </div>

            <p class="text-slate-100 font-medium text-sm sm:text-base leading-relaxed break-words pt-1" x-text="activeIdea?.content"></p>
        </div>

        <!-- Área Central de Comentários / Discussão -->
        <div class="p-4 sm:p-5 overflow-y-auto flex-1 space-y-4">
            <div class="space-y-3">
                <template x-for="comment in comments" :key="comment.id">
                    <div class="bg-slate-950/80 border border-slate-800/80 rounded-xl p-4 space-y-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0 shadow-inner">
                                <span class="text-base sm:text-lg leading-none" x-text="comment.author_avatar || '👤'"></span>
                            </div>

                            <div class="flex flex-col min-w-0">
                                <span class="font-bold text-amber-400 text-xs sm:text-sm truncate leading-tight" x-text="comment.author_name || 'Anônimo'"></span>
                                <span class="text-[11px] text-slate-500 truncate leading-tight mt-0.5" x-text="comment.created_at_human"></span>
                            </div>
                        </div>

                        <p class="text-slate-300 text-sm leading-relaxed break-words whitespace-pre-line pl-1" x-text="comment.content"></p>
                    </div>
                </template>

                <template x-if="comments.length === 0">
                    <div class="text-center py-10 bg-slate-950/40 rounded-xl border border-dashed border-slate-800">
                        <p class="text-sm text-slate-500">Nenhum comentário ainda. Seja o primeiro a comentar!</p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Rodapé Estilo WhatsApp com Auto-resize e Botão Responsivo -->
        <div class="p-3 sm:p-4 bg-slate-950 border-t border-slate-800 space-y-2">
            <template x-if="myPersona">
                <div class="flex items-center gap-1.5 text-xs text-slate-400 px-1">
                    <span>{{ __('app.room.my_persona') }}</span>
                    <span class="font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <span x-text="myPersona.avatar"></span>
                        <span x-text="myPersona.name"></span>
                    </span>
                </div>
            </template>

            <div class="flex items-end gap-2">
                <textarea
                    x-ref="commentTextarea"
                    x-model="newComment"
                    rows="1"
                    @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                    @keydown.enter.exact.prevent="submitComment(); $nextTick(() => { $el.style.height = 'auto' })"
                    placeholder="{{ __('app.modal-comment.placeholder') ?? 'Escreva um comentário...' }}"
                    class="flex-1 bg-slate-900 border border-slate-800 rounded-2xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition resize-none max-h-36 min-h-[42px] leading-relaxed overflow-y-auto"></textarea>

                <button
                    @click="submitComment(); $nextTick(() => { if($refs.commentTextarea) $refs.commentTextarea.style.height = 'auto' })"
                    :disabled="!newComment.trim()"
                    aria-label="Enviar comentário"
                    class="h-10 w-10 sm:w-auto px-0 sm:px-4 bg-amber-500 hover:bg-amber-400 disabled:opacity-40 text-slate-950 font-bold text-sm rounded-full sm:rounded-xl flex items-center justify-center gap-2 shrink-0 transition shadow-md">
                    <span class="hidden sm:inline">{{ __('app.idea-card.submit_comment') }}</span>
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>