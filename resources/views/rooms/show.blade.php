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
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900 border border-slate-700 text-slate-100 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium">
        <span x-text="toast.icon"></span>
        <span x-text="toast.message"></span>
    </div>

    {{-- HUB PRINCIPAL DA SALA & ÁREA DE CONTRIBUIÇÃO --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 sm:p-7 shadow-xl space-y-6">

        {{-- 1. TOPO: Título / Tema Principal + Botão Compartilhar --}}
        <div class="flex items-start justify-between gap-4">
            <h1
                class="text-xl sm:text-2xl md:text-3xl font-extrabold text-white leading-snug break-words"
                x-text="roomData.description || '{{ __('app.room.loading') }}'"></h1>

            <button
                @click="copyLink()"
                class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold rounded-xl text-xs flex items-center gap-2 transition shrink-0 mt-1">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                </svg>
                <span class="hidden sm:inline">{{ __('app.share') }}</span>
            </button>
        </div>

        {{-- 2. MEIO: Badges de Estado & Criador --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Badges de Expiração --}}
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

                {{-- Badges de Privacidade --}}
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

            <span class="text-xs font-medium text-slate-400 truncate">
                {{ __('app.ideas.created_by') }} <span class="text-amber-400 font-semibold" x-text="roomOwner || '{{ __('app.idea.anonymous') }}'"></span>
            </span>
        </div>

        {{-- 3. BASE: Divisor Suave + Área de Ação/Formulário --}}
        <div class="pt-5 border-t border-slate-800/80">

            {{-- Chamada Fechada --}}
            <div x-show="!showForm" class="flex items-center justify-end gap-4">
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

            {{-- Formulário Expandido --}}
            <div x-show="showForm" x-cloak class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-300">{{ __('app.idea.write_as') }}</span>
                        <template x-if="myPersona">
                            <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 rounded-md">
                                <span x-text="myPersona.avatar"></span> <span x-text="myPersona.name"></span>
                            </span>
                        </template>
                    </div>
                </div>

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

    {{-- CABEÇALHO DO MURAL & FILTROS --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
        <h2 class="text-base font-bold text-white flex items-center gap-2">
            <span>{{ __('app.ideas.title') }}</span>
            <span class="text-xs font-mono bg-slate-800 text-slate-300 px-2.5 py-0.5 rounded-full" x-text="ideas.length">0</span>
        </h2>

        {{-- Abas de Ordenação com Scroll Horizontal --}}
        <div class="flex items-center bg-slate-900 border border-slate-800 rounded-xl p-1 text-xs overflow-x-auto no-scrollbar max-w-full">
            <button
                @click="setSort('recent')"
                :class="sortBy === 'recent' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
                {{ __('app.sort.recent') }}
            </button>

            <button
                @click="setSort('hot')"
                :class="sortBy === 'hot' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
                {{ __('app.sort.hot') }}
            </button>

            <button
                @click="setSort('top_rated')"
                :class="sortBy === 'top_rated' && !filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0">
                {{ __('app.sort.top_rated') }}
            </button>

            @auth
            <button
                @click="toggleMine()"
                :class="filterMine ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'"
                class="px-3 py-1.5 rounded-lg transition whitespace-nowrap shrink-0 border-l border-slate-800 ml-1">
                {{ __('app.sort.mine') }}
            </button>
            @endauth
        </div>
    </div>

    {{-- FEED DE IDEIAS (COLUNA ÚNICA CENTRALIZADA) --}}
    <section class="max-w-3xl mx-auto space-y-4">
        <template x-if="loading && ideas.length === 0">
            <template x-for="i in 3" :key="i">
                <div class="animate-pulse bg-slate-900/60 border border-slate-800 h-28 rounded-2xl p-5"></div>
            </template>
        </template>

        <template x-if="!loading && ideas.length === 0">
            <div class="text-center py-12 bg-slate-900/40 rounded-2xl border border-dashed border-slate-800">
                <p class="text-xs text-slate-500">{{ __('app.ideas.empty') }}</p>
            </div>
        </template>

        {{-- Cards das ideias --}}
        <template x-for="idea in ideas" :key="idea.id">
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
        </template>
    </section>

    {{-- MODAL DE DETALHES DA IDEIA (COMENTÁRIOS + AVALIAÇÕES) --}}
    <div
        x-show="activeIdea !== null"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
        @keydown.escape.window="closeIdeaDetails()">

        <div
            @click.outside="closeIdeaDetails()"
            class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">

            {{-- CABEÇALHO DO MODAL --}}
            <div class="p-5 border-b border-slate-800 flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-amber-400" x-text="activeIdea?.created_at_human"></span>
                    <p class="text-slate-100 font-medium text-base leading-relaxed break-words" x-text="activeIdea?.content"></p>
                </div>
                <button @click="closeIdeaDetails()" class="text-slate-400 hover:text-white p-1 rounded-lg">✕</button>
            </div>

            {{-- ABAS DE NAVEGAÇÃO --}}
            <div class="flex border-b border-slate-800 bg-slate-950/40 px-5 pt-3 gap-6 text-xs font-bold">
                <button
                    @click="activeTab = 'comments'"
                    :class="activeTab === 'comments' ? 'text-amber-400 border-b-2 border-amber-400 pb-2' : 'text-slate-400 pb-2'">
                    Comentários (<span x-text="activeIdea?.comments_count || 0"></span>)
                </button>
                <button
                    @click="activeTab = 'ratings'"
                    :class="activeTab === 'ratings' ? 'text-amber-400 border-b-2 border-amber-400 pb-2' : 'text-slate-400 pb-2'">
                    Avaliações (<span x-text="activeIdea?.ratings_count || 0"></span>)
                </button>
            </div>

            {{-- CORPO DO MODAL --}}
            <div class="p-5 overflow-y-auto flex-1 space-y-4">

                {{-- ABA 1: COMENTÁRIOS --}}
                <div x-show="activeTab === 'comments'" class="space-y-4">
                    <div class="flex gap-2">
                        <input
                            type="text"
                            x-model="newComment"
                            @keydown.enter="submitComment()"
                            placeholder="Escreva um comentário..."
                            class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-amber-500">
                        <button
                            @click="submitComment()"
                            :disabled="!newComment.trim()"
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-bold text-xs rounded-xl">
                            Enviar
                        </button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="comment in comments" :key="comment.id">
                            <div class="bg-slate-950/60 border border-slate-800/60 rounded-xl p-3 text-xs space-y-1">
                                <div class="flex justify-between text-slate-500">
                                    <span class="font-bold text-slate-300" x-text="comment.author_name || 'Anônimo'"></span>
                                    <span x-text="comment.created_at_human"></span>
                                </div>
                                <p class="text-slate-300" x-text="comment.content"></p>
                            </div>
                        </template>
                        <template x-if="comments.length === 0">
                            <p class="text-xs text-slate-500 text-center py-4">Nenhum comentário ainda.</p>
                        </template>
                    </div>
                </div>

                {{-- ABA 2: AVALIAÇÕES --}}
                <div x-show="activeTab === 'ratings'" class="space-y-4">
                    <div class="p-3 bg-slate-950/60 rounded-xl border border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-300">Sua avaliação:</span>
                        <div class="flex gap-1">
                            <template x-for="star in [1,2,3,4,5]" :key="star">
                                <button @click="rateIdea(activeIdea.id, star)" class="text-lg hover:scale-110 transition">⭐</button>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <template x-for="rating in ratings" :key="rating.id">
                            <div class="bg-slate-950/60 border border-slate-800/60 rounded-xl p-3 text-xs flex justify-between items-center">
                                <span class="text-slate-300" x-text="rating.author_name || 'Anônimo'"></span>
                                <span class="text-amber-400 font-bold" x-text="'⭐ ' + rating.score"></span>
                            </div>
                        </template>
                        <template x-if="ratings.length === 0">
                            <p class="text-xs text-slate-500 text-center py-4">Nenhuma avaliação ainda.</p>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

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
            
            // Novos Estados do Modal
            activeIdea: null,
            activeTab: 'comments',
            comments: [],
            ratings: [],
            newComment: '',

            toast: {
                show: false,
                message: '',
                icon: '✅'
            },

            async init() {
                await this.fetchRoomDetails();
                await this.fetchIdeas();
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

            openIdeaDetails(idea) {
                this.activeIdea = idea;
                this.activeTab = 'comments';
                this.fetchComments(idea.id);
                this.fetchRatings(idea.id);
            },

            closeIdeaDetails() {
                this.activeIdea = null;
                this.comments = [];
                this.ratings = [];
                this.newComment = '';
            },

            async fetchRoomDetails() {
                try {
                    const response = await fetch(`/api/rooms/${this.uuid}`);
                    const json = await response.json();
                    const payload = json.data || json;
                    this.roomData = payload;
                    this.roomOwner = payload.owner_name || 'Anônimo';
                    this.myPersona = payload.my_persona || null;
                    this.roomId = this.roomData.id || this.roomId;
                } catch (error) {
                    console.error(error);
                }
            },

            async fetchIdeas() {
                this.loading = true;
                try {
                    let url = `/api/ideas?sort=${this.sortBy}&room_uuid=${this.uuid}`;
                    if (this.filterMine) url += `&filter=mine`;
                    const response = await fetch(url);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    const json = await response.json();
                    this.ideas = json.data || json;
                } catch (error) {
                    console.error(error);
                    this.ideas = [];
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
                        body: JSON.stringify({
                            content: this.newIdeaContent,
                            room_uuid: this.uuid
                        })
                    });

                    if (response.ok) {
                        this.newIdeaContent = '';
                        this.showForm = false;
                        this.showToast('Ideia publicada!', '🎉');
                        this.fetchIdeas();
                    }
                } catch (error) {
                    this.showToast('Erro ao enviar', '❌');
                } finally {
                    this.submitting = false;
                }
            },

            async fetchComments(ideaId) {
                try {
                    const response = await fetch(`/api/ideas/${ideaId}/comments`);
                    if (response.ok) {
                        const json = await response.json();
                        this.comments = json.data || json;
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            async fetchRatings(ideaId) {
                try {
                    const response = await fetch(`/api/ideas/${ideaId}/ratings`);
                    if (response.ok) {
                        const json = await response.json();
                        this.ratings = json.data || json;
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            async submitComment() {
                if (!this.newComment.trim() || !this.activeIdea) return;
                try {
                    const response = await fetch(`/api/ideas/${this.activeIdea.id}/comments`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            content: this.newComment,
                            room_uuid: this.uuid
                        })
                    });

                    if (response.ok) {
                        this.newComment = '';
                        this.fetchComments(this.activeIdea.id);
                        this.activeIdea.comments_count = (this.activeIdea.comments_count || 0) + 1;
                        this.showToast('Comentário enviado!', '💬');
                    }
                } catch (error) {
                    this.showToast('Erro ao comentar', '❌');
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
                        body: JSON.stringify({
                            score: score,
                            room_uuid: this.uuid
                        })
                    });
                    this.showToast('Avaliação salva!', '⭐');
                    if (this.activeIdea) {
                        this.fetchRatings(ideaId);
                    }
                    this.fetchIdeas();
                } catch (error) {
                    console.error(error);
                }
            }
        }
    }
</script>
@endsection