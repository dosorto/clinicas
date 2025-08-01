<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Automatización de Contabilidad Médica
    |--------------------------------------------------------------------------
    |
    | Este archivo define las configuraciones para la automatización de procesos
    | relacionados con la contabilidad médica, como la generación de cargos,
    | liquidaciones y pagos.
    |
    */

    // Generar liquidaciones automáticamente al crear un cargo médico
    'liquidacion_automatica' => env('LIQUIDACION_AUTOMATICA', true),

    // Generar liquidaciones solo cuando el cargo es aprobado
    'liquidacion_automatica_aprobacion' => env('LIQUIDACION_AUTOMATICA_APROBACION', true),

    // Generar cargos médicos automáticamente cuando se paga una factura
    'cargo_automatico' => env('CARGO_AUTOMATICO', true),

    // Programación de liquidaciones automáticas (diaria, semanal, mensual)
    'liquidacion_programada' => env('LIQUIDACION_PROGRAMADA', 'diaria'),

    // Programación de pagos automáticos
    'pagos_automaticos' => env('PAGOS_AUTOMATICOS', false),

    // Día del mes para pagos automáticos (si pagos_automaticos es true)
    'dia_pago_automatico' => env('DIA_PAGO_AUTOMATICO', 15),

    // Notificaciones de liquidaciones
    'notificaciones_liquidaciones' => env('NOTIFICACIONES_LIQUIDACIONES', true),

    // Notificaciones de pagos
    'notificaciones_pagos' => env('NOTIFICACIONES_PAGOS', true),

    // Porcentaje médico por defecto (si no hay contrato)
    'porcentaje_medico_default' => env('PORCENTAJE_MEDICO_DEFAULT', 80),

    // Porcentaje de retención ISR por defecto
    'porcentaje_retencion_default' => env('PORCENTAJE_RETENCION_DEFAULT', 10),

    // Permitir pagos parciales
    'permitir_pagos_parciales' => env('PERMITIR_PAGOS_PARCIALES', true),
    
    // Generación automática de nómina
    'nomina_automatica' => env('NOMINA_AUTOMATICA', false),
    
    // Día del mes para generación automática de nómina
    'dia_nomina_automatica' => env('DIA_NOMINA_AUTOMATICA', 30),
    
    // Ruta de almacenamiento de las nóminas generadas automáticamente
    'ruta_nomina_automatica' => env('RUTA_NOMINA_AUTOMATICA', 'app/public/nominas'),
    
    // Incluir pagos ya realizados en la nómina automática
    'nomina_incluir_pagados' => env('NOMINA_INCLUIR_PAGADOS', true),
    
    // Incluir liquidaciones pendientes en la nómina automática
    'nomina_incluir_pendientes' => env('NOMINA_INCLUIR_PENDIENTES', false),
];
