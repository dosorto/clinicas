# Guía paso a paso del flujo de ContabilidadMedica

## 1. Visión general del proceso

```
CARGO MÉDICO → LIQUIDACIÓN DE HONORARIOS → PAGO DE HONORARIOS
   (Servicio)        (Cálculo del pago)         (Pago real)
```

## 2. Dashboard de ContabilidadMedica

```
+-----------------------------------------------+
|           CONTABILIDAD MÉDICA                 |
+-----------------------------------------------+
| RESUMEN                                       |
| - Cargos pendientes: 10 (L. 25,000)           |
| - Liquidaciones pendientes: 8 (L. 20,000)     |
| - Pagos este mes: L. 15,000                   |
+-----------------------------------------------+
| ACCIONES RÁPIDAS                              |
| [Nuevo Cargo] [Nueva Liquidación] [Nuevo Pago]|
+-----------------------------------------------+
| CARGOS RECIENTES                              |
| ID | Médico      | Monto   | Estado           |
| 1  | Dr. Pérez   | L.1,500 | Pendiente        |
| 2  | Dra. López  | L.2,000 | Parcial          |
| 3  | Dr. García  | L.1,800 | Pagado           |
+-----------------------------------------------+
```

## 3. Proceso detallado con ejemplos

### 3.1 Cargo Médico

Cuando un médico atiende pacientes, se genera un cargo:

```
+-----------------------------------------------+
|           CREAR CARGO MÉDICO                  |
+-----------------------------------------------+
| INFORMACIÓN BÁSICA                            |
| - Médico: [Dr. Juan Pérez]                    |
| - Descripción: [Consulta de cardiología]      |
| - Período: [01/07/2023] a [30/07/2023]        |
+-----------------------------------------------+
| DETALLE FINANCIERO                            |
| - Subtotal: [L. 1,500.00]                     |
| - Impuesto: [L. 0.00]                         |
| - Total: [L. 1,500.00]                        |
+-----------------------------------------------+
|                 [GUARDAR CARGO]               |
+-----------------------------------------------+
```

### 3.2 Liquidación de Honorarios

Basado en el cargo, se calcula cuánto se le pagará al médico:

```
+-----------------------------------------------+
|         CREAR LIQUIDACIÓN DE HONORARIOS       |
+-----------------------------------------------+
| SELECCIONAR CARGO                             |
| [#1 - Dr. Juan Pérez - L. 1,500.00]           |
+-----------------------------------------------+
| CÁLCULO AUTOMÁTICO                            |
| - Monto total: L. 1,500.00                    |
| - Porcentaje centro: 20%                      |
| - Monto centro: L. 300.00                     |
| - Monto médico: L. 1,200.00                   |
+-----------------------------------------------+
|             [CREAR LIQUIDACIÓN]               |
+-----------------------------------------------+
```

### 3.3 Pago de Honorarios (Formulario Simplificado)

Finalmente, cuando se realiza el pago al médico:

```
+-----------------------------------------------+
|          REGISTRAR PAGO DE HONORARIOS         |
+-----------------------------------------------+
| SELECCIÓN DE LIQUIDACIÓN                      |
| [#101 - Dr. Pérez - L. 1,200 (Pendiente)]     |
+-----------------------------------------------+
| INFORMACIÓN DEL PAGO                          |
| - Monto pendiente: L. 1,200.00                |
| - Monto a pagar: [L. 1,200.00]                |
| - Fecha de pago: [30/07/2023]                 |
| - Método: [Transferencia]                     |
| - Referencia: [Trans #12345]                  |
| - Concepto: [Pago honorarios Dr. Pérez]       |
| [x] Generar recibo automáticamente            |
+-----------------------------------------------+
|                                               |
|             +-----------------+               |
|             |   GUARDAR PAGO  |               |
|             +-----------------+               |
|                                               |
+-----------------------------------------------+
```

## 4. Confirmación y Recibo

Después de guardar el pago:

```
+-----------------------------------------------+
|            PAGO EXITOSO                       |
+-----------------------------------------------+
|                                               |
|    ✅ Pago registrado correctamente           |
|                                               |
|    📄 Recibo generado                         |
|    [Descargar Recibo]                         |
|                                               |
+-----------------------------------------------+
|               [Volver a la lista]             |
+-----------------------------------------------+
```

## 5. Estados de los registros

- **Cargo Médico**: Pendiente → Parcial → Pagado
- **Liquidación**: Pendiente → Parcial → Pagado
- **Pago**: Completado / Parcial / Anulado

## 6. Mejoras implementadas

1. **Formulario simplificado** con botones visibles
2. **Botón GUARDAR PAGO** destacado y fácil de ver
3. **Autocompletado de campos** al seleccionar liquidación
4. **Generación automática de recibos**
5. **Actualización automática de estados**

## 7. Paso a paso para probar

1. Ve a **Cargos Médicos** y crea uno nuevo o usa uno existente
2. Ve a **Liquidaciones** y crea una para el cargo
3. Ve a **Pagos de Honorarios** y usa el formulario simplificado
4. Observa cómo los estados se actualizan automáticamente

¡Listo! Ahora tienes una guía visual completa del flujo de ContabilidadMedica.
