<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BestIdea') }} - Ideias Anônimas & Decisões Sem Viés</title>

    {{-- Importação do Vite com CSS e JS (que já traz o Alpine.js) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased flex flex-col justify-between selection:bg-amber-500 selection:text-slate-950">

    {{-- NAVBAR GLOBAL --}}
    <nav class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            
            {{-- Logo / Brand --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-black text-xl tracking-tight text-amber-400 hover:text-amber-300 transition">
                <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span>BestIdea</span>
            </a>

            {{-- Contexto do Usuário / Autenticação --}}
            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-300 hover:text-white transition">
                        Meu Painel
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-rose-400 hover:text-rose-300 transition">
                            Sair
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white transition">
                        Entrar
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm rounded-lg transition shadow-sm">
                        Criar Conta
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- CONTEÚDO DINÂMICO DA VIEW DA PÁGINA --}}
    <main class="mb-auto">
        @yield('content')
    </main>

    {{-- FOOTER GLOBAL --}}
    <footer class="border-t border-slate-900 bg-slate-950/80 py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} BestIdea. Decisões sem viés acionadas por IA.</p>
    </footer>

</body>
</html>