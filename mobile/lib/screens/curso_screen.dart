import 'package:flutter/material.dart';

import '../services/api.dart';
import 'player_screen.dart';

class CursoScreen extends StatelessWidget {
  final String slug;
  const CursoScreen({super.key, required this.slug});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Curso')),
      body: FutureBuilder<Map<String, dynamic>>(
        future: Api.curso(slug),
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            return const Center(child: Text('Nao foi possivel carregar o curso.'));
          }
          final curso = snap.data!;
          final modulos = (curso['modulos'] as List).cast<Map<String, dynamic>>();
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(curso['titulo'], style: Theme.of(context).textTheme.headlineSmall),
              const SizedBox(height: 8),
              Text(curso['descricao'] ?? ''),
              const SizedBox(height: 8),
              LinearProgressIndicator(value: (curso['progresso_percentual'] ?? 0) / 100),
              const SizedBox(height: 16),
              for (final modulo in modulos) ...[
                Text(modulo['titulo'],
                    style: Theme.of(context).textTheme.titleMedium),
                for (final aula in (modulo['aulas'] as List).cast<Map<String, dynamic>>())
                  ListTile(
                    leading: Icon(
                      aula['concluida'] == true
                          ? Icons.check_circle
                          : Icons.play_circle_outline,
                      color: aula['concluida'] == true ? Colors.green : null,
                    ),
                    title: Text(aula['titulo']),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => PlayerScreen(aulaId: aula['id'])),
                    ),
                  ),
                const SizedBox(height: 12),
              ],
            ],
          );
        },
      ),
    );
  }
}
