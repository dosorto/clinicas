<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago #{{ $numero_recibo }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        .info-box {
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9fafb;
        }
        .grid {
            display: block;
            margin-top: 10px;
        }
        .grid-row {
            display: flex;
            margin-bottom: 5px;
        }
        .grid-row .label {
            flex: 1;
            color: #6b7280;
            font-weight: normal;
        }
        .grid-row .value {
            flex: 1;
            font-weight: bold;
        }
        .amount-box {
            margin-top: 20px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 15px;
            background-color: #f0f9ff;
        }
        .amount-box .concept {
            margin-bottom: 10px;
        }
        .amount-box .concept .label {
            color: #6b7280;
            font-size: 12px;
        }
        .amount-box .concept .value {
            font-weight: bold;
        }
        .amount-total {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding: 10px;
            background-color: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
        }
        .amount-total .amount-text {
            font-weight: bold;
            color: #1e40af;
        }
        .amount-total .amount-number {
            font-size: 16px;
            font-weight: bold;
            color: #1e40af;
        }
        .amount-total .amount-words {
            font-size: 10px;
            color: #6b7280;
        }
        .signatures {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            text-align: center;
            width: 40%;
        }
        .signature-line {
            border-top: 1px solid #d1d5db;
            width: 100%;
            margin: 0 auto;
            margin-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
        }
        .estado {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .estado-completado {
            background-color: #d1fae5;
            color: #047857;
        }
        .estado-parcial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .observations {
            margin-bottom: 20px;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 10px;
            background-color: #f9fafb;
        }
        .row {
            display: flex;
            margin-bottom: 15px;
        }
        .col {
            flex: 1;
            padding: 0 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">RECIBO DE PAGO DE HONORARIOS</div>
        <div class="subtitle">{{ $centro->nombre_centro ?? 'Clínica Médica' }}</div>
    </div>
    
    <div class="row">
        <div class="col">
            <div class="info-box">
                <strong>Información del Pago</strong>
                <div class="grid">
                    <div class="grid-row">
                        <div class="label">Recibo No:</div>
                        <div class="value">{{ $numero_recibo }}</div>
                    </div>
                    <div class="grid-row">
                        <div class="label">Fecha de Pago:</div>
                        <div class="value">{{ $fecha_formateada }}</div>
                    </div>
                    <div class="grid-row">
                        <div class="label">Método de Pago:</div>
                        <div class="value">{{ ucfirst($pago->metodo_pago) }}</div>
                    </div>
                    @if($pago->referencia_pago)
                    <div class="grid-row">
                        <div class="label">Referencia:</div>
                        <div class="value">{{ $pago->referencia_pago }}</div>
                    </div>
                    @endif
                    <div class="grid-row">
                        <div class="label">Estado:</div>
                        <div class="value">
                            <span class="estado {{ $pago->estado == 'completado' ? 'estado-completado' : 'estado-parcial' }}">
                                {{ ucfirst($pago->estado) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="info-box">
                <strong>Información del Médico</strong>
                <div class="grid">
                    <div class="grid-row">
                        <div class="label">Médico:</div>
                        <div class="value">{{ $persona->nombre_completo ?? 'N/A' }}</div>
                    </div>
                    <div class="grid-row">
                        <div class="label">Centro:</div>
                        <div class="value">{{ $centro->nombre_centro ?? 'N/A' }}</div>
                    </div>
                    <div class="grid-row">
                        <div class="label">Cargo ID:</div>
                        <div class="value">{{ $cargoMedico->id ?? 'N/A' }}</div>
                    </div>
                    <div class="grid-row">
                        <div class="label">Liquidación:</div>
                        <div class="value">#{{ $liquidacion->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="amount-box">
        <strong>Detalle del Pago</strong>
        
        <div class="concept">
            <div class="label">Concepto:</div>
            <div class="value">{{ $pago->concepto }}</div>
        </div>
        
        <div class="concept">
            <div class="label">Periodo:</div>
            <div class="value">
                @if($cargoMedico)
                    {{ \Carbon\Carbon::parse($cargoMedico->periodo_inicio)->format('d/m/Y') }} - 
                    {{ \Carbon\Carbon::parse($cargoMedico->periodo_fin)->format('d/m/Y') }}
                @else
                    N/A
                @endif
            </div>
        </div>
        
        <div class="amount-total">
            <div class="amount-text">
                <div class="amount-number">Monto Total:</div>
                <div class="amount-words">{{ $monto_texto }}</div>
            </div>
            <div class="amount-number">L. {{ $monto_formateado }}</div>
        </div>
    </div>
    
    @if($pago->observaciones)
    <div class="observations">
        <strong>Observaciones</strong>
        <p>{{ $pago->observaciones }}</p>
    </div>
    @endif
    
    <div class="signatures">
        <div class="signature">
            <div class="signature-line"></div>
            <div>Firma del Pagador</div>
        </div>
        <div class="signature">
            <div class="signature-line"></div>
            <div>Firma del Médico</div>
        </div>
    </div>
    
    <div class="footer">
        <p>Este recibo fue generado automáticamente el {{ $fecha_generacion }}</p>
        <p>Documento sin validez fiscal - Solo para control interno</p>
    </div>
</body>
</html>
