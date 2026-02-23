<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quote->number }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $tenant->name ?? 'Tenanta' }}</h1>
    </div>

    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Estimado/a <strong>{{ $client->name }}</strong>,</p>

        <p>Le hacemos llegar la cotización <strong>{{ $quote->number }}</strong> que solicitó.</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2 style="color: #4f46e5; margin-top: 0;">Resumen</h2>

            @if($quote->subject)
            <p><strong>Asunto:</strong> {{ $quote->subject }}</p>
            @endif

            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Fecha:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $quote->created_at->format('d/m/Y') }}</td>
                </tr>
                @if($quote->valid_until)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Válida hasta:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $quote->valid_until->format('d/m/Y') }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 12px 0; font-size: 18px;"><strong>Total:</strong></td>
                    <td style="padding: 12px 0; font-size: 18px; text-align: right; color: #4f46e5;"><strong>${{ number_format($quote->total, 2, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <p>Adjuntamos el documento PDF con el detalle completo de la cotización.</p>

        @if($quote->notes)
        <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <strong>Notas:</strong><br>
            {{ $quote->notes }}
        </div>
        @endif

        <p>Si tiene alguna pregunta, no dude en contactarnos.</p>

        <p>Saludos cordiales,<br>
        <strong>{{ $tenant->name ?? 'Tenanta' }}</strong></p>
    </div>

    <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        <p>Este correo fue enviado automáticamente por Tenanta CRM.</p>
    </div>
</body>
</html>
