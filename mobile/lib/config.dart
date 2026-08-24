/// Configuracao do app.
///
/// baseUrl aponta para a API Laravel. No emulador Android, o host da maquina
/// e acessado por 10.0.2.2. Em producao, troque pela URL publica da API.
class Config {
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';
}
