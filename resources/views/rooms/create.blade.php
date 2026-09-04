@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12" x-data="createRoomForm()">

    {{-- Card de Boas-Vindas / Apresentação --}}
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 mb-8 text-center shadow-xl">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">
            Qual é o desafio ou tema da sua reunião?
        </h1>
        <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
            Escreva o objetivo da sala de forma clara. Os participantes poderão interagir e enviar ideias de forma totalmente anônima.
        </p>
    </div>

    {{-- Formulário de Criação --}}
    <form @submit.prevent="submitRoom()" class="space-y-6">
        <div>
            <label for="room-description" class="block text-sm font-semibold text-slate-300 mb-2">
                Descrição / Pergunta Principal <span class="text-amber-400">*</span>
            </label>
            
            <textarea 
                id="room-description"
                x-model="form.description"
                x-ref="descriptionInput"
                rows="4"
                class="w-full bg-slate-900 border border-slate-800 rounded-xl p-4 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition duration-200 resize-none"
                placeholder="Ex: Como podemos melhorar o processo de deploy sem impactar a produção?"
                required
                :disabled="loading"
            ></textarea>

            {{-- Erro de Validação --}}
            <template x-if="errorMessage">
                <p class="mt-2 text-sm text-rose-400" x-text="errorMessage"></p>
            </template>
        </div>

        {{-- Ação Principal --}}
        <div class="flex justify-center pt-2">
            <button 
                type="submit" 
                :disabled="loading || !form.description.trim()"
                class="px-8 py-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-lg rounded-xl shadow-lg shadow-amber-500/10 hover:shadow-amber-500/20 transition duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="!loading">Avançar para o Mural</span>
                <span x-show="loading" x-cloak>Criando Sala...</span>
                <span x-show="!loading" class="text-xl">&rarr;</span>
            </button>
        </div>
    </form>
</div>

<script>
function createRoomForm() {
    return {
        form: {
            description: ''
        },
        loading: false,
        errorMessage: null,

        init() {
            // Foco automático na textarea ao carregar a página (igual ao vanilla)
            this.$nextTick(() => {
                this.$refs.descriptionInput.focus();
            });
        },

        async submitRoom() {
            if (!this.form.description.trim() || this.loading) return;

            this.loading = true;
            this.errorMessage = null;

            try {
                const response = await fetch('/api/rooms', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        description: this.form.description
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Ocorreu um erro ao criar a sala.');
                }

                // Redireciona para o Mural da Sala recém-criada usando o UUID retornado
                window.location.href = `/rooms/${data.uuid || data.data.uuid}`;

            } catch (error) {
                this.errorMessage = error.message;
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection