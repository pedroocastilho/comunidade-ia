import 'package:flutter/material.dart';

import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'services/auth_store.dart';

void main() {
  runApp(const ComunidadeIaApp());
}

class ComunidadeIaApp extends StatelessWidget {
  const ComunidadeIaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Comunidade IA',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorSchemeSeed: const Color(0xFF059669),
        useMaterial3: true,
      ),
      home: const _Gate(),
    );
  }
}

/// Decide a tela inicial conforme haja token salvo.
class _Gate extends StatefulWidget {
  const _Gate();

  @override
  State<_Gate> createState() => _GateState();
}

class _GateState extends State<_Gate> {
  Future<String?>? _token;

  @override
  void initState() {
    super.initState();
    _token = AuthStore.obterToken();
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<String?>(
      future: _token,
      builder: (context, snap) {
        if (snap.connectionState != ConnectionState.done) {
          return const Scaffold(body: Center(child: CircularProgressIndicator()));
        }
        return snap.data != null ? const HomeScreen() : const LoginScreen();
      },
    );
  }
}
