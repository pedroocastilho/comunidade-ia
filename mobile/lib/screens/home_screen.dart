import 'package:flutter/material.dart';

import '../services/api.dart';
import '../services/auth_store.dart';
import 'curso_screen.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _tab = 0;

  Future<void> _sair() async {
    await AuthStore.limpar();
    if (mounted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final telas = [const _InicioTab(), const _CursosTab()];

    return Scaffold(
      appBar: AppBar(
        title: const Text('Comunidade IA'),
        actions: [IconButton(onPressed: _sair, icon: const Icon(Icons.logout))],
      ),
      body: telas[_tab],
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() => _tab = i),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.home_outlined), label: 'Inicio'),
          NavigationDestination(icon: Icon(Icons.grid_view_outlined), label: 'Cursos'),
        ],
      ),
    );
  }
}

class _InicioTab extends StatelessWidget {
  const _InicioTab();

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<Map<String, dynamic>>(
      future: Api.home(),
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snap.hasError) {
          return const Center(child: Text('Nao foi possivel carregar.'));
        }
        final home = snap.data!;
        final trilhas = (home['trilhas'] as List).cast<Map<String, dynamic>>();
        return ListView(
          padding: const EdgeInsets.all(16),
          children: [
            for (final trilha in trilhas) ...[
              Text(trilha['categoria']['nome'],
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              SizedBox(
                height: 150,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: [
                    for (final curso in (trilha['cursos']['data'] as List))
                      _CursoCard(curso: curso as Map<String, dynamic>),
                  ],
                ),
              ),
              const SizedBox(height: 20),
            ],
          ],
        );
      },
    );
  }
}

class _CursosTab extends StatelessWidget {
  const _CursosTab();

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<dynamic>>(
      future: Api.cursos(),
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Center(child: CircularProgressIndicator());
        }
        final cursos = snap.data ?? [];
        return GridView.count(
          crossAxisCount: 2,
          padding: const EdgeInsets.all(16),
          childAspectRatio: 0.8,
          mainAxisSpacing: 12,
          crossAxisSpacing: 12,
          children: [
            for (final curso in cursos) _CursoCard(curso: curso as Map<String, dynamic>),
          ],
        );
      },
    );
  }
}

class _CursoCard extends StatelessWidget {
  final Map<String, dynamic> curso;
  const _CursoCard({required this.curso});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => CursoScreen(slug: curso['slug'])),
      ),
      child: Container(
        width: 160,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          color: Colors.green.shade50,
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: curso['capa_url'] != null
                  ? Image.network(curso['capa_url'], fit: BoxFit.cover)
                  : Container(color: Colors.green.shade100),
            ),
            Padding(
              padding: const EdgeInsets.all(8),
              child: Text(curso['titulo'],
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
          ],
        ),
      ),
    );
  }
}
