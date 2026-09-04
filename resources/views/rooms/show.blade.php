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

    {{-- HEADER PRINCIPAL DA SALA --}}
    <header class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        
        {{-- Topo do Header: Timer + Botão Compartilhar --}}
        <div class="flex items-center justify-between gap-4 mb-6">
            
            {{-- Timer de Expiração --}}
            <div class="flex items-center gap-2 bg-slate-950/80 border border-slate-800/80 text-slate-400 font-mono text-xs px-3.5 py-1.5 rounded-full">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Expira em:</span>
                <span class="text-amber-400 font-bold" x-text="timeRemaining">--:--:--</span>
            </div>

            {{-- Botão Compartilhar --}}
            <button @click="copyLink()" class="px-4 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl text-xs flex items-center gap-2 transition">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                <span>Compartilhar</span>
            </button>
        </div>

        {{-- Pergunta / Tema da Sala --}}
        <h1 class="text-xl sm:text-3xl font-extrabold text-white leading-snug" x-text="room.description || 'Carregando sala...'"></h1>
    </header>

    {{-- ÁREA DE CONTRIBUIÇÃO --}}
    <section class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-lg">
        
        {{-- Chamada Principal --}}
        <div x-show="!showForm" class="flex items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-white">Adicionar uma ideia</h3>
                <p class="text-xs text-slate-400">Sua participação é anônima.</p>
            </div>

            <button 
                @click="openForm()" 
                class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2 whitespace-nowrap"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Nova Ideia</span>
            </button>
        </div>

        {{-- Formulário Expandido --}}
        <div x-show="showForm" x-cloak class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-300">Escreva sua sugestão</span>
                <span class="text-xs text-slate-500" x-text="`${newIdeaContent.length}/1000`"></span>
            </div>

            <textarea 
                x-ref="ideaTextarea"
                x-model="newIdeaContent"
                maxlength="1000"
                rows="3" 
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3.5 text-white placeholder-slate-500 focus:ring-1 focus:ring-amber-500 focus:border-amber-500 focus:outline-none transition resize-none text-sm"
                placeholder="Descreva sua ideia..."
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
                    <span x-show="!submitting">Enviar</span>
                    <span x-show="submitting" x-cloak>Enviando...</span>
                </button>
            </div>
        </div>
    </section>

    {{-- CABEÇALHO DO MURAL & FILTROS DA API --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span>Mural de Ideias</span>
            <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-full" x-text="ideas.length">0</span>
        </h2>

        {{-- Abas de Ordenação e Filtro da API --}}
        <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs overflow-x-auto">
            <button 
                @click="setSort('recent')" 
                :class="sortBy === 'recent' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap"
            >
                Mais Recentes
            </button>

            <button 
                @click="setSort('hot')" 
                :class="sortBy === 'hot' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap"
            >
                🔥 Em Alta
            </button>

            <button 
                @click="setSort('top_rated')" 
                :class="sortBy === 'top_rated' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap"
            >
                ⭐ Mais Votadas
            </button>

            @auth
            <button 
                @click="toggleMine()" 
                :class="filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap border-l border-slate-800 ml-1"
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
                <div class="animate-pulse bg-slate-900/60 border border-slate-800 h-32 rounded-2xl p-5"></div>
            </template>
        </template>

        <template x-if="!loading && ideas.length === 0">
            <div class="col-span-full text-center py-12 bg-slate-900/40 rounded-2xl border border-dashed border-slate-800">
                <p class="text-xs text-slate-500">Nenhuma ideia encontrada para este filtro.</p>
            </div>
        </template>

        <template x-for="idea in ideas" :key="idea.id">
            <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-5 flex flex-col justify-between space-y-4">
                
                <p class="text-slate-200 text-sm leading-relaxed" x-text="idea.content"></p>

                <div class="flex items-center justify-between pt-3 border-t border-slate-800/60 text-xs">
                    <span class="text-slate-500" x-text="idea.created_at_human || 'Recente'"></span>

                    <button 
                        @click="rateIdea(idea.id, 1)" 
                        class="flex items-center gap-1.5 bg-slate-950 border border-slate-800 hover:border-amber-500/50 px-2.5 py-1 rounded-lg text-slate-300 transition"
                    >
                        <span>⭐</span>
                        <span class="font-bold text-xs" x-text="idea.avg_score || idea.ratings_avg || 0"></span>
                    </button>
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
        room: {},
        ideas: [],
        loading: false,
        showForm: false,
        submitting: false,
        newIdeaContent: '',
        sortBy: 'recent',
        filterMine: false,
        timeRemaining: '--:--:--',
        roomUrl: window.location.href,
        toast: { show: false, message: '', icon: '✅' },

        init() {
            this.fetchRoomDetails();
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
                this.room = json.data || json;
                this.roomId = this.room.id || this.roomId;
                this.startCountdown(this.room.expires_at);
            } catch (error) {
                console.error('Erro ao buscar dados da sala:', error);
            }
        },

        async fetchIdeas() {
            this.loading = true;
            try {
                let url = `/api/ideas?sort=${this.sortBy}`;
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
        },

        startCountdown(expireAt) {
            if (!expireAt) return;
            const expireTime = new Date(expireAt.replace(' ', 'T')).getTime();

            const update = () => {
                const now = new Date().getTime();
                const diff = expireTime - now;

                if (diff <= 0) {
                    this.timeRemaining = 'Expirada';
                    return;
                }

                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                this.timeRemaining = `${hours}h ${minutes}m ${seconds}s`;
            };

            update();
            setInterval(update, 1000);
        }
    }
}
</script>
@endsection