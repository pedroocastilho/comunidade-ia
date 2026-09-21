<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — Círculo Aura</title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600&family=Cinzel:wght@600&family=Instrument+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; }
        body { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
               background: #16120C; color: #F2EBDD; font-family: 'Instrument Sans', sans-serif; padding: 24px; text-align: center; }
        img.logo { width: 84px; height: 84px; border-radius: 999px; }
        .marca { font-family: Cinzel, serif; letter-spacing: .25em; color: #C9A24B; font-weight: 600; margin-top: 14px; font-size: 15px; }
        h1 { font-family: 'Bricolage Grotesque', sans-serif; font-size: 64px; color: #C9A24B; margin-top: 36px; line-height: 1; }
        p { color: #A89D8C; margin-top: 12px; max-width: 420px; line-height: 1.6; }
        a.botao { display: inline-block; margin-top: 28px; background: linear-gradient(90deg, #C9A24B, #E8CE8F);
                  color: #16120C; font-weight: 600; text-decoration: none; padding: 12px 32px; border-radius: 999px; }
    </style>
</head>
<body>
    <img class="logo" src="/logo.png" alt="">
    <div class="marca">CÍRCULO AURA</div>
    <h1>@yield('code')</h1>
    <p>@yield('message')</p>
    <a class="botao" href="/">Voltar ao início</a>
</body>
</html>
