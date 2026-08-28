<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Perfis ficticios que dao movimento ao Circulo. Nunca fazem login (senha
 * aleatoria descartada, e-mail em dominio interno) e ficam fora das metricas.
 * Idempotente: roda quantas vezes precisar sem duplicar.
 */
class PerfisFicticiosSeeder extends Seeder
{
    /** [nome completo, apelido exibido (null = usa o nome)] */
    private const PERFIS = [
        ['Mariana Lopes', null],
        ['Carla Menezes', 'Carla'],
        ['Tatiane Ribeiro', 'Tati Ribeiro'],
        ['Juliana Martins', 'Ju Martins'],
        ['Fernanda Souza', null],
        ['Patrícia Almeida', 'Paty'],
        ['Renata Cardoso', null],
        ['Bruna Ferreira', 'Bru'],
        ['Aline Costa', null],
        ['Camila Rocha', 'Cami Rocha'],
        ['Débora Nunes', null],
        ['Luciana Pires', 'Lu Pires'],
        ['Vanessa Teixeira', null],
        ['Priscila Duarte', 'Pri Duarte'],
        ['Roberta Lima', null],
        ['Simone Barros', null],
        ['Gabriela Freitas', 'Gabi'],
        ['Amanda Correia', null],
        ['Elaine Moraes', null],
        ['Larissa Gomes', 'Lari Gomes'],
        ['Cristiane Farias', 'Cris Farias'],
        ['Michele Santana', null],
        ['Daniela Azevedo', 'Dani Azevedo'],
        ['Adriana Monteiro', null],
        ['Isabela Carvalho', 'Isa'],
        ['Natália Ramos', null],
        ['Viviane Castro', 'Vivi Castro'],
        ['Andréa Peixoto', null],
        ['Letícia Moura', 'Lê Moura'],
        ['Rosana Vieira', null],
        ['Thiago Andrade', null],
        ['Rafael Siqueira', 'Rafa'],
        ['Marcelo Tavares', null],
        ['Eduardo Fonseca', 'Edu Fonseca'],
        ['Felipe Nascimento', null],
        ['Leonardo Braga', 'Leo Braga'],
        ['André Machado', null],
        ['Gustavo Reis', null],
        ['Rodrigo Campos', 'Rô Campos'],
        ['Paulo Henrique', null],
    ];

    public function run(): void
    {
        foreach (self::PERFIS as $i => [$nome, $apelido]) {
            $email = 'ficticio'.($i + 1).'@circuloaura.local';
            if (User::where('email', $email)->exists()) {
                continue;
            }

            $criadoEm = now()->subDays(random_int(5, 120))->subMinutes(random_int(0, 1440));

            User::forceCreate([
                'name' => $nome,
                'apelido' => $apelido,
                'email' => $email,
                'password' => Str::random(48),
                'role' => 'aluno',
                'tem_acesso' => false,
                'assinatura_status' => 'manual',
                'onboarding_completo_em' => $criadoEm->copy()->addMinutes(random_int(10, 90)),
                'perfil_ficticio' => true,
                'created_at' => $criadoEm,
                'updated_at' => $criadoEm,
            ]);
        }
    }
}
