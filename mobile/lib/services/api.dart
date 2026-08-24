import 'dart:convert';

import 'package:http/http.dart' as http;

import '../config.dart';
import 'auth_store.dart';

/// Cliente da API Comunidade IA.
class Api {
  static Future<Map<String, String>> _headers() async {
    final token = await AuthStore.obterToken();
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  static Future<dynamic> _get(String path) async {
    final resp = await http.get(
      Uri.parse('${Config.baseUrl}$path'),
      headers: await _headers(),
    );
    if (resp.statusCode >= 200 && resp.statusCode < 300) {
      return jsonDecode(resp.body);
    }
    throw ApiException(resp.statusCode, resp.body);
  }

  static Future<dynamic> _post(String path, [Map<String, dynamic>? body]) async {
    final resp = await http.post(
      Uri.parse('${Config.baseUrl}$path'),
      headers: await _headers(),
      body: body == null ? null : jsonEncode(body),
    );
    if (resp.statusCode >= 200 && resp.statusCode < 300) {
      return resp.body.isEmpty ? null : jsonDecode(resp.body);
    }
    throw ApiException(resp.statusCode, resp.body);
  }

  // Autenticacao
  static Future<String> login(String email, String senha) async {
    final data = await _post('/auth/login', {'email': email, 'password': senha});
    return data['token'] as String;
  }

  // Conteudo
  static Future<Map<String, dynamic>> home() async => await _get('/home');
  static Future<List<dynamic>> cursos({String? busca}) async {
    final q = busca != null && busca.isNotEmpty ? '?busca=$busca' : '';
    final data = await _get('/cursos$q');
    return data['data'] as List<dynamic>;
  }

  static Future<Map<String, dynamic>> curso(String slug) async {
    final data = await _get('/cursos/$slug');
    return data['data'] as Map<String, dynamic>;
  }

  static Future<Map<String, dynamic>> aula(int id) async {
    final data = await _get('/aulas/$id');
    return data['data'] as Map<String, dynamic>;
  }

  static Future<void> concluirAula(int id) async => await _post('/aulas/$id/concluir');
  static Future<void> salvarProgresso(int id, int segundos) async =>
      await _post('/aulas/$id/progresso', {'posicao_segundos': segundos});
}

class ApiException implements Exception {
  final int status;
  final String body;
  ApiException(this.status, this.body);

  @override
  String toString() => 'ApiException($status)';
}
