@extends('layouts.app')

@section('title', 'Clientes | Coop0156')

@section('content')
<main class="mx-auto w-full max-w-6xl px-4 py-10">
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="mb-2 text-sm font-medium text-emerald-400">Cadastro</p><h1 class="text-3xl font-semibold text-white">Clientes</h1><p class="mt-2 text-sm text-slate-400">Consulte e mantenha os dados cadastrais.</p></div>
        <a href="{{ url('/clientes/create') }}" class="rounded-lg bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-emerald-400">Novo cliente</a>
    </div>
    <section class="glass-panel overflow-hidden rounded-2xl">
        <div class="border-b border-panelBorder/70 px-5 py-4"><h2 class="font-medium text-white">Clientes cadastrados</h2><p id="list-summary" class="mt-1 text-xs text-slate-500">Carregando...</p></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[700px] text-left text-sm"><thead class="bg-slate-950/30 text-xs uppercase tracking-wider text-slate-500"><tr><th class="px-5 py-3">Cliente</th><th class="px-5 py-3">CPF</th><th class="px-5 py-3">Telefone</th><th class="px-5 py-3">Renda mensal</th><th class="px-5 py-3 text-right">Ações</th></tr></thead><tbody id="customers-table" class="divide-y divide-panelBorder/60"><tr><td colspan="5" class="px-5 py-12 text-center text-slate-500">Carregando clientes...</td></tr></tbody></table></div>
        <div class="flex items-center justify-between border-t border-panelBorder/70 px-5 py-4"><span id="page-label" class="text-xs text-slate-500"></span><div class="flex gap-2"><button id="previous-page" class="rounded-lg border border-panelBorder px-3 py-2 text-xs disabled:opacity-40" disabled>Anterior</button><button id="next-page" class="rounded-lg border border-panelBorder px-3 py-2 text-xs disabled:opacity-40" disabled>Próxima</button></div></div>
    </section>
</main>
@endsection

@push('scripts') @vite('resources/js/customers.js') @endpush
