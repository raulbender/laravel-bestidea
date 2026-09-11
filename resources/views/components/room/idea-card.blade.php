<div class="bg-slate-900 border border-slate-800 hover:border-slate-700/80 rounded-2xl transition shadow-sm overflow-hidden">
    <div class="p-5 space-y-4">
        
        <!-- 1 & 2. TOPO: Autor/Tempo (Esquerda) e Badge Média (Direita) -->
        <div class="flex items-center justify-between gap-3 text-xs border-b border-slate-800/60 pb-3">
            <!-- 1. Esquerda: Avatar, Nome e Tempo -->
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-sm" x-text="idea.author_avatar || '👤'"></span>
                <span class="font-bold text-amber-400 truncate" x-text="idea.author_name || 'Anônimo'"></span>
                <span class="text-slate-600">•</span>
                <span class="text-slate-500 whitespace-nowrap" x-text="idea.created_at_human"></span>
            </div>

            <!-- 2. Direita: Badge da Nota Média e Quantidade de Avaliações [⭐2.50 (5)] -->
            <div class="flex items-center gap-1.5 bg-slate-950 border border-slate-800 text-slate-200 px-2.5 py-1 rounded-xl shrink-0 font-medium">
                <span class="text-amber-400">⭐</span>
                <span class="font-bold" x-text="idea.avg_score || idea.ratings_avg || '0.00'"></span>
                <span class="text-slate-500" x-text="`(${idea.ratings_count || 0})`"></span>
            </div>
        </div>

        <!-- 3. MEIO: Texto Completo da Ideia (sem truncate) -->
        <p class="text-slate-200 text-sm leading-relaxed break-words whitespace-pre-line" x-text="idea.content"></p>

        <!-- 4. EMBAIXO: Botões de Ação -->
        <div class="flex items-center gap-2 pt-3 border-t border-slate-800/60 text-xs">
            <!-- 4a. Botão [💬 Comentários (5)] - Abre Modal -->
            <button 
                @click="openIdeaComments(idea)"
                class="flex items-center gap-1.5 text-slate-300 bg-slate-950 border border-slate-800 hover:border-slate-700 hover:text-white px-3.5 py-2 rounded-xl transition font-medium">
                💬<span x-text="idea.comments_count || 0"></span>
            </button>

            <!-- 4b. Botão [⭐ Avaliar] - Expande Accordion Inline -->
            <button 
                @click.stop="toggleRating(idea.id)"
                :class="expandedRatingIdeaId === idea.id ? 'bg-amber-500/20 text-amber-400 border-amber-500/40' : 'bg-slate-950 border-slate-800 text-slate-300 hover:border-slate-700 hover:text-white'"
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border transition font-medium">
                <span class="text-amber-400">⭐</span>
                <span>Avaliar</span>
            </button>
        </div>
    </div>

    <!-- PRIMEIRA EXPANSÃO DO CARD: Form de Avaliação Inline -->
    <div 
        x-show="expandedRatingIdeaId === idea.id" 
        x-collapse 
        x-cloak
        class="bg-slate-950/90 border-t border-slate-800/80 p-4 space-y-3">

        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-300">Sua avaliação:</span>
            
            <!-- Estrelas -->
            <div class="flex gap-1">
                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                    <button 
                        @click="rateIdeaInline(idea, star)" 
                        :disabled="isRatingSubmitting"
                        class="text-xl hover:scale-125 transition-transform disabled:opacity-50">
                        ⭐
                    </button>
                </template>
            </div>
        </div>

        <!-- Comentário opcional atrelado à nota -->
        <template x-if="currentRatingId">
            <div class="space-y-2 pt-2 border-t border-slate-900 animate-fadeIn">
                <textarea
                    x-model="ratingComment"
                    rows="2"
                    placeholder="Quer adicionar um comentário explicando sua nota? (opcional)"
                    class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition resize-none"></textarea>

                <div class="flex justify-end gap-2">
                    <button 
                        @click="closeRatingInline()" 
                        class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition">
                        Concluir sem comentar
                    </button>
                    <button 
                        @click="submitRatingComment(idea)" 
                        :disabled="!ratingComment.trim()"
                        class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold text-xs rounded-lg transition">
                        Enviar Comentário
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>