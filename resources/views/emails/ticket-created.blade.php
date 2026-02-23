<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $ticket->number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Ticket de Soporte</h1>
    </div>

    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Su ticket de soporte ha sido creado exitosamente.</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Número:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $ticket->number }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Asunto:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $ticket->subject }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Prioridad:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ ucfirst($ticket->priority) }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0;"><strong>Estado:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ ucfirst($ticket->status) }}</td>
                </tr>
            </table>
        </div>

        <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <strong>Descripción:</strong><br>
            {{ $ticket->description }}
        </div>

        <p>Nuestro equipo de soporte revisará su solicitud y le responderá a la brevedad.</p>

        <p>Saludos cordiales,<br>
        <strong>Equipo de Soporte</strong></p>
    </div>

    <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        <p>Este correo fue enviado automáticamente por Tenanta CRM.</p>
    </div>
</body>
</html>
