<?php

namespace App\Notifications;

use App\Http\Middleware\SenhaPadraoDefinida;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Primeiro e-mail que o comprador recebe apos a compra aprovada no checkout:
 * boas-vindas + como entrar (senha padrao; o primeiro login obriga a trocar).
 * Enviado de forma sincrona pelo ProcessadorWebhookPagamento (sem fila,
 * para nao depender do worker no caminho critico de acesso).
 */
class BoasVindasAssinante extends Notification
{
    public function toMail(object $notifiable): MailMessage
    {
        $primeiroNome = explode(' ', trim($notifiable->name))[0];

        return (new MailMessage)
            ->subject('Seu acesso ao Círculo Aura chegou ✦')
            ->greeting("Olá, {$primeiroNome}!")
            ->line('Sua assinatura do Círculo Aura está ativa. Para entrar:')
            ->line('**E-mail:** '.$notifiable->email)
            ->line('**Senha provisória:** '.SenhaPadraoDefinida::SENHA_PADRAO)
            ->action('Acessar o Círculo Aura', url('/login'))
            ->line('No primeiro acesso você cria a sua senha pessoal, responde o questionário inicial e começa sua jornada de 30 dias com o Noah te acompanhando.')
            ->line('Se você não fez esta compra, ignore este e-mail.')
            ->salutation('Até já, ✦ Círculo Aura');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }
}
