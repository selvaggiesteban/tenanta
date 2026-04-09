<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #673DE6; padding-bottom: 20px; }
        .score-circle { width: 100px; height: 100px; border-radius: 50%; background: #673DE6; color: white; line-height: 100px; text-align: center; font-size: 30px; font-weight: bold; margin: 20px auto; }
        .rec { border-left: 4px solid #F59E0B; padding: 10px; background: #FFFBEB; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>AUDITORÍA SEO TÉCNICA</h2>
        <p>Sitio: {{ $tenant->slug }}.tenanta.com</p>
    </div>

    <div class="score-circle">{{ $data['puntaje_global'] }}</div>
    <p style="text-align: center; font-weight: bold;">PUNTAJE DE SALUD WEB</p>

    <h3>Recomendaciones de Mejora</h3>
    @foreach($data['metadatos']['recomendaciones'] as $rec)
        <div class="rec">{{ $rec }}</div>
    @endforeach

    @foreach($data['estructura_on_page']['recomendaciones'] as $rec)
        <div class="rec">{{ $rec }}</div>
    @endforeach
</body>
</html>
