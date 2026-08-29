<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Coop0156')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background: #0b0f19; }
        .glass-panel { background: rgba(19, 28, 46, .82); border: 1px solid rgba(30, 45, 74, .8); }
    </style>
    @stack('head')
</head>
<body class="min-h-screen text-slate-200">
    <header class="sticky top-0 z-50 border-b border-panelBorder/70 bg-darkBg/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-xl font-bold text-white">C</span>
                <span><strong class="block text-xl text-emerald-400">Coop0156</strong><small class="block text-xs text-slate-500">Gestão de crédito</small></span>
            </a>
            <nav class="flex items-center gap-1 text-sm" aria-label="Navegação principal">
                <a href="{{ url('/') }}" class="rounded-lg px-3 py-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">Análise de crédito</a>
                <a href="{{ url('/contratacoes') }}" class="rounded-lg px-3 py-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">Contratações</a>
                <a href="{{ url('/clientes') }}" class="rounded-lg px-3 py-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">Clientes</a>
            </nav>
        </div>
    </header>
    @yield('content')
    @stack('scripts')
</body>
</html>
