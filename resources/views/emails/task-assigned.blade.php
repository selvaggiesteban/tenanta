<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Tarea Asignada</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #4f46e5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Nueva Tarea Asignada</h1>
    </div>

    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px;">
        <p>Se te ha asignado una nueva tarea.</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2 style="color: #4f46e5; margin-top: 0;">{{ $task->title }}</h2>

            <table style="width: 100%; border-collapse: collapse;">
                @if($project)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Proyecto:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $project->name }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Prioridad:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">
                        @switch($task->priority)
                            @case('urgent')
                                <span style="color: #dc2626;">Urgente</span>
                                @break
                            @case('high')
                                <span style="color: #ea580c;">Alta</span>
                                @break
                            @case('medium')
                                <span style="color: #ca8a04;">Media</span>
                                @break
                            @default
                                <span style="color: #16a34a;">Baja</span>
                        @endswitch
                    </td>
                </tr>
                @if($task->due_date)
                <tr>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee;"><strong>Fecha límite:</strong></td>
                    <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">{{ $task->due_date->format('d/m/Y') }}</td>
                </tr>
                @endif
                @if($task->estimated_hours)
                <tr>
                    <td style="padding: 8px 0;"><strong>Horas estimadas:</strong></td>
                    <td style="padding: 8px 0; text-align: right;">{{ $task->estimated_hours }}h</td>
                </tr>
                @endif
            </table>
        </div>

        @if($task->description)
        <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <strong>Descripción:</strong><br>
            {!! nl2br(e($task->description)) !!}
        </div>
        @endif

        <p>Inicia sesión en Tenanta para ver más detalles y comenzar a trabajar en esta tarea.</p>

        <p>Saludos cordiales,<br>
        <strong>Tenanta CRM</strong></p>
    </div>

    <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        <p>Este correo fue enviado automáticamente por Tenanta CRM.</p>
    </div>
</body>
</html>
