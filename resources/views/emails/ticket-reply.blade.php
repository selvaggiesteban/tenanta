<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re: Ticket {{ $ticket->number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Nueva Respuesta en su Ticket</h1>
    </div>

    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Hay una nueva respuesta en su ticket de soporte.</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0;"><strong>Ticket:</strong> {{ $ticket->number }}</p>
            <p style="margin: 0;"><strong>Asunto:</strong> {{ $ticket->subject }}</p>
        </div>

        <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                <strong>{{ $reply->user->name }}</strong> escribió:
            </p>
            {!! nl2br(e($reply->content)) !!}
        </div>

        <p>Puede responder a este ticket iniciando sesión en su cuenta.</p>

        <p>Saludos cordiales,<br>
        <strong>Equipo de Soporte</strong></p>
    </div>

    <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        <p>Este correo fue enviado automáticamente por Tenanta CRM.</p>
    </div>
</body>
</html>
