@extends('layouts.app')

@section('content')
<div x-data="roomBoard('{{ $uuid }}', {{ $room->id ?? 'null' }})" class="max-w-2xl mx-auto px-4 py-8 space-y-6">

    <x-room.toast />

    <!-- Header do Room (Largura total do container principal) -->
    <x-room.header />

    <!-- Container do Feed (Filtros + Lista de Cards alinhados) -->
    <section class="max-w-xl mx-auto space-y-6">
        
        <!-- Os filtros e o título passam a ficar aqui dentro, no mesmo limite de largura -->
        <x-room.filters />

        <!-- Skeletons de Loading -->
        <template x-if="loading && ideas.length === 0">
            <template x-for="i in 3" :key="i">
                <div class="animate-pulse bg-slate-900/60 border border-slate-800 h-28 rounded-2xl p-5"></div>
            </template>
        </template>

        <!-- Estado Vazio -->
        <template x-if="!loading && ideas.length === 0">
            <div class="text-center py-12 bg-slate-900/40 rounded-2xl border border-dashed border-slate-800">
                <p class="text-xs text-slate-500">{{ __('app.ideas.empty') }}</p>
            </div>
        </template>

        <!-- Feed de Ideias -->
        <template x-for="idea in ideas" :key="idea.id">
            <x-room.idea-card />
        </template>
    </section>

    <x-room.modal-comments />

</div>

@push('scripts')
    @include('rooms.partials.script')
@endpush
@endsection