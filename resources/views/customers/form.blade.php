@extends('layouts.app')

@section('title', $customer ? 'Editar cliente | Coop0156' : 'Novo cliente | Coop0156')

@section('content')
<main class="mx-auto w-full max-w-3xl px-4 py-10"><a href="{{ url('/clientes') }}" class="text-sm text-slate-400 hover:text-emerald-400">&larr; Voltar para clientes</a><div class="mb-8 mt-6"><p class="mb-2 text-sm font-medium text-emerald-400">Cadastro</p><h1 class="text-3xl font-semibold text-white">{{ $customer ? 'Editar cliente' : 'Novo cliente' }}</h1></div>
<section class="glass-panel rounded-2xl p-6 sm:p-8"><div id="form-alert" class="mb-6 hidden rounded-lg border px-4 py-3 text-sm"></div><form id="customer-form" class="space-y-6" novalidate>
    <div><label for="nome" class="mb-2 block text-sm text-slate-300">Nome completo</label><input id="nome" name="nome" value="{{ $customer?->nome }}" required maxlength="255" class="field" placeholder="Digite o nome completo"><p data-error="nome" class="error"></p></div>
    <div class="grid gap-6 sm:grid-cols-2"><div><label for="cpf" class="mb-2 block text-sm text-slate-300">CPF</label><input id="cpf" name="cpf" value="{{ $customer?->cpf }}" inputmode="numeric" required class="field" placeholder="000.000.000-00" data-mask="cpf"><p data-error="cpf" class="error"></p></div><div><label for="telefone" class="mb-2 block text-sm text-slate-300">Telefone</label><input id="telefone" name="telefone" value="{{ $customer?->telefone }}" inputmode="tel" class="field" placeholder="(00) 00000-0000" data-mask="phone"><p data-error="telefone" class="error"></p></div></div>
    <div class="grid gap-6 sm:grid-cols-2"><div><label for="email" class="mb-2 block text-sm text-slate-300">E-mail</label><input id="email" name="email" type="email" value="{{ $customer?->email }}" required maxlength="255" class="field" placeholder="cliente@email.com"><p data-error="email" class="error"></p></div><div><label for="renda_mensal" class="mb-2 block text-sm text-slate-300">Renda mensal</label><input id="renda_mensal" name="renda_mensal" type="text" inputmode="decimal" value="{{ $customer?->renda_mensal }}" required class="field" placeholder="R$ 3.500,00" data-mask="money"><p data-error="renda_mensal" class="error"></p></div></div>
    <div class="flex flex-col-reverse justify-end gap-3 border-t border-panelBorder/70 pt-6 sm:flex-row"><a href="{{ url('/clientes') }}" class="rounded-lg border border-panelBorder px-5 py-3 text-center text-sm">Cancelar</a><button id="submit-button" class="rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 hover:bg-emerald-400">{{ $customer ? 'Salvar alterações' : 'Cadastrar cliente' }}</button></div>
</form></section></main>
<style>.field{width:100%;border-radius:.5rem;border:1px solid #1e2d4a;background:rgba(2,6,23,.5);padding:.75rem 1rem;color:white;outline:none}.field:focus{border-color:#10b981}.error{margin-top:.25rem;min-height:1rem;font-size:.75rem;color:#f87171}</style>
@endsection

@push('scripts')<script>window.customerPage={mode:@json($customer ? 'edit' : 'create'),code:@json($customer?->hashids_code)};</script>@vite('resources/js/customers.js')@endpush
