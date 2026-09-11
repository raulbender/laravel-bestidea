@extends('layouts.app')

@section('content')
<div x-data="roomBoard('{{ $uuid }}', {{ $room->id ?? 'null' }})" class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    <x-room.toast />

    <x-room.header />

    <x-room.filters />

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