# Comunidade IA — App (Flutter)

App nativo (iOS/Android) da Comunidade IA. Consome a API Laravel em `/backend`.

> ⚠️ **Status: código-base NÃO compilado.** Foi escrito sem o Flutter SDK
> instalado na máquina de desenvolvimento, então **ainda não foi buildado nem
> testado**. Pode precisar de ajustes ao rodar `flutter pub get` / `flutter run`.

## Estrutura

```
lib/
  main.dart              # app + gate de autenticacao (token salvo -> Home ou Login)
  config.dart            # baseUrl da API
  services/
    api.dart             # cliente HTTP da API (login, home, cursos, aula, progresso)
    auth_store.dart      # token Sanctum em shared_preferences
  screens/
    login_screen.dart    # login
    home_screen.dart     # bottom nav: Inicio (trilhas) + Cursos (catalogo)
    curso_screen.dart    # detalhe do curso (modulos/aulas + progresso)
    player_screen.dart   # player (webview do embed Bunny) + concluir aula
```

## Como rodar (quando o SDK estiver instalado)

1. Instalar o Flutter SDK e as toolchains (Android SDK / Xcode).
2. `flutter pub get`
3. Subir a API: em `/backend`, `php artisan serve` (a `baseUrl` usa `10.0.2.2:8000`
   para o emulador Android alcançar o host).
4. `flutter run`

Login de teste (seed do backend): `admin@comunidade.local` / `admin12345`.

## Ainda por fazer (paridade com o plano)

- Abas Explorar, Em alta e Downloads (offline).
- Onboarding e cadastro in-app.
- Minha Lista, comentários por aula, mural de avisos, perfil/config.
- Salvar posição de progresso durante a reprodução.
- Identidade visual definitiva (paleta/nome).
