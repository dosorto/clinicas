<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nómina del mes de {{ $mesNombre }} {{ $nomina->año }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 12px;
        }
        .info-table .label {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 30%;
        }
        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .employees-table th,
        .employees-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .employees-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
        }
        .employees-table .number-cell {
            text-align: right;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .status {
            color: #d32f2f;
            font-weight: bold;
        }
        .signatures {
            margin-top: 50px;
            margin-bottom: 30px;
            width: 100%;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-cell {
            width: 33.33%;
            text-align: center;
            padding: 40px 10px 10px 10px;
            border-bottom: 1px solid #333;
            font-size: 11px;
        }
        .signature-label {
            margin-top: 10px;
            font-weight: bold;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $nomina->empresa }}</div>
        <div class="company-info">Colonia Palmira, Tegucigalpa</div>
        <div class="company-info">Teléfono: 2233-4455</div>
        <div class="title">Nómina del mes de {{ $mesNombre }} {{ $nomina->año }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Fecha de generación</td>
                <td>{{ $fechaGeneracion }}</td>
                <td class="label">Período</td>
                <td>{{ $mesNombre }} {{ $nomina->año }}</td>
            </tr>
            <tr>
                <td class="label">Tipo de Pago</td>
                <td>{{ ucfirst($nomina->tipo_pago) }}</td>
                <td class="label">Estado</td>
                <td class="status">{{ $nomina->cerrada ? 'Cerrada' : 'Abierta' }}</td>
            </tr>
        </table>
    </div>

    <div class="title">Detalle de Médicos</div>
    
    <table class="employees-table">
        <thead>
            <tr>
                <th>Médico</th>
                <th>Salario</th>
                <th>Deducciones</th>
                <th>Percepciones</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($empleados as $medico)
            <tr>
                <td>{{ $medico['nombre'] }}</td>
                <td class="number-cell">L. {{ number_format($medico['salario'], 2) }}</td>
                <td class="number-cell">
                    @if($medico['deducciones'] > 0)
                        Total: L. {{ number_format($medico['deducciones'], 2) }}
                    @else
                        Total: L. 0.00
                    @endif
                </td>
                <td class="number-cell">
                    @if($medico['percepciones'] > 0)
                        Total: L. {{ number_format($medico['percepciones'], 2) }}
                    @else
                        Total: L. 0.00
                    @endif
                </td>
                <td class="number-cell">L. {{ number_format($medico['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right; font-weight: bold;">TOTAL NÓMINA:</td>
                <td class="number-cell" style="font-weight: bold;">L. {{ number_format($totalNomina, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="signatures">
        <table class="signature-table">
            <tr>
                <td class="signature-cell">
                    <div class="signature-label">Elaborado por</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-label">Revisado por</div>
                </td>
                <td class="signature-cell">
                    <div class="signature-label">Autorizado por</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ $fechaGeneracion }}</p>
        <p>Sistema de Gestión de Clínicas Médicas</p>
    </div>
</body>
</html>
