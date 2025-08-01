<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nómina Médica</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin-bottom: 5px;
            color: #333;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        .info-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }
        .info-box p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 20px;
            border-top: 2px solid #333;
            padding-top: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .signature {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            border-top: 1px solid #333;
            width: 200px;
            padding-top: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NÓMINA MÉDICA</h1>
        @if($centro_medico)
        <p><strong>{{ $centro_medico->nombre_centro }}</strong></p>
        <p>{{ $centro_medico->direccion ?? 'Centro Médico' }}</p>
        @endif
        <p>Periodo: {{ $periodo_inicio }} - {{ $periodo_fin }}</p>
        <p>Fecha de generación: {{ $fecha_generacion }}</p>
    </div>
    
    <div class="info-box">
        @if($centro_medico)
        <p><strong>Centro médico:</strong> {{ $centro_medico->nombre_centro }}</p>
        @endif
        <p><strong>Total general:</strong> L. {{ number_format($total_general, 2) }}</p>
        <p><strong>Total retenciones:</strong> L. {{ number_format($total_retenciones, 2) }}</p>
        <p><strong>Total neto pagado:</strong> L. {{ number_format($total_neto, 2) }}</p>
        <p><strong>Total médicos:</strong> {{ count($resultados) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Médico</th>
                <th>Especialidad</th>
                <th>Centro</th>
                <th>% Médico</th>
                <th>Total Honorarios</th>
                <th>Retenciones</th>
                <th>Neto a Pagar</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($resultados as $resultado)
            <tr>
                <td>{{ $resultado['nombre_medico'] }}</td>
                <td>{{ $resultado['especialidad'] }}</td>
                <td>{{ $resultado['centro'] }}</td>
                <td>{{ $resultado['porcentaje_medico'] }}%</td>
                <td>L. {{ number_format($resultado['total_pagado'] + ($resultado['salario_periodo'] ?? 0), 2) }}</td>
                <td>L. {{ number_format($resultado['total_retenciones'], 2) }}</td>
                <td>L. {{ number_format($resultado['total_con_salario'] ?? $resultado['monto_neto'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; font-style: italic; color: #666;">
                    No hay datos de nómina para el período seleccionado
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @foreach ($resultados as $resultado)
    <div style="page-break-before: always;">
        <h2>Detalle de Honorarios - {{ $resultado['nombre_medico'] }}</h2>
        <div class="info-box">
            <p><strong>Médico:</strong> {{ $resultado['nombre_medico'] }}</p>
            <p><strong>Especialidad:</strong> {{ $resultado['especialidad'] }}</p>
            <p><strong>Centro médico:</strong> {{ $resultado['centro'] }}</p>
            <p><strong>Periodo:</strong> {{ $periodo_inicio }} - {{ $periodo_fin }}</p>
            <p><strong>Porcentaje de honorarios:</strong> {{ $resultado['porcentaje_medico'] }}%</p>
            <p><strong>Número de colegiación:</strong> {{ $resultado['medico']->numero_colegiacion ?? 'No disponible' }}</p>
            @if($resultado['contrato'])
            <p><strong>Contrato:</strong> #{{ $resultado['contrato']->id }} ({{ $resultado['contrato']->fecha_inicio }} - {{ $resultado['contrato']->fecha_fin ?? 'Vigente' }})</p>
            <p><strong>Salario mensual:</strong> L. {{ number_format($resultado['salario_base'], 2) }}</p>
            <p><strong>Salario del período:</strong> L. {{ number_format($resultado['salario_periodo'] ?? 0, 2) }}</p>
            @else
            <p style="color: red;"><strong>Estado:</strong> {{ $resultado['mensaje'] ?? 'Sin contrato vigente' }}</p>
            @endif
        </div>
        
        <h3>Liquidaciones del periodo</h3>
        @if($resultado['liquidaciones']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Monto Total</th>
                    <th>Monto Médico</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultado['liquidaciones'] as $liquidacion)
                <tr>
                    <td>{{ $liquidacion->id }}</td>
                    <td>{{ $liquidacion->created_at->format('d/m/Y') }}</td>
                    <td>{{ $liquidacion->cargoMedico ? $liquidacion->cargoMedico->descripcion : 'Liquidación #' . $liquidacion->id }}</td>
                    <td>L. {{ number_format($liquidacion->monto_total, 2) }}</td>
                    <td>L. {{ number_format($liquidacion->monto_total, 2) }}</td>
                    <td>{{ ucfirst($liquidacion->estado) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="info-box">
            <p style="text-align: center; font-style: italic; color: #666;">
                No hay liquidaciones registradas para este período
            </p>
        </div>
        @endif
        
        <h3>Pagos realizados</h3>
        @if($resultado['pagos']->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Retención</th>
                    <th>Neto</th>
                    <th>Método</th>
                    <th>Referencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($resultado['pagos'] as $pago)
                <tr>
                    <td>{{ $pago->id }}</td>
                    <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                    <td>L. {{ number_format($pago->monto, 2) }}</td>
                    <td>L. {{ number_format($pago->retencion_isr_monto, 2) }}</td>
                    <td>L. {{ number_format($pago->monto - $pago->retencion_isr_monto, 2) }}</td>
                    <td>{{ ucfirst($pago->metodo_pago) }}</td>
                    <td>{{ $pago->referencia_pago }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="info-box">
            <p style="text-align: center; font-style: italic; color: #666;">
                No hay pagos registrados para este período
            </p>
        </div>
        @endif
        
        <div class="summary">
            <p><strong>Salario base del período:</strong> L. {{ number_format($resultado['salario_periodo'] ?? 0, 2) }}</p>
            <p><strong>Total liquidaciones:</strong> L. {{ number_format($resultado['total_liquidaciones'], 2) }}</p>
            <p><strong>Total pagado (servicios):</strong> L. {{ number_format($resultado['total_pagado'], 2) }}</p>
            <p><strong>Total retenciones:</strong> L. {{ number_format($resultado['total_retenciones'], 2) }}</p>
            <p><strong>Neto servicios:</strong> L. {{ number_format($resultado['monto_neto'], 2) }}</p>
            <p><strong>Total neto (salario + servicios):</strong> L. {{ number_format($resultado['total_con_salario'] ?? $resultado['monto_neto'], 2) }}</p>
            <p><strong>Pendiente de pago:</strong> L. {{ number_format($resultado['total_pendiente'], 2) }}</p>
            @if($resultado['mensaje'])
            <p style="color: orange; font-style: italic;"><strong>Nota:</strong> {{ $resultado['mensaje'] }}</p>
            @endif
        </div>
        
        <div class="signature">
            <div class="signature-box">
                Firma del Médico
            </div>
            <div class="signature-box">
                Autorización
            </div>
        </div>
    </div>
    @endforeach
    
    <div class="footer">
        <p>Documento generado automáticamente por el sistema de Contabilidad Médica.</p>
        <p>Este documento no tiene validez fiscal sin sello y firma autorizada.</p>
    </div>
</body>
</html>
