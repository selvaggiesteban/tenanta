<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Cotización {{ $quote->number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 20px;
        }

        .company-info {
            float: left;
            width: 50%;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 5px;
        }

        .quote-info {
            float: right;
            width: 45%;
            text-align: right;
        }

        .quote-number {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            margin-bottom: 10px;
        }

        .quote-meta {
            font-size: 11px;
            color: #666;
        }

        .clear {
            clear: both;
        }

        .addresses {
            margin: 30px 0;
        }

        .address-box {
            float: left;
            width: 48%;
        }

        .address-box.right {
            float: right;
        }

        .address-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .address-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .client-name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }

        th {
            background: #4f46e5;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        th:last-child {
            text-align: right;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        td:last-child {
            text-align: right;
        }

        .item-description {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .totals {
            float: right;
            width: 300px;
            margin-top: 20px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .totals-row.total {
            font-size: 16px;
            font-weight: bold;
            color: #4f46e5;
            border-bottom: none;
            border-top: 2px solid #4f46e5;
            padding-top: 15px;
            margin-top: 10px;
        }

        .totals-label {
            float: left;
        }

        .totals-value {
            float: right;
        }

        .notes {
            clear: both;
            margin-top: 40px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .terms {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }

        .terms-title {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-expired { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $tenant->name ?? 'Tenanta' }}</div>
                <div>{{ $tenant->address ?? '' }}</div>
                <div>{{ $tenant->phone ?? '' }}</div>
                <div>{{ $tenant->email ?? '' }}</div>
            </div>
            <div class="quote-info">
                <div class="quote-number">COTIZACIÓN</div>
                <div class="quote-meta">
                    <div><strong>N°:</strong> {{ $quote->number }}</div>
                    <div><strong>Fecha:</strong> {{ $quote->created_at->format('d/m/Y') }}</div>
                    @if($quote->valid_until)
                    <div><strong>Válida hasta:</strong> {{ $quote->valid_until->format('d/m/Y') }}</div>
                    @endif
                    <div style="margin-top: 10px;">
                        <span class="status-badge status-{{ $quote->status }}">
                            {{ ucfirst($quote->status) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="addresses">
            <div class="address-box">
                <div class="address-label">De</div>
                <div class="address-content">
                    <div class="client-name">{{ $tenant->name ?? 'Tenanta' }}</div>
                    <div>{{ $tenant->address ?? '' }}</div>
                </div>
            </div>
            <div class="address-box right">
                <div class="address-label">Para</div>
                <div class="address-content">
                    <div class="client-name">{{ $client->name ?? 'Cliente' }}</div>
                    @if($client->company)
                    <div>{{ $client->company }}</div>
                    @endif
                    <div>{{ $client->email ?? '' }}</div>
                    <div>{{ $client->phone ?? '' }}</div>
                    @if($client->address)
                    <div>{{ $client->address }}</div>
                    @endif
                </div>
            </div>
            <div class="clear"></div>
        </div>

        @if($quote->subject)
        <div style="margin-bottom: 20px;">
            <strong>Asunto:</strong> {{ $quote->subject }}
        </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width: 50%">Descripción</th>
                    <th style="width: 15%; text-align: center;">Cantidad</th>
                    <th style="width: 15%; text-align: right;">Precio Unit.</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->name }}</strong>
                        @if($item->description)
                        <div class="item-description">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                    <td style="text-align: right;">${{ number_format($item->total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <span class="totals-label">Subtotal</span>
                <span class="totals-value">${{ number_format($quote->subtotal, 2, ',', '.') }}</span>
                <div class="clear"></div>
            </div>
            @if($quote->discount > 0)
            <div class="totals-row">
                <span class="totals-label">Descuento ({{ $quote->discount_type === 'percentage' ? $quote->discount . '%' : '$' . number_format($quote->discount, 2, ',', '.') }})</span>
                <span class="totals-value">-${{ number_format($quote->discount_amount ?? 0, 2, ',', '.') }}</span>
                <div class="clear"></div>
            </div>
            @endif
            @if($quote->tax > 0)
            <div class="totals-row">
                <span class="totals-label">IVA ({{ $quote->tax }}%)</span>
                <span class="totals-value">${{ number_format($quote->tax_amount ?? 0, 2, ',', '.') }}</span>
                <div class="clear"></div>
            </div>
            @endif
            <div class="totals-row total">
                <span class="totals-label">TOTAL</span>
                <span class="totals-value">${{ number_format($quote->total, 2, ',', '.') }}</span>
                <div class="clear"></div>
            </div>
        </div>

        <div class="clear"></div>

        @if($quote->notes)
        <div class="notes">
            <div class="notes-title">Notas</div>
            <div>{!! nl2br(e($quote->notes)) !!}</div>
        </div>
        @endif

        @if($quote->terms)
        <div class="terms">
            <div class="terms-title">Términos y Condiciones</div>
            <div>{!! nl2br(e($quote->terms)) !!}</div>
        </div>
        @endif

        <div class="footer">
            Cotización generada por Tenanta CRM | {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
