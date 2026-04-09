<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Password;

class WelcomeAndActivateNotification extends Notification
{
    use Queueable;

    public function __construct() {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $token = Password::createToken($notifiable);
        $url = url("/definir-contrasena?token={$token}&email=" . urlencode($notifiable->email));

        return (new MailMessage)
            ->subject('¡Bienvenido a Tenanta! Activa tu cuenta de Inquilino')
            ->greeting("Hola {$notifiable->name},")
            ->line('Tu cuenta de negocio ha sido creada exitosamente mediante la importación masiva.')
            ->line('Por seguridad, es obligatorio que confirmes tu email y definas tu contraseña en las próximas 48 horas.')
            ->action('Definir mi Contraseña', $url)
            ->line('Si no realizas esta acción en 2 días, tu cuenta será suspendida automáticamente.')
            ->line('¡Gracias por confiar en Tenanta!');
    }
}
