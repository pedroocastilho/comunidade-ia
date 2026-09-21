<?php

/*
 * Configuracoes de negocio do Circulo Aura.
 */
return [

    // Cadastro publico (fora do checkout). Fechado por padrao: conta de membro
    // nasce pelo webhook de pagamento; admin cria manualmente pelo Filament.
    'cadastro_aberto' => env('CADASTRO_ABERTO', false),

    // Pagina de vendas/checkout usada nos CTAs (tela sem acesso, e-mails).
    'checkout_url' => env('CHECKOUT_URL'),

];
