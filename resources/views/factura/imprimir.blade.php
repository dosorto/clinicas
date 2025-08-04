@php
    // Variables esperadas: $factura, $config
    $medico = $factura->medico;
    $centro = $factura->centro;
    $paciente = $factura->paciente;
    $detalles = $factura->detalles;
@endphp

<div style="font-family: Arial, sans-serif; font-size: 15px; max-width: 900px; margin: 0 auto; background: white; padding: 24px; border: 1px solid #ddd;">
    <div style="display: flex; align-items: center; border-bottom: 2px solid {{ $config->color_primario }}; padding-bottom: 16px; margin-bottom: 18px;">
        @if($config->logo)
            <div style="margin-right: 24px;">
                <img src="{{ asset('storage/' . $config->logo) }}" alt="Logo" style="max-height: 70px; max-width: 120px; object-fit: contain;">
            </div>
        @endif
        <div style="flex: 1;">
            <h2 style="margin: 0; color: {{ $config->color_primario }}; font-size: 22px; font-weight: bold;">{{ $config->razon_social }}</h2>
            <div style="color: {{ $config->color_secundario }}; font-size: 14px; margin-top: 4px;">
                <div><span style="color: #666;">📞</span> {{ $config->telefono }}</div>
                <div><span style="color: #666;">📍</span> {{ $config->direccion }}</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 13px; color: {{ $config->color_secundario }};">{{ $factura->numero_factura }}</div>
        </div>
    </div>

    <div style="margin-bottom: 12px;">
        <strong>Fecha de emisión:</strong> {{ $factura->fecha_emision->format('d/m/Y') }}<br>
        <strong>Paciente:</strong> {{ $paciente->nombre_completo }}<br>
        <strong>Médico:</strong> {{ $medico->persona->primer_nombre }} {{ $medico->persona->primer_apellido }} (Reg. {{ $medico->numero_colegiacion }})<br>
        <strong>Centro:</strong> {{ $centro->nombre_centro }}
    </div>

    @if($config->encabezado)
        <div style="margin-bottom: 18px; color: {{ $config->color_primario }}; font-weight: 600; font-size: 16px;">{!! nl2br(e($config->encabezado)) !!}</div>
    @endif

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 18px;">
        <thead>
            <tr style="background: {{ $config->color_primario }}; color: #fff;">
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Descripción</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Cantidad</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Precio Unitario</th>
                <th style="padding: 8px; border: 1px solid #e5e7eb;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $detalle)
                <tr>
                    <td style="padding: 8px; border: 1px solid #e5e7eb;">{{ $detalle->descripcion }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: center;">{{ $detalle->cantidad }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td style="padding: 8px; border: 1px solid #e5e7eb; text-align: right;">{{ number_format($detalle->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <table style="min-width: 320px;">
            <tr>
                <td style="padding: 6px 8px; color: #333;">Subtotal:</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($factura->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; color: #333;">Descuento:</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($factura->descuento_total, 2) }}</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; color: #333;">Impuesto:</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($factura->impuesto_total, 2) }}</td>
            </tr>
            <tr style="font-weight: bold; color: {{ $config->color_primario }};">
                <td style="padding: 6px 8px;">Total:</td>
                <td style="padding: 6px 8px; text-align: right;">{{ number_format($factura->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($config->pie_pagina)
        <div style="margin-top: 24px; color: {{ $config->color_secundario }}; font-size: 13px; border-top: 1px solid #e5e7eb; padding-top: 12px;">{!! nl2br(e($config->pie_pagina)) !!}</div>
    @endif
</div>
