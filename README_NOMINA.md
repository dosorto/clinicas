# Sistema de Nómina Médica

Este módulo permite generar nóminas para los médicos, calculando los honorarios, retenciones y pagos realizados en un período específico.

## Características principales

- Generación de nóminas individuales o generales
- Selección de período (mensual o quincenal)
- Cálculo automático de retenciones fiscales
- Exportación a PDF para impresión o envío digital
- Estadísticas de pagos mensuales en el Dashboard
- Programación de generación automática de nóminas

## Cómo utilizar el sistema de nóminas

### Generación de nómina desde la interfaz

1. Acceda al panel de administración en `/admin`
2. Navegue a la sección "Contabilidad Médica" → "Nómina Médica"
3. Para generar una nómina general:
   - Haga clic en el botón "Generar nómina general" en la parte superior
   - Seleccione el período (fecha inicio y fin)
   - Seleccione un médico específico o deje en blanco para todos
   - Configure las opciones de inclusión (pagos realizados, liquidaciones pendientes)
   - Haga clic en "Generar" para descargar el PDF

4. Para generar una nómina individual:
   - Busque el médico en la lista
   - Haga clic en el botón "Generar nómina" en la fila del médico
   - Configure el período y opciones
   - Haga clic en "Generar" para descargar el PDF

### Generación automática de nóminas

El sistema puede generar automáticamente las nóminas en una fecha programada:

1. Configure las opciones en el archivo `.env`:
   ```
   NOMINA_AUTOMATICA=true
   DIA_NOMINA_AUTOMATICA=30
   RUTA_NOMINA_AUTOMATICA=app/public/nominas
   NOMINA_INCLUIR_PAGADOS=true
   NOMINA_INCLUIR_PENDIENTES=false
   ```

2. Las nóminas se generarán automáticamente el día configurado y se guardarán en la ruta especificada.

### Generación mediante línea de comandos

También puede generar nóminas manualmente desde la línea de comandos:

```bash
php artisan nomina:generar [opciones]
```

Opciones disponibles:
- `--medico_id=ID` - ID del médico (opcional)
- `--inicio=YYYY-MM-DD` - Fecha de inicio (por defecto: inicio del mes actual)
- `--fin=YYYY-MM-DD` - Fecha de fin (por defecto: fecha actual)
- `--incluir_pagados=1|0` - Incluir pagos ya realizados (por defecto: 1)
- `--incluir_pendientes=1|0` - Incluir liquidaciones pendientes (por defecto: 0)
- `--guardar` - Guardar el PDF en vez de mostrarlo
- `--ruta_guardado=ruta` - Ruta donde guardar el PDF generado

Ejemplo:
```bash
php artisan nomina:generar --inicio=2023-06-01 --fin=2023-06-30 --guardar
```

## Estructura del PDF de nómina

El PDF generado incluye:

1. **Resumen general**:
   - Período de la nómina
   - Total general de honorarios
   - Total de retenciones
   - Total neto pagado
   - Listado de médicos incluidos

2. **Detalle por médico**:
   - Información del médico (nombre, especialidad, centro, contrato)
   - Listado de liquidaciones del período
   - Listado de pagos realizados
   - Totales (honorarios, retenciones, neto, pendiente)
   - Espacios para firmas

## Integración con otros módulos

El sistema de nómina se integra con:

- **Contratos médicos**: Utiliza los porcentajes definidos en los contratos
- **Liquidaciones**: Obtiene las liquidaciones generadas en el período
- **Pagos de honorarios**: Calcula los pagos realizados y las retenciones aplicadas
