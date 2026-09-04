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
            Ideias anônimas. Decisões sem viés.
        </h1>
        <p class="text-slate-400 max-w-xl text-base sm:text-lg mb-8">
            Crie uma sala em segundos, convide seu time por link e colete feedback sincero sem a pressão da hierarquia.
        </p>

        <a href="{{ route('rooms.create') }}" class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-lg rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 transition duration-200">
            Criar Nova Sala
        </a>
    </section>

    <hr class="border-slate-800 mb-12" />

    {{-- FEED DE SALAS PÚBLICAS --}}
    <section x-data="publicRoomsFeed()" class="space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-white">Salas Públicas para Colaborar</h2>
            <p class="text-sm text-slate-400">Explore temas abertos e deixe suas ideias ou avaliações anonimamente.</p>
        </div>

        {{-- Loading Skeleton --}}
        <template x-if="loading && rooms.length === 0">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template x-for="i in 3" :key="i">
                    <div class="animate-pulse bg-slate-900 border border-slate-800 h-44 rounded-2xl p-6 flex flex-col justify-between">
                        <div class="h-4 bg-slate-800 rounded w-3/4"></div>
                        <div class="h-3 bg-slate-800/60 rounded w-1/2"></div>
                        <div class="h-8 bg-slate-800 rounded w-full mt-4"></div>
                    </div>
                </template>
            </div>
        </template>

        {{-- Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-cloak>
            <template x-for="room in rooms" :key="room.id">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-sm hover:border-slate-700 transition duration-200 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Aberta
                            </span>
                            <span class="text-xs text-slate-500" x-text="room.created_at_human"></span>
                        </div>
                        <h3 class="text-lg font-bold text-white line-clamp-2 mb-2" x-text="room.description"></h3>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between">
                        <span class="text-xs text-slate-400">
                            <strong x-text="room.participants_count || 0"></strong> participantes
                        </span>
                        
                        <a :href="'/rooms/' + room.uuid" class="inline-flex items-center text-sm font-semibold text-amber-400 hover:text-amber-300">
                            Entrar na Sala &rarr;
                        </a>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty State --}}
        <template x-if="!loading && rooms.length === 0">
            <div class="text-center py-12 bg-slate-900/50 rounded-2xl border border-dashed border-slate-800">
                <p class="text-slate-400">Nenhuma sala pública ativa no momento. Seja o primeiro a criar!</p>
            </div>
        </template>

        {{-- Botão de Paginação --}}
        <template x-if="nextPageUrl">
            <div class="text-center pt-6">
                <button 
                    @click="loadMore()" 
                    :disabled="loading" 
                    class="px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium rounded-xl transition text-sm disabled:opacity-50"
                >
                    <span x-show="!loading">Carregar mais salas</span>
                    <span x-show="loading" x-cloak>Carregando...</span>
                </button>
            </div>
        </template>
    </section>
</div>

<script>
function publicRoomsFeed() {
    return {
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
    }
}
</script>
@endsection