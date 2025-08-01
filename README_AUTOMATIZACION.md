# Automatización de Contabilidad Médica

Este documento explica cómo se ha implementado la automatización del flujo de Contabilidad Médica en el sistema.

## Características implementadas

1. **Generación automática de cargos médicos**
   - Al pagar una factura, se genera automáticamente un cargo médico
   - Se extraen los datos directamente de la factura

2. **Liquidaciones automáticas**
   - Se pueden generar automáticamente al crear un cargo médico
   - También se pueden generar periódicamente (diario, semanal, mensual)
   - Agrupación de cargos pendientes por médico

3. **Pagos programados**
   - Pagos automáticos en fechas específicas (configurable)
   - Cálculo automático de retenciones de impuestos

4. **Notificaciones automatizadas**
   - Alertas a médicos cuando sus liquidaciones están listas
   - Notificaciones de pagos realizados con enlaces a recibos digitales

## Configuración

Todas las opciones de automatización se pueden configurar en el archivo `config/contabilidad.php`. También puedes usar variables de entorno para modificar la configuración:

```
# .env
LIQUIDACION_AUTOMATICA=true
LIQUIDACION_AUTOMATICA_APROBACION=true
CARGO_AUTOMATICO=true
LIQUIDACION_PROGRAMADA=diaria  # Opciones: diaria, semanal, mensual
PAGOS_AUTOMATICOS=false
DIA_PAGO_AUTOMATICO=15  # Día del mes para pagos automáticos
NOTIFICACIONES_LIQUIDACIONES=true
NOTIFICACIONES_PAGOS=true
PORCENTAJE_MEDICO_DEFAULT=80  # Si no hay contrato
PORCENTAJE_RETENCION_DEFAULT=10
PERMITIR_PAGOS_PARCIALES=true
NOMINA_AUTOMATICA=false  # Generar nómina automáticamente
DIA_NOMINA_AUTOMATICA=30  # Día del mes para generar nómina
NOMINA_PERIODO=mensual  # Opciones: mensual, quincenal
```

## Programar tareas

Para que las tareas automáticas se ejecuten, es necesario configurar el programador de tareas de Laravel en el servidor:

```bash
# Agregar al crontab
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## Comandos disponibles

### Generar liquidaciones automáticas

```bash
# Generar liquidaciones para todos los centros
php artisan contabilidad:generar-liquidaciones

# Generar liquidaciones para un centro específico
php artisan contabilidad:generar-liquidaciones --centro_id=1

# Generar liquidaciones para un médico específico
php artisan contabilidad:generar-liquidaciones --medico_id=5

# Generar liquidaciones con fecha específica
php artisan contabilidad:generar-liquidaciones --fecha="2023-07-30"
```

### Generar pagos automáticos

```bash
# Generar pagos para todos los centros
php artisan contabilidad:generar-pagos

# Generar pagos para un centro específico
php artisan contabilidad:generar-pagos --centro_id=1

# Generar pagos para una liquidación específica
php artisan contabilidad:generar-pagos --liquidacion_id=101

# Generar pagos con fecha específica
php artisan contabilidad:generar-pagos --fecha="2023-07-30"
```

## Flujo de automatización

1. **Factura al paciente**: Al marcar una factura como pagada, se genera automáticamente un cargo médico.
2. **Cargo médico**: Según la configuración, puede generar automáticamente una liquidación o esperar al proceso programado.
3. **Liquidación**: Se generan periódicamente según la configuración (diaria, semanal, mensual).
4. **Pago**: Si los pagos automáticos están habilitados, se efectúan en el día configurado del mes.
5. **Nómina médica**: Genera un resumen de pagos y retenciones por periodo (mensual o quincenal).
6. **Notificaciones**: Se envían alertas a los médicos en cada paso.

### Generar nómina médica

```bash
# Generar nómina para todos los centros
php artisan contabilidad:generar-nomina

# Generar nómina para un centro específico
php artisan contabilidad:generar-nomina --centro_id=1

# Generar nómina para médicos específicos
php artisan contabilidad:generar-nomina --medico_id=5 --medico_id=10

# Generar nómina quincenal
php artisan contabilidad:generar-nomina --periodo=quincenal

# Generar nómina con periodo específico
php artisan contabilidad:generar-nomina --inicio="2025-07-01" --fin="2025-07-30"

# Generar y descargar PDF automáticamente
php artisan contabilidad:generar-nomina --descargar
```

## Modificación de la automatización

Si necesitas modificar el comportamiento de la automatización, puedes editar:

- **Observers**: `app/Observers/FacturaObserver.php` y `app/Observers/CargoMedicoObserver.php`
- **Comandos**: 
  - `app/Console/Commands/GenerarLiquidacionesAutomaticas.php`
  - `app/Console/Commands/GenerarPagosAutomaticos.php`
  - `app/Console/Commands/GenerarNominaMedica.php`
- **Notificaciones**: `app/Notifications/LiquidacionGenerada.php` y `app/Notifications/PagoHonorarioRealizado.php`
- **Programación**: `app/Console/Kernel.php`
- **Vistas PDF**: `resources/views/pdf/nomina.blade.php`
- **Controladores**: `app/Http/Controllers/NominaController.php`
