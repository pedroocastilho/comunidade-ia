<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Primeiro e-mail que o comprador recebe apos a compra aprovada no checkout:
 * boas-vindas + link para criar a senha (token do password broker).
 * Enviado de forma sincrona pelo ProcessadorWebhookPagamento (sem fila,
 * para nao depender do worker no caminho critico de acesso).
 */
class BoasVindasAssinante extends Notification
{
    public function __construct(private string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $primeiroNome = explode(' ', trim($notifiable->name))[0];

        return (new MailMessage)
            ->subject('Seu acesso ao Círculo Aura chegou ✦')
            ->greeting("Olá, {$primeiroNome}!")
            ->line('Sua assinatura do Círculo Aura está ativa. Falta só um passo: criar a sua senha de acesso.')
            ->action('Criar minha senha', $url)
            ->line('Depois é só entrar, responder o questionário inicial e começar sua jornada de 30 dias com o Noah te acompanhando.')
            ->line('Se você não fez esta compra, ignore este e-mail.')
            ->salutation('Até já, ✦ Círculo Aura');
    }
}
