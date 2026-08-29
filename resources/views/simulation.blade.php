<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulação de Crédito — Coop0156</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #0b0f19;
            background-image:
                radial-gradient(at 20% 20%, hsla(210, 70%, 15%, 0.2) 0px, transparent 50%),
                radial-gradient(at 80% 80%, hsla(142, 70%, 12%, 0.15) 0px, transparent 50%);
        }
        .glass-panel {
            background: rgba(19, 28, 46, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(30, 45, 74, 0.6);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex flex-col font-sans">

    <!-- Header -->
    <header class="border-b border-panelBorder/50 py-5 glass-panel sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 flex justify-between items-center">
            <a href="/" class="flex items-center gap-3 group">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-green-300 bg-clip-text text-transparent">Coop0156</h1>
                    <p class="text-xs text-slate-400">Desafio Análise de Crédito</p>
                </div>
            </a>
            <nav class="flex items-center gap-2 text-sm" aria-label="Navegação principal">
                <a href="{{ url('/') }}" class="text-slate-400 hover:text-emerald-400 transition-colors">Nova Análise</a>
                <a href="{{ url('/contratacoes') }}" class="text-slate-400 hover:text-emerald-400 transition-colors">Contratações</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-4xl mx-auto px-4 py-12 w-full">
        @php
            $valorTotal = $analise->valor_parcela * 12;
            $comprometimento = ($analise->valor_parcela / $analise->renda_mensal) * 100;
            $isProcessing = $analise->status === \App\Enums\AnalysisStatus::PROCESSING_CONTRACT;
            $isContracted = $analise->status === \App\Enums\AnalysisStatus::CONTRACTED;
        @endphp

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-slate-500 mb-8">
            <a href="/" class="hover:text-slate-300 transition-colors">Análise</a>
            <span>/</span>
            <span class="text-slate-300">Simulação</span>
        </nav>

        <!-- Cabeçalho da Simulação -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-3xl font-bold text-white">
                    @if($isContracted)
                        Crédito Contratado
                    @elseif($isProcessing)
                        Contratação em Processamento
                    @else
                        Simulação de Crédito
                    @endif
                </h2>
                <p class="text-slate-400 mt-1">
                    @if($isContracted)
                        Confira as condições do crédito contratado.
                    @elseif($isProcessing)
                        Sua contratação foi enviada para processamento.
                    @else
                        Revise as condições antes de confirmar a contratação.
                    @endif
                </p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                @if($isContracted)
                    Contratado
                @elseif($isProcessing)
                    Processando
                @else
                    Pré-aprovado
                @endif
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Dados do Proponente -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Proponente</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500">Nome</p>
                        <p class="font-semibold text-slate-100">{{ $analise->nome }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">CPF</p>
                        <p class="font-medium text-slate-200 font-mono">{{ $analise->cpf }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Renda Mensal</p>
                        <p class="font-medium text-slate-200">R$ {{ number_format($analise->renda_mensal, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Tipo de Crédito</p>
                        <p class="font-medium text-slate-200 capitalize">{{ $analise->tipo_credito->value }}</p>
                    </div>
                </div>
            </div>

            <!-- Score e Aprovação -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Score de Crédito</h3>
                <div class="flex flex-col items-center justify-center h-32">
                    <p class="text-6xl font-bold bg-gradient-to-b from-emerald-300 to-emerald-500 bg-clip-text text-transparent">
                        {{ $analise->score }}
                    </p>
                    <p class="text-slate-400 text-sm mt-2">Pontuação Obtida</p>
                </div>
                <div class="mt-4 pt-4 border-t border-panelBorder">
                    <p class="text-xs text-slate-500">Taxa de Juros Aplicada</p>
                    <p class="text-xl font-bold text-emerald-400 mt-1">{{ number_format($analise->taxa_juros, 1, ',', '.') }}% a.m.</p>
                </div>
            </div>

            <!-- Condições Financeiras -->
            <div class="glass-panel rounded-2xl p-6">
                <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-4">Condições</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-slate-500">Valor Solicitado</p>
                        <p class="font-semibold text-slate-100 text-lg">R$ {{ number_format($analise->valor_solicitado, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Parcelas</p>
                        <p class="font-medium text-slate-200">12x fixas</p>
                    </div>
                    <div class="pt-3 border-t border-panelBorder">
                        <p class="text-xs text-slate-500">Valor Estimado da Parcela</p>
                        <p class="text-2xl font-bold text-white mt-1">
                            R$ {{ number_format($analise->valor_parcela, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-panelBorder">
                        <p class="text-xs text-slate-500">Valor Total a Pagar</p>
                        <p class="text-xl font-bold text-emerald-400 mt-1">
                            R$ {{ number_format($valorTotal, 2, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aviso de Comprometimento de Renda -->
        <div class="glass-panel rounded-2xl p-5 mt-6 flex items-center gap-4">
            <div class="h-10 w-10 rounded-xl bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-200">Comprometimento de renda</p>
                <p class="text-xs text-slate-400 mt-0.5">
                    A parcela representa aproximadamente <span class="text-blue-400 font-semibold">{{ number_format($comprometimento, 1, ',', '.') }}%</span>
                    da sua renda mensal declarada (R$ {{ number_format($analise->renda_mensal, 2, ',', '.') }}).
                </p>
            </div>
        </div>

        <div class="mt-8 glass-panel rounded-2xl p-8 text-center">

            @if(session('erro'))
                <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6 text-red-400 text-sm">
                    {{ session('erro') }}
                </div>
            @endif

            @if($isContracted || $isProcessing)
                <h3 class="text-xl font-semibold text-white mb-2">
                    {{ $isContracted ? 'Contratação concluída' : 'Contratação em processamento' }}
                </h3>
                <p class="text-slate-400 text-sm mb-8 max-w-md mx-auto">
                    {{ $isContracted ? 'Este crédito já foi contratado e está disponível para consulta na listagem de contratações.' : 'O pedido foi enviado para a fila e será concluído pelo worker.' }}
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ url('/contratacoes') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-panelBorder px-8 text-sm font-medium text-slate-300 transition hover:border-slate-500 hover:text-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-darkBg">
                        Ver contratações
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-8 text-sm font-medium text-emerald-400 transition hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg">
                        Nova análise
                    </a>
                </div>
            @else
                <h3 class="text-xl font-semibold text-white mb-2">Confirmar Contratação</h3>
                <p class="text-slate-400 text-sm mb-8 max-w-md mx-auto">
                    Ao confirmar, você está simulando a solicitação formal de contratação deste crédito. Esta ação não pode ser desfeita.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="/" class="inline-flex h-12 items-center justify-center rounded-xl border border-panelBorder px-8 text-sm font-medium text-slate-400 transition hover:border-slate-500 hover:text-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-darkBg">
                        Cancelar
                    </a>
                    <button id="btn-confirmar"
                        class="flex h-12 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-10 text-sm font-semibold text-white shadow-lg shadow-indigo-500/20 transition duration-200 hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-darkBg disabled:cursor-not-allowed disabled:opacity-70 active:scale-[0.98]">
                        <span id="txt-confirmar">Confirmar Contratação</span>
                        <svg id="spinner-confirmar" class="animate-spin h-4 w-4 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                <p id="feedback-contratacao" class="mt-4 text-sm text-red-400"></p>
            @endif
        </div>

    </main>

    <!-- Sucesso Modal -->
    <div id="modal-sucesso" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 hidden">
        <div class="glass-panel rounded-3xl p-10 max-w-md w-full mx-4 text-center">
            <div class="h-20 w-20 bg-emerald-500/10 text-emerald-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Contratação Enviada!</h3>
            <p class="text-slate-400 text-sm mb-6">O pedido foi enviado para processamento. O worker da fila finalizará a contratação.</p>
            <div class="bg-emerald-500/5 border border-emerald-500/10 rounded-xl p-3 mb-6 text-xs text-emerald-400 font-mono">
                Status: PROCESSANDO_CONTRATACAO
            </div>
            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ url('/contratacoes') }}" class="inline-flex h-12 items-center justify-center rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-8 text-sm font-medium text-emerald-400 transition hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg">
                    Ver contratações
                </a>
                <a href="/" class="inline-flex h-12 items-center justify-center rounded-xl border border-panelBorder px-8 text-sm font-medium text-slate-300 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-darkBg">
                    Nova análise
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-panelBorder/40 py-6 text-center text-xs text-slate-600">
        <p>&copy; 2026 Coop0156. Desafio Técnico Laravel.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const confirmButton = document.getElementById('btn-confirmar');

            if (!confirmButton) {
                return;
            }

            const spinner = document.getElementById('spinner-confirmar');
            const buttonText = document.getElementById('txt-confirmar');
            const feedback = document.getElementById('feedback-contratacao');
            const successModal = document.getElementById('modal-sucesso');

            const setLoading = (loading) => {
                confirmButton.disabled = loading;
                spinner.classList.toggle('hidden', !loading);
                buttonText.textContent = loading ? 'Processando...' : 'Confirmar Contratação';
            };

            confirmButton.addEventListener('click', async () => {
                setLoading(true);
                feedback.textContent = '';

                try {
                    const response = await fetch('/api/analise-credito/{{ $analise->hashids_code }}/contratar', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                    });
                    const body = await response.json();

                    if (!response.ok) {
                        feedback.textContent = body.message || 'Não foi possível confirmar a contratação.';
                        setLoading(false);

                        return;
                    }

                    successModal.classList.remove('hidden');
                }
                catch (error) {
                    feedback.textContent = 'Não foi possível confirmar a contratação.';
                    setLoading(false);
                }
                finally {
                    spinner.classList.add('hidden');
                    buttonText.textContent = 'Confirmar Contratação';
                }
            });
        });
    </script>

</body>
</html>
