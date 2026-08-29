@extends('layouts.app')

@section('title', 'Contratações | Coop0156')

@section('content')
<main class="mx-auto w-full max-w-6xl px-4 py-10">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="mb-2 text-sm font-medium text-emerald-400">Crédito contratado</p>
            <h1 class="text-3xl font-semibold text-white">Contratações</h1>
            <p class="mt-2 text-sm text-slate-400">Acompanhe os créditos aprovados que foram contratados.</p>
        </div>
        <a href="{{ url('/') }}" class="inline-flex h-10 items-center justify-center rounded-lg bg-emerald-500 px-4 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg">
            Nova análise
        </a>
    </div>

    <section class="glass-panel overflow-hidden rounded-2xl">
        <div class="border-b border-panelBorder/70 px-5 py-4">
            <h2 class="font-medium text-white">Contratações realizadas</h2>
            <p class="mt-1 text-xs text-slate-500">
                Página {{ $contracts->currentPage() }}{{ $contracts->hasMorePages() ? ' com mais resultados disponíveis' : '' }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="bg-slate-950/30 text-xs uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Código</th>
                        <th class="px-5 py-3">Cliente</th>
                        <th class="px-5 py-3">CPF</th>
                        <th class="px-5 py-3">Tipo</th>
                        <th class="px-5 py-3">Valor contratado</th>
                        <th class="px-5 py-3">Parcela</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Taxa</th>
                        <th class="px-5 py-3">Contratado em</th>
                        <th class="px-5 py-3 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-panelBorder/60">
                    @forelse($contracts as $contract)
                        <tr class="transition hover:bg-slate-950/20">
                            <td class="px-5 py-4 font-mono text-xs text-emerald-400">{{ $contract->hashids_code }}</td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-100">{{ $contract->customer?->nome ?? $contract->nome }}</p>
                                <p class="text-xs text-slate-500">Score {{ $contract->score }}</p>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-slate-300">{{ $contract->cpf }}</td>
                            <td class="px-5 py-4 capitalize text-slate-300">{{ $contract->tipo_credito->value }}</td>
                            <td class="px-5 py-4 font-medium text-slate-100">
                                R$ {{ number_format($contract->valor_solicitado, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                12x de R$ {{ number_format($contract->valor_parcela, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                R$ {{ number_format($contract->valor_parcela * 12, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-slate-300">{{ number_format($contract->taxa_juros, 1, ',', '.') }}% a.m.</td>
                            <td class="px-5 py-4 text-slate-400">{{ $contract->updated_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ url('/simulacao/' . $contract->hashids_code) }}" class="inline-flex h-9 items-center justify-center whitespace-nowrap rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 text-xs font-semibold text-emerald-400 transition hover:bg-emerald-500/20 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-darkBg">
                                    Ver detalhes
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center text-slate-500">
                                Nenhuma contratação encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-panelBorder/70 px-5 py-4">
            <span class="text-xs text-slate-500">Página {{ $contracts->currentPage() }}</span>
            <div class="flex gap-2">
                @if($contracts->previousPageUrl())
                    <a href="{{ $contracts->previousPageUrl() }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-panelBorder px-3 text-xs font-medium text-slate-300 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-darkBg">Anterior</a>
                @else
                    <span class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-panelBorder px-3 text-xs font-medium text-slate-600 opacity-60">Anterior</span>
                @endif

                @if($contracts->nextPageUrl())
                    <a href="{{ $contracts->nextPageUrl() }}" class="inline-flex h-9 items-center justify-center rounded-lg border border-panelBorder px-3 text-xs font-medium text-slate-300 transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 focus:ring-offset-darkBg">Próxima</a>
                @else
                    <span class="inline-flex h-9 cursor-not-allowed items-center justify-center rounded-lg border border-panelBorder px-3 text-xs font-medium text-slate-600 opacity-60">Próxima</span>
                @endif
            </div>
        </div>
    </section>
</main>
@endsection
