import 'package:comunidade_ia/main.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  testWidgets('sem token salvo, o app abre na tela de login', (tester) async {
    SharedPreferences.setMockInitialValues({});

    await tester.pumpWidget(const ComunidadeIaApp());
    await tester.pumpAndSettle();

    // A tela de login mostra o titulo e o botao Entrar.
    expect(find.text('Comunidade IA'), findsWidgets);
    expect(find.text('Entrar'), findsOneWidget);
  });
}
