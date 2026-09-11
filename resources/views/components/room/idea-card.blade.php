<div class="bg-slate-900 border border-slate-800 hover:border-slate-700/80 rounded-2xl transition shadow-sm overflow-hidden">
    <!-- Padding responsivo: p-4 no mobile, p-5 em telas sm+ -->
    <div class="p-3 sm:p-5 space-y-4">

        <!-- 1 & 2. TOPO: Autor/Tempo (Esquerda) e Badge Média (Direita) -->
        <div class="flex items-center justify-between gap-3 border-b border-slate-800/60 pb-3">
            <!-- 1. Esquerda: Avatar Proporcional, Nome (Acima) e Tempo (Abaixo) -->
            <div class="flex items-center gap-3 min-w-0">
                <!-- Avatar w-10 h-10 (40px) com emoji text-lg e fundo em destaque -->
                <div class="w-10 h-10 rounded-full bg-slate-950 border border-slate-800 flex items-center justify-center shrink-0 shadow-inner">
                    <span class="text-lg leading-none" x-text="idea.author_avatar || '👤'"></span>
                </div>

                <!-- Nome e Tempo empilhados -->
                <div class="flex flex-col min-w-0">
                    <span class="font-bold text-amber-400 text-xs sm:text-sm truncate leading-tight" x-text="idea.author_name"></span>
                    <span class="text-[11px] text-slate-500 truncate leading-tight mt-0.5" x-text="idea.created_at_human"></span>
                </div>
            </div>

            <!-- 2. Direita: Badge da Nota Média -->
            <div class="flex items-center gap-1.5 bg-slate-950 border border-slate-800 text-slate-200 px-2.5 py-1 rounded-xl shrink-0 text-xs font-medium">
                <span class="text-amber-400">⭐</span>
                <span class="font-bold" x-text="idea.avg_score || idea.ratings_avg || '0.00'"></span>
                <span class="text-slate-500" x-text="`(${idea.ratings_count || 0})`"></span>
            </div>
        </div>

        <!-- 3. MEIO: Texto Completo da Ideia -->
        <p class="text-slate-200 text-sm leading-relaxed break-words whitespace-pre-line" x-text="idea.content"></p>

        <!-- 4. EMBAIXO: Botões de Ação -->
        <div class="flex items-center gap-2 pt-3 border-t border-slate-800/60 text-xs">
            <!-- 4a. Botão Comentários -->
            <button
                @click="openIdeaComments(idea)"
                :aria-label="'{{ __('app.idea-card.look') }} ' + (idea.comments_count || 0) + ' {{ __('app.idea-card.comments') }}'"
                class="flex items-center gap-1.5 text-slate-300 bg-slate-950 border border-slate-800 hover:border-slate-700 hover:text-white px-3.5 py-2 rounded-xl transition font-medium">
                💬<span x-text="idea.comments_count || 0"></span>
            </button>

            <!-- 4b. Botão Avaliar / Nota -->
            <button
                @click.stop="toggleRating(idea)"
                :class="{
                    'bg-amber-500/10 border-amber-500/40 text-amber-300 font-semibold hover:bg-amber-500/20': !idea.my_rating && expandedRatingIdeaId !== idea.id,
                    'bg-slate-950 border-slate-800 text-amber-400 font-medium hover:border-slate-700': idea.my_rating && expandedRatingIdeaId !== idea.id,
                    'bg-slate-900 border-amber-500 text-amber-300 font-semibold': expandedRatingIdeaId === idea.id
                }"
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl border transition text-xs">

                <span class="text-amber-400">⭐</span>
                <span x-text="idea.my_rating ? `{{ __('app.idea-card.your_rating') }}: ${idea.my_rating}` : '{{ __('app.idea-card.evaluate') }}'"></span>
            </button>
        </div>
    </div>

    <!-- EXPANSÃO DO CARD: Avaliação e Comentário Inline -->
    <div
        x-show="expandedRatingIdeaId === idea.id"
        x-collapse
        x-cloak
        class="bg-slate-950/90 border-t border-slate-800/80 p-4 space-y-3">

        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-300"
                x-text="idea.my_rating ? '{{ __('app.idea-card.change_rating') }}:' : '{{ __('app.idea-card.select_rating') }}:'"></span>

            <div class="flex gap-1">
                <template x-if="idea.my_rating">
                    <button
                        @click="removeRatingInline(idea)"
                        :disabled="isRatingSubmitting"
                        class="text-[11px] text-rose-500 hover:text-rose-300 transition ml-2 space-x-5">
                        {{ __('app.idea-card.remove_rating') }}
                    </button>
                </template>
                <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                    <button
                        @click="rateIdeaInline(idea, star)"
                        :disabled="isRatingSubmitting"
                        :aria-label="'{{ __('app.idea-card.evaluate') }} ' + star"
                        class="text-xl hover:scale-125 transition-transform disabled:opacity-50 focus:outline-none">
                        <span x-text="star <= (selectedScore || userScore || 0) ? '⭐' : '☆'"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- SEGUNDA ETAPA: Comentário Opcional -->
        <template x-if="currentRatingId || selectedScore">
            <div class="space-y-2 pt-3 border-t border-slate-800/80 animate-fadeIn">
                <textarea
                    x-model="ratingComment"
                    rows="2"
                    placeholder="{{ __('app.idea-card.comment_placeholder') }}"
                    class="w-full bg-slate-900 border border-slate-800 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 transition resize-none"></textarea>

                <div class="flex justify-end gap-2">
                    <button
                        @click="closeRatingInline()"
                        class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition">
                        {{ __('app.idea-card.cancel_comment') }}
                    </button>
                    <button
                        @click="submitRatingComment(idea)"
                        :disabled="!ratingComment.trim()"
                        class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold text-xs rounded-lg transition">
                        {{ __('app.idea-card.submit_comment') }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>