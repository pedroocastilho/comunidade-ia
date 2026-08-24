import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import '../services/api.dart';

class PlayerScreen extends StatefulWidget {
  final int aulaId;
  const PlayerScreen({super.key, required this.aulaId});

  @override
  State<PlayerScreen> createState() => _PlayerScreenState();
}

class _PlayerScreenState extends State<PlayerScreen> {
  Map<String, dynamic>? _aula;
  WebViewController? _webview;
  bool _concluindo = false;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    final aula = await Api.aula(widget.aulaId);
    final url = aula['video_embed_url'] as String?;
    setState(() {
      _aula = aula;
      if (url != null) {
        _webview = WebViewController()
          ..setJavaScriptMode(JavaScriptMode.unrestricted)
          ..loadRequest(Uri.parse(url));
      }
    });
  }

  Future<void> _concluir() async {
    setState(() => _concluindo = true);
    await Api.concluirAula(widget.aulaId);
    setState(() {
      _concluindo = false;
      _aula = {..._aula!, 'concluida': true};
    });
  }

  @override
  Widget build(BuildContext context) {
    final aula = _aula;
    return Scaffold(
      appBar: AppBar(title: Text(aula?['titulo'] ?? 'Aula')),
      body: aula == null
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                AspectRatio(
                  aspectRatio: 16 / 9,
                  child: _webview != null
                      ? WebViewWidget(controller: _webview!)
                      : const ColoredBox(
                          color: Colors.black,
                          child: Center(
                            child: Text('Video indisponivel',
                                style: TextStyle(color: Colors.white)),
                          ),
                        ),
                ),
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      Text(aula['titulo'],
                          style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(aula['descricao'] ?? ''),
                      const SizedBox(height: 20),
                      FilledButton.icon(
                        onPressed: aula['concluida'] == true || _concluindo ? null : _concluir,
                        icon: const Icon(Icons.check),
                        label: Text(aula['concluida'] == true
                            ? 'Concluida'
                            : 'Marcar como concluida'),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}
