@extends('layouts.app')

@section('content')
<div x-data="roomBoard('{{ $uuid }}', {{ $room->id ?? 'null' }})" class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    {{-- NOTIFICAÇÃO TOAST --}}
    <div 
        x-show="toast.show" 
        x-transition:enter="transition ease-out duration-200 transform"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150 transform"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-cloak
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900 border border-slate-700 text-slate-100 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium"
    >
        <span x-text="toast.icon"></span>
        <span x-text="toast.message"></span>
    </div>

    {{-- CARD PRINCIPAL DA SALA --}}
    <header class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl flex flex-col justify-between">
        
        {{-- Conteúdo do Card: Tema/Descrição --}}
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white leading-snug break-words" x-text="roomData.description || 'Carregando sala...'"></h1>
        </div>

        {{-- Rodapé do Card da Sala --}}
        <div class="pt-4 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
            
            {{-- Esquerda: Criador + Expiração Discreta (Padrão Home) --}}
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-medium text-slate-300 truncate">
                    Criado por <span class="text-amber-400 font-semibold" x-text="roomOwner || 'Anônimo'"></span>
                </span>
                <span class="text-[11px] text-slate-500 truncate" x-text="roomData.expires_at_human ? 'Expira em ' + roomData.expires_at_human : 'Sem expiração'"></span>
            </div>

            {{-- Direita: Ações (Botão Compartilhar) --}}
            <button @click="copyLink()" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl text-xs flex items-center gap-2 transition shrink-0">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                <span>Compartilhar</span>
            </button>
        </div>
    </header>

    {{-- ÁREA DE CONTRIBUIÇÃO --}}
    <section class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
        
        {{-- Chamada Principal --}}
        <div x-show="!showForm" class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                {{-- Persona / Identidade Anônima no contexto de Ação --}}
                <template x-if="myPersona">
                    <div class="flex items-center gap-1.5 bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs px-3 py-1.5 rounded-xl font-semibold shrink-0">
                        <span x-text="myPersona.avatar"></span>
                        <span class="hidden sm:inline" x-text="myPersona.name"></span>
                    </div>
                </template>

                <div>
                    <h3 class="text-sm font-bold text-white">Adicionar uma ideia</h3>
                    <p class="text-xs text-slate-400">Sua participação é 100% anônima nesta sala.</p>
                </div>
            </div>

            <button 
                @click="openForm()" 
                class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 shrink-0"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Nova Ideia</span>
            </button>
        </div>

        {{-- Formulário Expandido --}}
        <div x-show="showForm" x-cloak class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-300">Escreva sua sugestão como</span>
                    <template x-if="myPersona">
                        <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md">
                            <span x-text="myPersona.avatar"></span> <span x-text="myPersona.name"></span>
                        </span>
                    </template>
                </div>
                <span class="text-xs text-slate-500" x-text="`${newIdeaContent.length}/1000`"></span>
            </div>

            <textarea 
                x-ref="ideaTextarea"
                x-model="newIdeaContent"
                maxlength="1000"
                rows="3" 
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3.5 text-white placeholder-slate-500 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition resize-none text-sm"
                placeholder="Descreva sua ideia sem preocupação com julgamentos..."
            ></textarea>

            <div class="flex items-center justify-end gap-3 pt-1">
                <button @click="showForm = false" class="px-3 py-1.5 text-xs text-slate-400 hover:text-white transition">
                    Cancelar
                </button>
                <button 
                    @click="submitIdea()" 
                    :disabled="submitting || newIdeaContent.trim().length < 5" 
                    class="px-5 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-lg text-xs transition disabled:opacity-50"
                >
                    <span x-show="!submitting">Enviar Anônimamente</span>
                    <span x-show="submitting" x-cloak>Enviando...</span>
                </button>
            </div>
        </div>
    </section>

    {{-- CABEÇALHO DO MURAL & FILTROS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span>Mural de Ideias</span>
            <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-full" x-text="ideas.length">0</span>
        </h2>

        {{-- Abas de Ordenação com Scroll Horizontal --}}
        <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs overflow-x-auto no-scrollbar max-w-full">
            <button 
                @click="setSort('recent')" 
                :class="sortBy === 'recent' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0"
            >
                Mais Recentes
            </button>

            <button 
                @click="setSort('hot')" 
                :class="sortBy === 'hot' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0"
            >
                🔥 Em Alta
            </button>

            <button 
                @click="setSort('top_rated')" 
                :class="sortBy === 'top_rated' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0"
            >
                ⭐ Mais Votadas
            </button>

            @auth
            <button 
                @click="toggleMine()" 
                :class="filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 border-l border-slate-800 ml-1"
            >
                Minhas Ideias
            </button>
            @endauth
        </div>
    </div>

    {{-- FEED DE IDEIAS --}}
    <section class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <template x-if="loading && ideas.length === 0">
            <template x-for="i in 4" :key="i">
                <div class="animate-pulse bg-slate-900/60 border border-slate-800 h-36 rounded-2xl p-5"></div>
            </template>
        </template>

        <template x-if="!loading && ideas.length === 0">
            <div class="col-span-full text-center py-12 bg-slate-900/40 rounded-2xl border border-dashed border-slate-800">
                <p class="text-xs text-slate-500">Nenhuma ideia encontrada para esta sala.</p>
            </div>
        </template>

        <template x-for="idea in ideas" :key="idea.id">
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between space-y-4 hover:border-slate-700 transition duration-150">
                
                {{-- Conteúdo da Ideia --}}
                <p class="text-slate-200 text-sm leading-relaxed break-words" x-text="idea.content"></p>

                {{-- Rodapé do Card da Ideia --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-800/60 text-xs gap-2 min-w-0">
                    <span class="text-slate-500 truncate" x-text="idea.created_at_human || 'Recente'"></span>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="flex items-center gap-1 text-slate-400 bg-slate-950/60 border border-slate-800 px-2 py-1 rounded-lg text-xs">
                            💬 <span x-text="idea.comments_count || 0"></span>
                        </span>

                        <button 
                            @click="rateIdea(idea.id, 5)" 
                            class="flex items-center gap-1.5 bg-slate-950 border border-slate-800 hover:border-amber-500/50 px-2.5 py-1 rounded-lg text-slate-300 transition"
                        >
                            <span class="text-amber-400">⭐</span>
                            <span class="font-bold text-xs" x-text="idea.avg_score || idea.ratings_avg || '0.00'"></span>
                        </button>
                    </div>
                </div>

            </div>
        </template>

    </section>

</div>

<script>
function roomBoard(roomUuid, roomId = null) {
    return {
        uuid: roomUuid,
        roomId: roomId,
        roomData: {},
        roomOwner: '',
        myPersona: null,
        ideas: [],
        loading: false,
        showForm: false,
        submitting: false,
        newIdeaContent: '',
        sortBy: 'recent',
        filterMine: false,
        roomUrl: window.location.href,
        toast: { show: false, message: '', icon: '✅' },

        async init() {
            await this.fetchRoomDetails();
            this.fetchIdeas();
        },

        showToast(msg, icon = '✅') {
            this.toast.message = msg;
            this.toast.icon = icon;
            this.toast.show = true;
            setTimeout(() => this.toast.show = false, 3000);
        },

        openForm() {
            this.showForm = true;
            this.$nextTick(() => {
                this.$refs.ideaTextarea.focus();
            });
        },

        copyLink() {
            navigator.clipboard.writeText(this.roomUrl);
            this.showToast('Link copiado!', '📋');
        },

        setSort(type) {
            this.filterMine = false;
            this.sortBy = type;
            this.fetchIdeas();
        },

        toggleMine() {
            this.filterMine = !this.filterMine;
            this.fetchIdeas();
        },

        async fetchRoomDetails() {
            try {
                const response = await fetch(`/api/rooms/${this.uuid}`);
                const json = await response.json();
                
                // Mapeia corretamente o payload do RoomResource
                const payload = json.data || json;
                this.roomData = payload.room || payload;
                this.roomOwner = payload.owner_name || 'Anônimo';
                this.myPersona = payload.my_persona || null;
                
                // Garante que o roomId real seja utilizado nas consultas de ideias
                this.roomId = this.roomData.id || this.roomId;
            } catch (error) {
                console.error('Erro ao buscar dados da sala:', error);
            }
        },

        async fetchIdeas() {
            this.loading = true;
            try {
                let url = `/api/ideas?sort=${this.sortBy}`;
                
                // Passa o room_id garantido para não trazer ideias de outras salas
                if (this.roomId) url += `&room_id=${this.roomId}`;
                if (this.filterMine) url += `&filter=mine`;

                const response = await fetch(url);
                const json = await response.json();
                this.ideas = json.data || json;
            } catch (error) {
                console.error('Erro ao buscar ideias:', error);
            } finally {
                this.loading = false;
            }
        },

        async submitIdea() {
            if (this.newIdeaContent.trim().length < 5 || this.submitting) return;

            this.submitting = true;
            try {
                const response = await fetch(`/api/rooms/${this.uuid}/ideas`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ content: this.newIdeaContent })
                });

                if (response.ok) {
                    this.newIdeaContent = '';
                    this.showForm = false;
                    this.showToast('Ideia publicada!', '🎉');
                    this.fetchIdeas();
                }
            } catch (error) {
                this.showToast('Erro ao enviar ideia.', '❌');
            } finally {
                this.submitting = false;
            }
        },

        async rateIdea(ideaId, score) {
            try {
                await fetch(`/api/ideas/${ideaId}/ratings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ score: score })
                });
                this.showToast('Voto registrado!', '⭐');
                this.fetchIdeas();
            } catch (error) {
                console.error('Erro ao votar:', error);
            }
        }
    }
}
</script>
@endsection