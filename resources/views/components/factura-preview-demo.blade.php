@php
    $logo = $config['logo'] ?? null;
    if (is_array($logo) && !empty($logo)) {
        $logo = reset($logo);
    }
    $showLogo = !empty($logo);
    $headerColor = $config['color_primario'] ?? '#2563eb';
    $secondaryColor = $config['color_secundario'] ?? '#64748b';
    $razonSocial = $config['razon_social'] ?? '[Razón Social]';
    $telefono = $config['telefono'] ?? '[Teléfono]';
    $direccion = $config['direccion'] ?? '[Dirección]';
    $encabezado = $config['encabezado'] ?? '';
    $piePagina = $config['pie_pagina'] ?? '';
    $formatoNumeracion = $config['formato_numeracion'] ?? 'FAC-00001';
@endphp


<div class="factura-preview-demo" style="font-family: Arial, sans-serif; font-size: 15px; width: 100%; max-width: 800px; margin: 0 auto; background: white; padding: 24px; border: 1px solid #ddd;">
    <div style="display: flex; align-items: center; border-bottom: 2px solid {{ $headerColor }}; padding-bottom: 16px; margin-bottom: 18px;">
        @if($showLogo)
            <div style="margin-right: 24px;">
                <img src="{{ $logo }}" alt="Logo" style="max-height: 70px; max-width: 120px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 4px;">
            </div>
        @endif
        <div style="flex: 1;">
            <h2 style="margin: 0; color: {{ $headerColor }}; font-size: 22px; font-weight: bold;">{{ $razonSocial }}</h2>
            <div style="color: {{ $secondaryColor }}; font-size: 14px; margin-top: 4px;">
                <div><span style="color: #666;">📞</span> {{ $telefono }}</div>
                <div><span style="color: #666;">📍</span> {{ $direccion }}</div>
            </div>
        </div>
        <div style="text-align: right;">
            <div style="font-size: 13px; color: {{ $secondaryColor }};">{{ $formatoNumeracion }}</div>
        </div>
    </div>

    <!-- Información precargada de médico, paciente y centro -->
    @php
        // Simulación de datos si no existen
        $medico = $config['medico'] ?? (object)[
            'persona' => (object)[
                'primer_nombre' => 'Juan',
                'primer_apellido' => 'Pérez',
                'telefono' => '9999-9999',
            ],
            'numero_colegiacion' => '12345',
        ];
        $centro = $config['centro'] ?? (object)[
            'nombre_centro' => 'Centro Médico Demo',
            'direccion' => 'Dirección Demo',
            'telefono' => '8888-8888',
        ];
        $paciente = $config['paciente'] ?? (object)[
            'nombre_completo' => 'Paciente Ejemplo',
            'telefono' => '7777-7777',
        ];
    @endphp
    <div style="margin-bottom: 12px;">
        <strong>Médico:</strong> {{ $medico->persona->primer_nombre }} {{ $medico->persona->primer_apellido }} (Reg. {{ $medico->numero_colegiacion }})<br>
        <strong>Centro:</strong> {{ $centro->nombre_centro }}<br>
        <strong>Paciente:</strong> {{ $paciente->nombre_completo }}<br>
        <strong>Tel. Paciente:</strong> {{ $paciente->telefono }}
    </div>

    @if($encabezado)
        <div style="margin-bottom: 18px; color: {{ $headerColor }}; font-weight: 600; font-size: 16px;">{!! nl2br(e($encabezado)) !!}</div>
    @endif

    <div style="margin-bottom: 24px;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: {{ $headerColor }}; color: #fff;">
                    <th style="padding: 8px; border: 1px solid #131518ff;">Descripción</th>
                    <th style="padding: 8px; border: 1px solid #131518ff;">Cantidad</th>
                    <th style="padding: 8px; border: 1px solid #131518ff;">Precio Unitario</th>
                    <th style="padding: 8px; border: 1px solid #131518ff;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 8px; border: 1px solid #131518ff;">[Producto o Servicio]</td>
                    <td style="padding: 8px; border: 1px solid #131518ff; text-align: center;">1</td>
                    <td style="padding: 8px; border: 1px solid #131518ff; text-align: right;">0.00</td>
                    <td style="padding: 8px; border: 1px solid #131518ff; text-align: right;">0.00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 18px;">
        <table style="min-width: 320px;">
            <tr>
                <td style="padding: 6px 8px; color: #333;">Subtotal:</td>
                <td style="padding: 6px 8px; text-align: right;">0.00</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; color: #333;">Descuento:</td>
                <td style="padding: 6px 8px; text-align: right;">0.00</td>
            </tr>
            <tr>
                <td style="padding: 6px 8px; color: #333;">Impuesto:</td>
                <td style="padding: 6px 8px; text-align: right;">0.00</td>
            </tr>
            <tr style="font-weight: bold; color: {{ $headerColor }};">
                <td style="padding: 6px 8px;">Total:</td>
                <td style="padding: 6px 8px; text-align: right;">0.00</td>
            </tr>
        </table>
    </div>

    @if($piePagina)
        <div style="margin-top: 24px; color: {{ $secondaryColor }}; font-size: 13px; border-top: 1px solid #e5e7eb; padding-top: 12px;">{!! nl2br(e($piePagina)) !!}</div>
    @endif
</div>
