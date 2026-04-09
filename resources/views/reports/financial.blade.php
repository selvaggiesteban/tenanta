<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid {{ $colors['primary'] }}; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: {{ $colors['primary'] }}; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .kpi-grid { width: 100%; margin-bottom: 30px; }
        .kpi-card { background: #f9f9f9; padding: 15px; border-radius: 8px; text-align: center; width: 23%; display: inline-block; margin-right: 1%; }
        .kpi-value { font-size: 18px; font-weight: bold; color: {{ $colors['primary'] }}; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: {{ $colors['primary'] }}; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .watermark { position: fixed; top: 40%; left: 20%; font-size: 100px; color: rgba(0,0,0,0.03); transform: rotate(-45deg); z-index: -1; }
    </style>
</head>
<body>
    <div class="watermark">{{ $tenant->name }}</div>
    
    <div class="header">
        <div class="logo">TENANTA - REPORTING</div>
        <p>Reporte de Salud Financiera: {{ $tenant->name }}</p>
        <p>Fecha: {{ $date }}</p>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <p style="font-size: 10px; margin: 0;">INGRESOS</p>
            <div class="kpi-value">${{ number_format($data['ingresos_totales'], 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <p style="font-size: 10px; margin: 0;">GASTOS</p>
            <div class="kpi-value">${{ number_format($data['gastos_estimados'], 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <p style="font-size: 10px; margin: 0;">BENEFICIO</p>
            <div class="kpi-value">${{ number_format($data['beneficio_neto'], 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card" style="margin-right: 0;">
            <p style="font-size: 10px; margin: 0;">MARGEN</p>
            <div class="kpi-value">{{ $data['margen_operativo'] }}%</div>
        </div>
    </div>

    <h3>Desglose de Movimientos Recientes</h3>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['cuentas'] as $cuenta)
            <tr>
                <td>{{ $cuenta['title'] }}</td>
                <td>${{ number_format($cuenta['total'], 0, ',', '.') }}</td>
                <td>Aceptado</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Este documento es confidencial y generado automáticamente por Tenanta Platform.<br>
        Localización LATAM - es_AR
    </div>
</body>
</html>
