<div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl space-y-5">
    <!-- Bloco 1: Título, Criador e Compartilhar -->
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-1.5 min-w-0">
            <h1
                class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white leading-snug break-words"
                x-text="roomData.description || '{{ __('app.room.loading') }}'"></h1>

            <div class="text-xs font-medium text-slate-400 truncate">
                {{ __('app.ideas.created_by') }} <span class="text-amber-400 font-semibold" x-text="roomOwner || '{{ __('app.idea.anonymous') }}'"></span>
            </div>
        </div>

        <button
            @click="copyLink()"
            class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl text-xs flex items-center gap-2 transition shrink-0 mt-1">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            <span class="hidden sm:inline">{{ __('app.share') }}</span>
        </button>
    </div>

    <!-- Bloco 2: Badges de Estado da Sala -->
    <div class="flex items-center gap-2 flex-wrap pt-1">
        <template x-if="!roomData.expires_at">
            <x-badge variant="cyan" icon="♾️">
                {{ __('app.ideas.no_expires') }}
            </x-badge>
        </template>

        <template x-if="roomData.expires_at && roomData.is_expiring_soon">
            <x-badge variant="amber" icon="⏳" :pulse="true">
                <span x-text="'{{ __('app.ideas.expires_in') }} ' + roomData.expires_at_human"></span>
            </x-badge>
        </template>

        <template x-if="roomData.expires_at && !roomData.is_expiring_soon">
            <x-badge variant="slate" icon="⏳">
                <span x-text="'{{ __('app.ideas.expires_in') }} ' + roomData.expires_at_human"></span>
            </x-badge>
        </template>

        <template x-if="roomData.is_public">
            <x-badge variant="emerald" icon="🌐">
                {{ __('app.privacy.public') }}
            </x-badge>
        </template>

        <template x-if="!roomData.is_public">
            <x-badge variant="amber" icon="🔒">
                {{ __('app.privacy.unlisted') }}
            </x-badge>
        </template>
    </div>

    <!-- Bloco 3: Rodapé do Card (Persona + Envio de Ideias) -->
    <div class="pt-4 border-t border-slate-800/80 space-y-3">
        <!-- Badge da Persona no Rodapé (Acima do aviso de anonimato) -->
        <template x-if="myPersona">
            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                <span> {{ __('app.room.my_persona') }} </span>
                <span class="font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md flex items-center gap-1">
                    <span x-text="myPersona.avatar"></span>
                    <span x-text="myPersona.name"></span>
                </span>
            </div>
        </template>

        <!-- Estado Fechado: CTA Enviar Ideia -->
        <div x-show="!showForm" class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="min-w-0">
                    <h3 class="hidden sm:inline text-sm font-bold text-white truncate">{{ __('app.ideas.add_title') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('app.ideas.anonymous') }}</p>
                </div>
            </div>

            <button
                @click="openForm()"
                class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>{{ __('app.ideas.submit') }}</span>
            </button>
        </div>

        <!-- Estado Expandido: Formulário de Envio -->
        <div x-show="showForm" x-cloak class="space-y-3 pt-1">
            <textarea
                x-ref="ideaTextarea"
                x-model="newIdeaContent"
                maxlength="1000"
                rows="3"
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3.5 text-white placeholder-slate-500 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition resize-none text-sm"
                placeholder="{{ __('app.ideas.placeholder') }}"></textarea>
            
            <div class="flex justify-end"><span class="text-xs text-slate-500" x-text="`${newIdeaContent.length}/1000`"></span></div>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button @click="showForm = false" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition">
                    {{ __('Cancel') }}
                </button>
                <button
                    @click="submitIdea()"
                    :disabled="submitting || newIdeaContent.trim().length < 5"
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition disabled:opacity-50">
                    <span x-show="!submitting">{{ __('app.ideas.submit') }}</span>
                    <span x-show="submitting" x-cloak>{{ __('app.ideas.submitting') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>