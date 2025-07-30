@php
    $recetario = $medico->recetarios()->latest()->first() ?? null;
    $logo = $recetario?->logo ?? null;
    
    // Debug para ver qué recetario está cargando
    \Log::info('RecetarioPreview - Debug recetario:', [
        'medico_id' => $medico->id,
        'recetario_existe' => $recetario !== null,
        'recetario_id' => $recetario?->id,
        'color_primario_db' => $recetario?->color_primario,
        'color_secundario_db' => $recetario?->color_secundario,
    ]);
    
    // Manejar el logo si es un array
    if (is_array($logo)){
        $logo = reset($logo);
    }
    
    $showLogo = $recetario?->mostrar_logo ?? false;
    $headerColor = $recetario?->color_primario ?? '#1e40af';
    $secondaryColor = $recetario?->color_secundario ?? '#64748b';
    $fontFamily = $recetario?->fuente_familia ?? 'Arial, sans-serif';
    $fontSize = ($recetario?->fuente_tamano ?? 14) . 'px';
    $encabezadoTexto = $recetario?->encabezado_texto ?? 'RECETA MÉDICA';
    $piePagina = $recetario?->pie_pagina ?? 'Este documento es válido únicamente con la firma del médico';
    $textoAdicional = $recetario?->texto_adicional ?? '';
    
    // Debug final de colores procesados
    \Log::info('RecetarioPreview - Colores procesados:', [
        'headerColor' => $headerColor,
        'secondaryColor' => $secondaryColor,
    ]);

    // Función mejorada para manejar rutas de logo
    $logoPath = null;
    $logoExists = false;
    
    if ($logo) {
        // Caso 1: Archivo temporal de Livewire
        if (str_starts_with($logo, 'livewire-tmp/')) {
            try {
                $logoPath = \Livewire\Features\SupportFileUploads\TemporaryUploadedFile::createFromLivewire($logo)->temporaryUrl();
                $logoExists = true;
            } catch (Exception $e) {
                $logoExists = false;
            }
        }
        // Caso 2: Archivo en storage/app/public
        elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($logo)) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->url($logo);
            $logoExists = true;
        }
        // Caso 3: Ruta relativa - intentar con storage/
        elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('storage/', '', $logo))) {
            $logoPath = \Illuminate\Support\Facades\Storage::disk('public')->url(str_replace('storage/', '', $logo));
            $logoExists = true;
        }
        // Caso 4: Archivo en public/storage
        elseif (file_exists(public_path('storage/' . str_replace('storage/', '', $logo)))) {
            $logoPath = asset('storage/' . str_replace('storage/', '', $logo));
            $logoExists = true;
        }
        // Caso 5: URL completa
        elseif (filter_var($logo, FILTER_VALIDATE_URL)) {
            $logoPath = $logo;
            $logoExists = true;
        }
        // Caso 6: Ruta directa en public
        elseif (file_exists(public_path($logo))) {
            $logoPath = asset($logo);
            $logoExists = true;
        }
    }
@endphp

<div class="recetario-preview" style="font-family: {{ $fontFamily }}; font-size: {{ $fontSize }}; width: 100%; max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border: 1px solid #ddd;">
    <!-- Header Principal -->
    <div class="header-principal" style="display: flex; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid {{ $headerColor }};">
        @if($showLogo && $logo)
            <div class="logo-container" style="margin-right: 20px;">
                @if($logoExists && $logoPath)
                    <img src="{{ $logoPath }}" 
                         alt="Logo" 
                         style="max-height: 80px; max-width: 120px; object-fit: contain; border: 1px solid #e5e7eb; border-radius: 4px;"
                         onerror="this.parentElement.innerHTML='<div style=\'height: 80px; width: 120px; background: #fee2e2; border: 1px dashed #ef4444; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #dc2626; text-align: center; padding: 5px;\'>Error cargando imagen</div>'">
                @else
                    <div style="height: 80px; width: 120px; background: #fef3c7; border: 1px dashed #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #d97706; text-align: center; padding: 5px;">
                        Logo no encontrado<br>
                        <small style="font-size: 8px; margin-top: 2px;">{{ Str::limit($logo, 20) }}</small>
                    </div>
                @endif
            </div>
        @endif
        
        <div class="info-header" style="flex: 1;">
            <h2 style="margin: 0; color: {{ $headerColor }}; font-size: 24px; font-weight: bold;">
                {{ $recetario?->titulo ?? $recetario?->titulo_medico ?? 'Dr(a).' }} {{ $recetario?->nombre_mostrar ?? $recetario?->nombre_mostrar_medico ?? (isset($medico->persona) ? trim(($medico->persona->primer_nombre ?? '') . ' ' . ($medico->persona->segundo_nombre ?? '') . ' ' . ($medico->persona->primer_apellido ?? '') . ' ' . ($medico->persona->segundo_apellido ?? '')) : '[Información del médico no disponible]') }}
            </h2>
            <div style="margin-top: 8px; color: {{ $secondaryColor }};">
                @if(isset($medico->especialidades) && $medico->especialidades->count() > 0)
                    <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                        Especialidades: 
                        @foreach($medico->especialidades as $index => $especialidad)
                            {{ $especialidad->especialidad }}@if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif
                @if(!empty($recetario?->telefono_mostrar) || !empty($recetario?->telefonos_medico))
                    <div style="font-size: 13px; color: {{ $secondaryColor }}; margin-top: 2px;">
                        <span style="color: #666;">📞</span> {{ $recetario?->telefono_mostrar ?? $recetario?->telefonos_medico }}
                    </div>
                @endif
            </div>
        </div>
        
        @if(isset($medico->centro) && $medico->centro)
            <div class="centro-info" style="text-align: right; color: {{ $secondaryColor }};">
                <div style="font-weight: 600; color: #333; font-size: 16px;">{{ $medico->centro->nombre_centro ?? 'Centro médico' }}</div>
                @if($medico->centro->direccion && ($recetario?->mostrar_direccion ?? true))
                    <div style="font-size: 11px; margin-top: 4px; line-height: 1.3;">
                        <span style="color: #666;">📍</span> {{ $medico->centro->direccion }}
                    </div>
                @endif
                @if($medico->centro->telefono && ($recetario?->mostrar_telefono ?? true))
                    <div style="font-size: 11px; margin-top: 2px; line-height: 1.3;">
                        <span style="color: #666;">📞</span> {{ $medico->centro->telefono }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- Información del Paciente y Consulta -->
    <div class="patient-section" style="display: flex; gap: 30px; margin-bottom: 25px; padding: 15px; background-color: #f8fafc; border-radius: 8px;">
        <div class="patient-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información del Paciente</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                @if(isset($receta->paciente->persona))
                    @php
                        $nombreCompleto = trim(($receta->paciente->persona->primer_nombre ?? '') . ' ' . ($receta->paciente->persona->segundo_nombre ?? '') . ' ' . ($receta->paciente->persona->primer_apellido ?? '') . ' ' . ($receta->paciente->persona->segundo_apellido ?? ''));
                        $edad = $receta->paciente->persona->fecha_nacimiento ? \Carbon\Carbon::parse($receta->paciente->persona->fecha_nacimiento)->age : null;
                    @endphp
                    <div><strong>Nombre:</strong> {{ $nombreCompleto ?: 'Sin nombre' }}</div>
                    <div style="display: flex; gap: 20px;">
                        <span><strong>Edad:</strong> {{ $edad ?? 'Sin edad' }} años</span>
                        <span><strong>Sexo:</strong> {{ $receta->paciente->persona->sexo ?? 'No especificado' }}</span>
                    </div>
                @else
                    <div><strong>Nombre:</strong> Información no disponible</div>
                    <div><strong>Edad:</strong> Sin información <strong>Sexo:</strong> Sin información</div>
                @endif
            </div>
        </div>
        
        <div class="consult-info" style="flex: 1;">
            <h4 style="margin: 0 0 10px 0; color: {{ $headerColor }}; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Información de la Consulta</h4>
            <div style="display: flex; flex-direction: column; gap: 8px; font-size: 14px;">
                <div><strong>Fecha:</strong> {{ $receta->fecha_receta ? \Carbon\Carbon::parse($receta->fecha_receta)->format('d/m/Y') : now()->format('d/m/Y') }}</div>
                @if(isset($medico->horario_entrada) && isset($medico->horario_salida))
                    <div><strong>Horario:</strong> {{ $medico->horario_entrada }} - {{ $medico->horario_salida }}</div>
                @else
                    <div><strong>Horario:</strong> No especificado</div>
                @endif
            </div>
        </div>
    </div>


    <!-- Receta y Indicaciones en cuadrícula -->
    <div class="receta-grid" style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 20px; border: 2px solid #e5e7eb; border-radius: 10px; padding: 0; background: #fff; margin-bottom: 30px; box-shadow: 0 2px 8px #0001; overflow: hidden;">
        <!-- Prescripción -->
        <div class="prescription-section" style="border-right: 1px solid #e5e7eb; padding: 25px 20px 25px 25px; min-height: 260px;">
            <h4 style="margin: 0 0 15px 0; color: {{ $headerColor }}; border-bottom: 2px solid {{ $headerColor }}; padding-bottom: 8px; font-size: 18px;">
                ℞ {{ $encabezadoTexto }}
            </h4>
            <div class="prescription-content" style="min-height: 180px; padding: 15px; border: 1px dashed #d1d5db; border-radius: 8px; background-color: #f9fafb; line-height: 1.8;">
                @if($receta->medicamentos)
                    <div style="white-space: pre-line; font-size: 15px;">{{ $receta->medicamentos }}</div>
                @else
                    <div style="color: #9ca3af; font-style: italic; text-align: center; padding: 40px 0;">
                        [Espacio para medicamentos y dosificación]
                    </div>
                @endif
            </div>
        </div>

        <!-- Indicaciones -->
        <div class="instructions-section" style="padding: 25px 25px 25px 20px; min-height: 260px;">
            <h4 style="margin: 0 0 15px 0; color: {{ $headerColor }}; border-bottom: 1px solid {{ $headerColor }}; padding-bottom: 5px;">
                Indicaciones Especiales
            </h4>
            <div style="min-height: 180px; padding: 15px; border: 1px dashed #d1d5db; border-radius: 8px; background-color: #fff; font-size: 14px; line-height: 1.6;">
                {{ $receta->indicaciones ?: '[Aquí aparecerán las indicaciones especiales]' }}
            </div>
        </div>
    </div>

    <!-- Texto Adicional del Recetario -->
    @if($textoAdicional)
    <div class="additional-text-section" style="margin-bottom: 25px;">
        <h4 style="margin: 0 0 15px 0; color: {{ $headerColor }}; border-bottom: 1px solid {{ $headerColor }}; padding-bottom: 5px;">
            Información Adicional
        </h4>
        <div style="padding: 15px; background-color: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px; font-size: 14px; line-height: 1.6;">
            {{ $textoAdicional }}
        </div>
    </div>
    @endif

    <!-- Footer con Firma -->
    <div class="footer-section" style="margin-top: 40px; display: flex; justify-content: space-between; align-items: end; border-top: 1px solid #e5e7eb; padding-top: 20px;">
        <div class="signature-area" style="text-align: center; min-width: 250px;">
            <div style="border-top: 1px solid #333; margin-bottom: 5px; width: 250px;"></div>
            <div style="font-weight: 600; font-size: 13px;">
                {{ $recetario?->titulo ?? $recetario?->titulo_medico ?? 'Dr(a).' }} {{ $recetario?->nombre_mostrar ?? $recetario?->nombre_mostrar_medico ?? (isset($medico->persona) ? trim(($medico->persona->primer_nombre ?? '') . ' ' . ($medico->persona->segundo_nombre ?? '') . ' ' . ($medico->persona->primer_apellido ?? '') . ' ' . ($medico->persona->segundo_apellido ?? '')) : '[Firma del médico]') }}
            </div>
            @if(!empty($recetario?->telefono_mostrar) || !empty($recetario?->telefonos_medico))
                <div style="font-size: 12px; color: {{ $secondaryColor }}; margin-top: 2px;">
                    <span style="color: #666;">📞</span> {{ $recetario?->telefono_mostrar ?? $recetario?->telefonos_medico }}
                </div>
            @endif
            @if(isset($medico->numero_colegiacion) && $medico->numero_colegiacion)
                <div style="font-size: 12px; color: {{ $secondaryColor }};">Reg. Médico: {{ $medico->numero_colegiacion }}</div>
            @endif
        </div>
        
        <div class="footer-info" style="text-align: right; font-size: 11px; color: {{ $secondaryColor }};">
            <div>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
            <div>{{ $piePagina }}</div>
        </div>
    </div>
</div>