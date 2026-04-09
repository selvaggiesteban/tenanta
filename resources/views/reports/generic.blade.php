<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; padding: 20px; }
        .header { border-bottom: 2px solid #673DE6; padding-bottom: 10px; margin-bottom: 20px; }
        .footer { position: fixed; bottom: 0; font-size: 10px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE OPERATIVO - {{ $tenant->name }}</h1>
        <p>Sección: {{ $type }} | Fecha: {{ $date }}</p>
    </div>

    <div class="content">
        <p>Resumen de indicadores para la unidad de negocio seleccionada.</p>
        <pre>{{ json_encode($data, JSON_PRETTY_PRINT) }}</pre>
    </div>

    <div class="footer">Generado por Tenanta Platform - Localización LATAM</div>
</body>
</html>
