@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- HERO SECTION --}}
    <section class="flex flex-col items-center justify-center min-h-[45vh] text-center mb-12">
        <div class="group cursor-pointer mb-6 transition-transform duration-300 hover:scale-105 hover:rotate-3">
            <div class="p-6 rounded-full bg-amber-500/10 border border-amber-500/20 shadow-[0_0_50px_rgba(244,187,44,0.15)]">
                <svg class="w-28 h-28 text-amber-400 drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
        </div>

        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white mb-4">
            {{ __('app.home.hero.title') }}
        </h1>
        <p class="text-slate-400 max-w-xl text-base sm:text-lg mb-8">
            {{ __('app.home.hero.subtitle') }}
        </p>

        <a href="{{ route('rooms.create') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-lg rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 transition duration-200">
            {{ __('app.home.hero.cta') }}
        </a>
    </section>

    <hr class="border-slate-800 mb-12" />

    {{-- FEED DE SALAS PÚBLICAS --}}
    <section x-data="publicRoomsFeed()" class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-white">{{ __('app.home.feed.title') }}</h2>
            <p class="text-sm text-slate-400">{{ __('app.home.feed.subtitle') }}</p>
        </div>

        {{-- Loading Skeleton --}}
        <div x-show="loading && rooms.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="i in 3" :key="i">
                <div class="animate-pulse bg-slate-900 border border-slate-800 h-52 rounded-2xl p-6 flex flex-col justify-between">
                    <div class="h-5 bg-slate-800 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-slate-800/60 rounded w-full mb-2"></div>
                    <div class="h-8 bg-slate-800 rounded w-full mt-auto"></div>
                </div>
            </template>
        </div>

        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="!loading || rooms.length > 0">
            <template x-for="item in rooms" :key="item.id || item.uuid">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-700 transition duration-200 flex flex-col justify-between">

                    <div>
                        {{-- Topo do Card: Badge de Expiração --}}
                        <div class="mb-3">
                            {{-- Badges de Expiração --}}
                            {{-- 1. Sala Permanente (sem data de expiração) --}}
                            <template x-if="!item.expires_at">
                                <x-badge variant="cyan" icon="♾️">
                                    {{ __('app.ideas.no_expires') }}
                                </x-badge>
                            </template>

                            {{-- 2. Sala Prestes a Expirar (Temporária + Expira em Breve) --}}
                            <template x-if="item.expires_at && item.is_expiring_soon">
                                <x-badge variant="amber" icon="⏳" :pulse="true">
                                    <span x-text="'{{ __('app.ideas.expires_in') }} ' + item.expires_at_human"></span>
                                </x-badge>
                            </template>

                            {{-- 3. Sala Temporária Normal (Expira, mas não em breve) --}}
                            <template x-if="item.expires_at && !item.is_expiring_soon">
                                <x-badge variant="slate" icon="⏳">
                                    <span x-text="'{{ __('app.ideas.expires_in') }} ' + item.expires_at_human"></span>
                                </x-badge>
                            </template>
                        </div>
                        <h3 class="text-lg font-bold text-white line-clamp-2 mb-4" x-text="item.description"></h3>

                        {{-- Badges de Métricas --}}
                        <div class="grid grid-cols-3 gap-2 text-center text-xs text-slate-400 bg-slate-950/50 px-3 py-2 rounded-xl border border-slate-800/60 mb-4">

                            <div class="flex items-center justify-center gap-1 min-w-0">
                                <span>💡</span>
                                <strong class="text-slate-200" x-text="item.ideas_count || 0"></strong>
                                <span class="hidden sm:inline text-slate-400">{{ __('app.home.card.ideas') }}</span>
                            </div>

                            <div class="flex items-center justify-center gap-1 border-x border-slate-800/80 px-1 min-w-0">
                                <span>💬</span>
                                <strong class="text-slate-200" x-text="item.comments_count || 0"></strong>
                                <span class="hidden sm:inline text-slate-400">{{ __('app.home.card.comments') }}</span>
                            </div>

                            <div class="flex items-center justify-center gap-1 min-w-0">
                                <span>👥</span>
                                <strong class="text-slate-200" x-text="item.participants_count || 0"></strong>
                                <span class="hidden sm:inline text-slate-400">{{ __('app.home.card.people') }}</span>
                            </div>

                        </div>
                    </div>

                    {{-- Rodapé do Card --}}
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <div class="flex flex-col min-w-0">
                            <span class="text-xs font-medium text-slate-300 truncate">
                                {{ __('app.home.card.created_by') }} <span class="text-amber-400 font-semibold" x-text="item.owner_name || 'Bill'"></span>
                            </span>
                        </div>

                        <a :href="'/rooms/' + item.uuid"
                            class="shrink-0 px-3.5 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-bold rounded-lg border border-amber-500/20 transition duration-150 inline-flex items-center gap-1">
                            {{ __('app.home.card.enter') }} &rarr;
                        </a>
                    </div>

                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <div x-show="!loading && rooms.length === 0" class="text-center py-12 bg-slate-900/50 rounded-2xl border border-dashed border-slate-800">
            <p class="text-slate-400">{{ __('app.home.feed.empty') }}</p>
        </div>

        {{-- Botão de Paginação --}}
        <div x-show="nextPageUrl" class="text-center pt-6">
            <button
                @click="loadMore()"
                :disabled="loading"
                class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-xl transition text-sm disabled:opacity-50">
                <span x-show="!loading">{{ __('app.home.feed.load_more') }}</span>
                <span x-show="loading">{{ __('app.home.feed.loading') }}</span>
            </button>
        </div>
    </section>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('publicRoomsFeed', () => ({
            rooms: [],
            nextPageUrl: '/api/rooms/public',
            loading: false,

            init() {
                this.fetchRooms();
            },

            async fetchRooms() {
                if (!this.nextPageUrl || this.loading) return;
                this.loading = true;

                try {
                    const response = await fetch(this.nextPageUrl);
                    const json = await response.json();

                    this.rooms = [...this.rooms, ...(json.data || [])];
                    this.nextPageUrl = json.links ? json.links.next : null;
                } catch (error) {
                    console.error('Erro ao buscar salas públicas:', error);
                } finally {
                    this.loading = false;
                }
            },

            loadMore() {
                this.fetchRooms();
            }
        }));
    });
</script>
@endsection