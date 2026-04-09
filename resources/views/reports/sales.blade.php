<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1E3A8A; padding-bottom: 20px; }
        .funnel { margin: 40px auto; width: 80%; }
        .step { padding: 15px; margin-bottom: 5px; color: white; text-align: center; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1E3A8A; color: white; padding: 10px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h2>REPORTE DE VENTAS - PIBLO</h2>
        <p>Inquilino: {{ $tenant->name }}</p>
    </div>

    <div class="funnel">
        <div class="step" style="background: #1E3A8A; width: 100%">VENTAS CERRADAS: ${{ number_format($data['ventas_cerradas'], 0) }}</div>
        <div class="step" style="background: #2563EB; width: 80%">EN NEGOCIACIÓN: ${{ number_format($data['en_proceso'], 0) }}</div>
        <div class="step" style="background: #3B82F6; width: 60%">TASA CONVERSIÓN: {{ $data['tasa_conversion'] }}%</div>
    </div>

    <h3>Métricas de Realización</h3>
    <table>
        <thead>
            <tr><th>Región</th><th>Ventas</th><th>Meta</th></tr>
        </thead>
        <tbody>
            <tr><td>Norte</td><td>$45.000</td><td>$50.000</td></tr>
            <tr><td>Centro</td><td>$30.000</td><td>$30.000</td></tr>
        </tbody>
    </table>
</body>
</html>
