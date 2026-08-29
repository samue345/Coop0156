<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma de Crédito Cooperativo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #0b0f19;
            background-image:
                radial-gradient(at 0% 0%, hsla(142, 70%, 15%, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, hsla(220, 70%, 15%, 0.15) 0px, transparent 50%);
        }
        /* Glassmorphism utility */
        .glass-panel {
            background: rgba(19, 28, 46, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(30, 45, 74, 0.6);
        }
    </style>
</head>
<body class="text-slate-200 min-h-screen flex flex-col font-sans">

    <!-- Header / Navbar -->
    <header class="border-b border-panelBorder/50 py-5 glass-panel sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-xl bg-gradient-to-tr from-green-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-green-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight bg-gradient-to-r from-emerald-400 to-green-300 bg-clip-text text-transparent">Coop0156</h1>
                    <p class="text-xs text-slate-400">Desafio Análise de Crédito</p>
                </div>
            </div>
            <nav class="flex items-center gap-2" aria-label="Navegação principal">
                <a href="{{ url('/') }}" class="rounded-lg bg-emerald-500/10 px-3 py-2 text-sm font-medium text-emerald-400">Análise de crédito</a>
                <a href="{{ url('/contratacoes') }}" class="rounded-lg px-3 py-2 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">Contratações</a>
                <a href="{{ url('/clientes') }}" class="rounded-lg px-3 py-2 text-sm text-slate-400 transition hover:bg-slate-800 hover:text-white">Clientes</a>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Ambiente de Testes
                </span>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow max-w-6xl mx-auto px-4 py-12 w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Formulário de Solicitação -->
        <section class="lg:col-span-7 glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden transition-all duration-300 hover:border-panelBorder">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>

            <h2 class="text-2xl font-semibold mb-6 flex items-center gap-2">
                <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-sm">01</span>
                Nova Solicitação de Crédito
            </h2>

            <form id="form-analise" class="space-y-6">
                <!-- Nome Completo -->
                <div>
                    <label for="nome" class="block text-sm font-medium text-slate-400 mb-2">Nome Completo</label>
                    <input type="text" id="nome" name="nome" required placeholder="Digite o nome completo do proponente"
                        class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- CPF -->
                    <div>
                        <label for="cpf" class="block text-sm font-medium text-slate-400 mb-2">CPF</label>
                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            required
                            placeholder="000.000.000-00"
                            inputmode="numeric"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>

                    <!-- Renda Mensal -->
                    <div>
                        <label for="renda_mensal" class="block text-sm font-medium text-slate-400 mb-2">Renda Mensal (R$)</label>
                        <input
                            type="text"
                            id="renda_mensal"
                            name="renda_mensal"
                            required
                            placeholder="R$ 3.500,00"
                            inputmode="decimal"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tipo de Crédito -->
                    <div>
                        <label for="tipo_credito" class="block text-sm font-medium text-slate-400 mb-2">Tipo de Crédito</label>
                        <select id="tipo_credito" name="tipo_credito" required
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                            <option value="" disabled selected>Selecione uma opção</option>
                            <option value="pessoal">Crédito Pessoal</option>
                            <option value="imobiliario">Crédito Imobiliário</option>
                            <option value="automotivo">Crédito Automotivo</option>
                        </select>
                    </div>

                    <!-- Valor Solicitado -->
                    <div>
                        <label for="valor_solicitado" class="block text-sm font-medium text-slate-400 mb-2">Valor Requerido (R$)</label>
                        <input
                            type="text"
                            id="valor_solicitado"
                            name="valor_solicitado"
                            inputmode="decimal"
                            required
                            placeholder="R$ 15.000,00"
                            class="w-full bg-slate-950/50 border border-panelBorder rounded-xl px-4 py-3 text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Botão Enviar -->
                <button type="submit" id="btn-solicitar"
                    class="flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-600 px-6 text-sm font-semibold text-white shadow-lg shadow-emerald-500/10 transition duration-200 hover:from-emerald-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg disabled:cursor-not-allowed disabled:opacity-70 active:scale-[0.98]">
                    <span id="txt-solicitar">Solicitar Análise de Crédito</span>
                    <svg id="loading-spinner" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>
        </section>

        <!-- Resultados e Contratação -->
        <section class="lg:col-span-5 space-y-6">

            <!-- Card de Resultado Inicial (Placeholder) -->
            <div id="resultado-vazio" class="glass-panel rounded-3xl p-8 text-center border-dashed border-2 border-panelBorder flex flex-col items-center justify-center py-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 id="resultado-vazio-titulo" class="text-lg font-medium text-slate-400">Aguardando Solicitação</h3>
                <p id="resultado-vazio-descricao" class="text-sm text-slate-500 mt-2 max-w-xs">Preencha os dados do formulário ao lado e solicite a análise para simular as condições.</p>
            </div>

            <!-- Card de Resultado da Análise -->
            <div id="resultado-analise" class="glass-panel rounded-3xl p-8 shadow-2xl relative overflow-hidden hidden">
                <div id="status-indicator-badge" class="absolute top-6 right-6">
                    <!-- Badge Aprovado ou Reprovado (Dinâmico) -->
                </div>

                <h3 class="text-xl font-semibold mb-6 flex items-center gap-2">
                    <span class="bg-emerald-500/10 text-emerald-400 p-2 rounded-lg text-sm">02</span>
                    Resultado da Análise
                </h3>

                <!-- Dados da Análise -->
                <div class="space-y-4 divide-y divide-panelBorder">
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-400 text-sm">Proponente</span>
                        <span id="res-nome" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">CPF</span>
                        <span id="res-cpf" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">Score de Crédito</span>
                        <span id="res-score" class="font-medium text-slate-100">-</span>
                    </div>
                    <div class="flex justify-between pt-4">
                        <span class="text-slate-400 text-sm">Status da Análise</span>
                        <span id="res-status" class="font-bold">-</span>
                    </div>

                    <!-- Bloco Aprovado -->
                    <div id="dados-aprovado" class="space-y-4 pt-4 hidden">
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Taxa de Juros Aplicada</span>
                            <span id="res-taxa" class="font-medium text-emerald-400">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Parcela Mensal (12x)</span>
                            <span id="res-parcela" class="font-bold text-lg text-emerald-400">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 text-sm">Renda Comprometida</span>
                            <span id="res-comprometimento" class="font-medium text-slate-100">-</span>
                        </div>
                    </div>

                    <!-- Bloco Reprovado -->
                    <div id="dados-reprovado" class="pt-4 hidden">
                        <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mt-2">
                            <span class="text-red-400 text-xs block font-semibold uppercase tracking-wider mb-1">Motivo da Recusa</span>
                            <p id="res-motivo" class="text-slate-200 text-sm">-</p>
                        </div>
                    </div>
                </div>

                <!-- Ações para Contratação -->
                <div id="container-contratacao" class="mt-8 pt-6 border-t border-panelBorder hidden">
                    <a id="link-simulacao" href="#"
                        class="mb-3 flex min-h-12 w-full items-center justify-center rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-3 text-center text-sm font-semibold text-emerald-400 transition hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg">
                        Ver simulação e confirmar contratação
                    </a>
                    <button id="btn-contratar"
                        class="flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-6 text-sm font-semibold text-white shadow-lg shadow-indigo-500/10 transition duration-200 hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-darkBg disabled:cursor-not-allowed disabled:opacity-70 active:scale-[0.98]">
                        <span id="txt-contratar">Confirmar Contratação do Crédito</span>
                        <svg id="loading-spinner-contratar" class="animate-spin h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                    <p id="feedback-contratacao" class="mt-3 text-center text-sm text-red-400"></p>
                </div>
            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-panelBorder/40 py-6 text-center text-xs text-slate-600">
        <div class="max-w-6xl mx-auto px-4">
            <p>&copy; 2026 CoopCred. Todos os direitos reservados. Desafio Técnico Laravel.</p>
        </div>
    </footer>

    <!--
      -- =========================================================================
      -- INSTRUÇÕES DE IMPLEMENTAÇÃO JAVASCRIPT (DESAFIO PARA O CANDIDATO)
      -- =========================================================================
      -- O candidato deve escrever o JavaScript abaixo para integrar com as APIs.
      -- Requisitos:
      --   1. Tratar a submissão do formulário 'form-analise'.
      --   2. Fazer requisição POST para '/api/analise-credito' com os dados do form.
      --   3. Se REPROVADO: exibir o card de resultado com o motivo da recusa.
      --   4. Se APROVADO: exibir o card de resultado e um botão/link que redirecione
      --      o usuário para '/simulacao/{code}' para visualizar as condições antes de contratar.
      -->
    <script>
        const onlyDigits = (value) => value.replace(/\D/g, '');

        const cpfMask = (value) => onlyDigits(value)
            .slice(0, 11)
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');

        const moneyMask = (value) => {
            const digits = onlyDigits(value);

            if (!digits) {
                return '';
            }

            return `R$ ${(Number(digits) / 100).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            })}`;
        };

        const moneyValue = (value) => (
            Number(onlyDigits(value)) / 100
        ).toFixed(2);

        const isValidCpf = (value) => {
            const cpf = onlyDigits(value);

            if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
                return false;
            }

            const checkDigit = (digits) => {
                const sum = [...digits].reduce(
                    (total, digit, index) => total + Number(digit) * (digits.length + 1 - index),
                    0
                );
                const remainder = sum % 11;

                return remainder < 2 ? 0 : 11 - remainder;
            };

            return Number(cpf[9]) === checkDigit(cpf.slice(0, 9))
                && Number(cpf[10]) === checkDigit(cpf.slice(0, 10));
        };

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('form-analise');
            const resultadoVazio = document.getElementById('resultado-vazio');
            const resultadoVazioTitulo = document.getElementById('resultado-vazio-titulo');
            const resultadoVazioDescricao = document.getElementById('resultado-vazio-descricao');
            const resultadoAnalise = document.getElementById('resultado-analise');
            const containerContratacao = document.getElementById('container-contratacao');
            const linkSimulacao = document.getElementById('link-simulacao');
            const alertBox = document.createElement('p');
            const cpf = document.getElementById('cpf');
            const rendaMensal = document.getElementById('renda_mensal');
            const valorSolicitado = document.getElementById('valor_solicitado');
            const loadingSpinner = document.getElementById('loading-spinner');
            const btnContratar = document.getElementById('btn-contratar');
            const txtContratar = document.getElementById('txt-contratar');
            const loadingSpinnerContratar = document.getElementById('loading-spinner-contratar');
            const feedbackContratacao = document.getElementById('feedback-contratacao');
            let currentAnalysisCode = null;
            const waitingTitle = 'Aguardando Solicitação';
            const waitingDescription = 'Preencha os dados do formulário ao lado e solicite a análise para simular as condições.';

            const setAnalysisLoading = (loading) => {
                resultadoVazioTitulo.textContent = loading ? 'Processando análise...' : waitingTitle;
                resultadoVazioDescricao.textContent = loading
                    ? 'Aguarde enquanto consultamos seus dados.'
                    : waitingDescription;
                resultadoVazio.classList.toggle('hidden', !loading);
                resultadoAnalise.classList.toggle('hidden', loading);
            };

            cpf.addEventListener('input', () => {
                cpf.value = cpfMask(cpf.value);
            });

            rendaMensal.addEventListener('input', () => {
                rendaMensal.value = moneyMask(rendaMensal.value);
            });

            valorSolicitado.addEventListener('input', () => {
                valorSolicitado.value = moneyMask(valorSolicitado.value);
            });

            alertBox.className = 'mt-4 text-center text-sm text-red-400';
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                alertBox.textContent = '';
                feedbackContratacao.textContent = '';
                feedbackContratacao.className = 'mt-3 text-center text-sm text-red-400';
                currentAnalysisCode = null;
                containerContratacao.classList.add('hidden');
                linkSimulacao.removeAttribute('href');
                linkSimulacao.textContent = 'Ver simulação e confirmar contratação';
                btnContratar.disabled = false;
                txtContratar.textContent = 'Confirmar Contratação do Crédito';
                const button = document.getElementById('btn-solicitar');
                button.disabled = true;
                loadingSpinner.classList.remove('hidden');
                setAnalysisLoading(true);
                const payload = Object.fromEntries(new FormData(form));
                payload.cpf = onlyDigits(payload.cpf);
                payload.renda_mensal = moneyValue(payload.renda_mensal);
                payload.valor_solicitado = moneyValue(payload.valor_solicitado);

                if (!isValidCpf(payload.cpf)) {
                    alertBox.textContent = 'Informe um CPF válido.';
                    form.appendChild(alertBox);
                    button.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    setAnalysisLoading(false);
                    return;
                }

                if (Number(payload.renda_mensal) <= 0 || Number(payload.valor_solicitado) <= 0) {
                    alertBox.textContent = 'A renda e o valor solicitado devem ser maiores que zero.';
                    form.appendChild(alertBox);
                    button.disabled = false;
                    loadingSpinner.classList.add('hidden');
                    setAnalysisLoading(false);
                    return;
                }

                try {
                    const response = await fetch('/api/analise-credito', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const body = await response.json();
                    if (!response.ok) {
                        throw new Error(body?.message || 'Não foi possível solicitar a análise.');
                    }

                    const analysis = body.data || body;
                    setAnalysisLoading(false);
                    resultadoVazio.classList.add('hidden');
                    resultadoAnalise.classList.remove('hidden');
                    document.getElementById('res-nome').textContent = analysis.nome || payload.nome;
                    document.getElementById('res-cpf').textContent = analysis.cpf || payload.cpf;
                    document.getElementById('res-score').textContent = analysis.score ?? '-';
                    document.getElementById('res-status').textContent = analysis.status || '-';
                    document.getElementById('res-taxa').textContent = analysis.taxa_juros
                        ? `${analysis.taxa_juros}% a.m.`
                        : '-';
                    document.getElementById('res-parcela').textContent = analysis.valor_parcela
                        ? `R$ ${Number(analysis.valor_parcela).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
                        : '-';
                    document.getElementById('res-comprometimento').textContent = analysis.valor_parcela
                        ? `${((Number(analysis.valor_parcela) / Number(payload.renda_mensal)) * 100).toLocaleString('pt-BR', { maximumFractionDigits: 1 })}%`
                        : '-';
                    document.getElementById('res-motivo').textContent = analysis.motivo_rejeicao || '-';
                    document.getElementById('dados-aprovado').classList.toggle('hidden', analysis.status !== 'aprovado');
                    document.getElementById('dados-reprovado').classList.toggle('hidden', analysis.status !== 'reprovado');

                    if (analysis.status === 'aprovado') {
                        currentAnalysisCode = analysis.code;
                        containerContratacao.classList.remove('hidden');
                        linkSimulacao.href = `/simulacao/${analysis.code}`;
                        linkSimulacao.textContent = 'Ver simulação e confirmar contratação';
                    }
                }
                catch (error) {
                    alertBox.textContent = error.message || 'Não foi possível solicitar a análise.';
                    form.appendChild(alertBox);
                    setAnalysisLoading(false);
                } finally {
                    button.disabled = false;
                    loadingSpinner.classList.add('hidden');
                }
            });

            btnContratar.addEventListener('click', async () => {
                if (!currentAnalysisCode) {
                    feedbackContratacao.textContent = 'Solicite uma análise aprovada antes de contratar.';
                    return;
                }

                btnContratar.disabled = true;
                loadingSpinnerContratar.classList.remove('hidden');
                txtContratar.textContent = 'Processando...';
                feedbackContratacao.textContent = '';

                try {
                    const response = await fetch(`/api/analise-credito/${currentAnalysisCode}/contratar`, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                    });
                    const body = await response.json();

                    if (!response.ok) {
                        throw new Error(body?.message || 'Não foi possível confirmar a contratação.');
                    }

                    document.getElementById('res-status').textContent = body?.status || 'processando_contratacao';
                    linkSimulacao.href = '/contratacoes';
                    linkSimulacao.textContent = 'Ver contratações';
                    feedbackContratacao.className = 'mt-3 text-center text-sm text-emerald-400';
                    feedbackContratacao.textContent = body?.message || 'Contratação enviada para processamento.';
                } catch (error) {
                    feedbackContratacao.className = 'mt-3 text-center text-sm text-red-400';
                    feedbackContratacao.textContent = error.message || 'Não foi possível confirmar a contratação.';
                    btnContratar.disabled = false;
                } finally {
                    loadingSpinnerContratar.classList.add('hidden');
                    txtContratar.textContent = 'Confirmar Contratação do Crédito';
                }
            });
        });
    </script>
</body>
</html>
