    @props([
    'variant' => 'slate', // Opções: emerald, cyan, amber, slate
    'icon' => null,
    'pulse' => false,
    ])

    @php
    $variants = [
    'emerald' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    'cyan' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
    'amber' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
    'slate' => 'bg-slate-800 text-slate-300 border-slate-700/60',
    ];

    $variantClass = $variants[$variant] ?? $variants['slate'];
    @endphp

    <span {{ $attributes->merge([
    'class' => "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold border shrink-0 {$variantClass} " . ($pulse ? 'animate-pulse' : '')
]) }}>
        @if($icon)
        <span class="leading-none text-xs">{{ $icon }}</span>
        @endif
        <span>{{ $slot }}</span>
    </span>