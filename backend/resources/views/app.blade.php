<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>Círculo Aura</title>

        <meta name="description" content="Sua jornada diária de manifestação: ritual, aula e ação — guiada pela Aura.">
        <meta property="og:title" content="Círculo Aura">
        <meta property="og:description" content="Sua jornada diária de manifestação: ritual, aula e ação — guiada pela Aura.">
        <meta property="og:image" content="{{ url('/og-image.png') }}">
        <meta property="og:type" content="website">
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <!-- Fontes da identidade: Bricolage (titulos) + Fraunces (marca) + Instrument Sans (corpo) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,600;12..96,700&family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
