@php
    // Para la vista previa, usamos los valores del formulario
    $logo = $config['logo'] ?? null;
    if (is_array($logo)){
        $logo = reset($logo);
    }
    $showLogo = $config['mostrar_logo'] ?? false;
    $headerColor = $config['color_primario'] ?? '#1e40af';
    $secondaryColor = $config['color_secundario'] ?? '#64748b';
    $fontFamily = $config['fuente_familia'] ?? 'Arial, sans-serif';
    $fontSize = $config['fuente_tamano'] ?? '14px';
@endphp

<div class="recetario-preview-demo" style="font-family: {{ $fontFamily }}; font-size: {{ $fontSize }}; width: 100%; max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border: 1px solid #ddd;">
    <!-- Header Principal -->
    <div class="header-principal" style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid {{ $headerColor }};">
        @if($showLogo && $logo)
            <div class="logo-container" style="margin-right: 20px;">
                @if(Storage::disk('public')->exists($logo))
                    <img src="{{ Storage::url($logo) }}" alt="Logo" style="max-height: 80px; max-width: 120px;">
                @elseif(file_exists(public_path('storage/' . $logo)))
                    <img src="{{ asset('storage/' . $logo) }}" alt="Logo" style="max-height: 80px; max-width: 120px;">
                @else
                    <div style="max-height: 80px; max-width: 120px; background: #f3f4f6; border: 1px dashed #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666; text-align: center; padding: 10px;">
                        Logo<br>Preview
                    </div>
                @endif
            </div>
        @elseif($showLogo)
            <div class="logo-container" style="margin-right: 20px;">
                <div style="max-height: 80px; max-width: 120px; background: #f3f4f6; border: 1px dashed #d1d5db; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #666; text-align: center; padding: 10px;">
                    Logo<br>Placeholder
                </div>
            </div>
        @endif
        
        <div class="info-header" style="flex: 1;">
            <h2 style="margin: 0; color: {{ $headerColor }}; font-size: 24px; font-weight: bold;">
                Dr(a). [Su Nombre Completo]
            </h2>
            <div style="margin-top: 8px; color: {{ $secondaryColor }};">
                <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                    Especialidades: [Sus Especialidades]
                </div>
            </div>
        </div>
        
        <div class="centro-info" style="text-align: right; color: {{ $secondaryColor }};">
            <div style="font-weight: 600; color: #333; font-size: 16px;">{{ $config['nombre_centro'] ?? '[Nombre del Centro Médico]' }}</div>
            @if($config['mostrar_direccion'] ?? true)
                <div style="font-size: 12px; margin-top: 4px;">
                    <span style="color: #666;">📍</span> {{ $config['direccion'] ?? '[Dirección del Centro]' }}
                </div>
            @endif
            @if($config['mostrar_telefono'] ?? true)
                <div style="font-size: 12px; margin-top: 2px;">
                    <span style="color: #666;">📞</span> {{ $config['telefono'] ?? '[Teléfono del Centro]' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Información del Paciente y Consulta -->
    <div class="patient-section" style="display: flex; gap: 30px; margin-bottom: 25px; padding: 15px; background-color: #f8fafc; border-radius: 8px;">
        <div class="patient-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información del Paciente</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                <div><strong>Nombre:</strong> Juan Pérez López</div>
                <div style="display: flex; gap: 20px;">
                    <span><strong>Edad:</strong> 35 años</span>
                    <span><strong>Sexo:</strong> M</span>
                </div>
            </div>
        </div>
        
        <div class="consult-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información de la Consulta</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                <div><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</div>
                <div><strong>Horario:</strong> [Su Horario de Atención]</div>
            </div>
        </div>
    </div>

    <!-- Prescripción -->
    <div class="prescription-section" style="margin-bottom: 30px;">
        <h4 style="margin: 0 0 15px 0; color: {{ $headerColor }}; border-bottom: 2px solid {{ $headerColor }}; padding-bottom: 8px; font-size: 18px;">
            ℞ PRESCRIPCIÓN MÉDICA
        </h4>
        
        <div class="prescription-content" style="min-height: 200px; padding: 20px; border: 1px solid #d1d5db; border-radius: 8px; background-color: #ffffff; line-height: 1.8;">
            <div style="color: #9ca3af; font-style: italic; text-align: center; padding: 40px 0;">
                [Aquí aparecerán los medicamentos y dosificación]
            </div>
        </div>
    </div>

    <!-- Indicaciones -->
    <div class="instructions-section" style="margin-bottom: 25px;">
        <h4 style="margin: 0 0 15px 0; color: {{ $headerColor }}; border-bottom: 1px solid {{ $headerColor }}; padding-bottom: 5px;">
            Indicaciones Especiales
        </h4>
        <div style="padding: 15px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; font-size: 14px; line-height: 1.6;">
            [Indicaciones especiales para el paciente]
        </div>
    </div>

    <!-- Footer con Firma -->
    <div class="footer-section" style="margin-top: 40px; display: flex; justify-content: space-between; align-items: end; border-top: 1px solid #e5e7eb; padding-top: 20px;">
        <div class="signature-area" style="text-align: center; min-width: 250px;">
            <div style="border-top: 1px solid #333; margin-bottom: 5px; width: 250px;"></div>
            <div style="font-weight: 600; font-size: 13px;">Dr(a). [Su Nombre Completo]</div>
            <div style="font-size: 12px; color: {{ $secondaryColor }};">Reg. Médico: [Su Número de Colegiación]</div>
        </div>
        
        <div class="footer-info" style="text-align: right; font-size: 11px; color: {{ $secondaryColor }};">
            <div>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Este documento es válido únicamente con la firma del médico</div>
        </div>
    </div>
</div>
