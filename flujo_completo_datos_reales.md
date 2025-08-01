# Guía Detallada del Proceso de Contabilidad Médica en el Sistema

## Tabla de Modelos y sus Relaciones

| Modelo | Descripción | Relaciones Principales |
|--------|-------------|------------------------|
| **ContratoMedico** | Define acuerdo entre el médico y el centro | - Médico (pertenece a)<br>- Centro Médico (pertenece a) |
| **Cita** | Agenda la visita del paciente | - Médico (pertenece a)<br>- Paciente (pertenece a)<br>- Facturas (tiene muchas) |
| **Factura** | Documento de cobro al paciente | - Paciente (pertenece a)<br>- Cita (pertenece a)<br>- CargoMedico (genera) |
| **CargoMedico** | Registro del servicio prestado por el médico | - Médico (pertenece a)<br>- LiquidacionHonorario (tiene una) |
| **LiquidacionHonorario** | Cálculo de lo que se le debe al médico | - CargoMedico (pertenece a)<br>- PagoHonorario (tiene muchos) |
| **PagoHonorario** | Registro del pago efectuado al médico | - LiquidacionHonorario (pertenece a) |

## Flujo de Datos con Ejemplo Paso a Paso

### 1. Registro de Contrato Médico

```
ID: CM-123
Médico: Dr. Juan Pérez (Cardiólogo)
Centro Médico: Hospital San Lucas
Fecha inicio: 01/01/2023
Fecha fin: 31/12/2023
Honorarios:
  - Consulta: L. 500 (80% médico, 20% centro)
  - Electrocardiograma: L. 1,000 (75% médico, 25% centro)
  - Ecocardiograma: L. 2,000 (70% médico, 30% centro)
```

### 2. Agendamiento de Cita

```
ID: CITA-456
Paciente: María Rodríguez
Médico: Dr. Juan Pérez
Fecha: 15/07/2023
Hora: 10:00 AM
Motivo: Dolor en el pecho, revisión cardiaca
Servicios programados: Consulta + Electrocardiograma
```

### 3. Atención al Paciente y Registro

```
Fecha real: 15/07/2023
Hora inicio: 10:05 AM
Hora fin: 10:45 AM
Diagnóstico: Arritmia leve
Tratamiento: Medicación y seguimiento
Servicios realizados: 
  - Consulta cardiológica
  - Electrocardiograma
Observaciones: Paciente estable, control en 1 mes
```

### 4. Facturación al Paciente

```
ID: FACT-789
Fecha: 15/07/2023
Paciente: María Rodríguez
Centro: Hospital San Lucas
Detalles:
  - Consulta cardiología: L. 500.00
  - Electrocardiograma: L. 1,000.00
Subtotal: L. 1,500.00
ISV (15%): L. 0.00 (exento por ser servicio médico)
Total: L. 1,500.00
Método de pago: Efectivo
Estado: Pagada
```

### 5. Generación de Cargo Médico

```
ID: CM-456
Médico: Dr. Juan Pérez
Centro: Hospital San Lucas
Descripción: Servicios de cardiología (15/07/2023)
Referencia: Factura #FACT-789
Detalle:
  - Consulta: L. 500.00
  - Electrocardiograma: L. 1,000.00
Subtotal: L. 1,500.00
Impuestos: L. 0.00
Total: L. 1,500.00
Estado: Pendiente
```

### 6. Generación de Liquidación de Honorarios

```
ID: LH-101
Cargo Médico: CM-456
Médico: Dr. Juan Pérez
Centro: Hospital San Lucas
Período: 15/07/2023
Detalle del cálculo:
  - Consulta: L. 500 × 80% = L. 400 (médico)
  - Electrocardiograma: L. 1,000 × 75% = L. 750 (médico)
Monto total servicios: L. 1,500.00
Porcentaje centro promedio: 23.33%
Monto centro: L. 350.00
Monto médico: L. 1,150.00
Estado: Pendiente
```

### 7. Registro de Pago de Honorarios

```
ID: PH-201
Liquidación: LH-101
Médico: Dr. Juan Pérez
Centro: Hospital San Lucas
Fecha de pago: 30/07/2023
Monto pagado: L. 1,150.00
Método: Transferencia bancaria
Referencia: #TRF-12345
Retención ISR: 10% = L. 115.00
Monto neto pagado: L. 1,035.00
Concepto: Pago honorarios por servicios cardiológicos
Estado: Completado
```

### 8. Generación de Recibo

```
RECIBO DE HONORARIOS PROFESIONALES
N°: REC-301
Fecha: 30/07/2023

PAGADO A: Dr. Juan Pérez
CÉDULA/RTN: 0801-1980-12345
POR CONCEPTO DE: Honorarios profesionales por servicios médicos

DETALLE DE SERVICIOS:
- Consulta cardiológica (15/07/2023)
- Electrocardiograma (15/07/2023)

LIQUIDACIÓN:
Monto bruto: L. 1,150.00
Retención ISR (10%): L. 115.00
Monto neto pagado: L. 1,035.00

FORMA DE PAGO: Transferencia bancaria #TRF-12345
CENTRO MÉDICO: Hospital San Lucas

___________________           ___________________
  Firma Autorizada              Firma del Médico
```

### 9. Actualización de Estados

```
CargoMedico CM-456: Actualizado a "Pagado"
LiquidacionHonorario LH-101: Actualizado a "Pagado"
```

### 10. Registros Contables (backend)

```
ASIENTO CONTABLE:
Fecha: 30/07/2023
Concepto: Pago honorarios Dr. Juan Pérez

DÉBITOS:
- Cuenta "Honorarios Médicos por Pagar": L. 1,150.00

CRÉDITOS:
- Cuenta "Bancos": L. 1,035.00
- Cuenta "Retenciones ISR por Pagar": L. 115.00
```

## Resumen del Proceso Completo

1. **Contrato**: Se establece el acuerdo médico-centro con porcentajes.
2. **Cita**: Se agenda la visita del paciente.
3. **Atención**: El médico presta los servicios.
4. **Factura**: Se cobra al paciente los servicios recibidos.
5. **Cargo**: Se registra lo que el médico ha generado.
6. **Liquidación**: Se calcula lo que corresponde al médico según contrato.
7. **Pago**: Se paga al médico sus honorarios.
8. **Recibo**: Se documenta el pago realizado.
9. **Contabilidad**: Se registra todo en los libros contables.

## Relaciones entre las tablas en la base de datos

```
ContratoMedico (1) ------ (*) CargoMedico
         |
         |
Medico (1) ------ (*) CargoMedico (1) ------ (1) LiquidacionHonorario (1) ------ (*) PagoHonorario
         |                   |
         |                   |
     (*) Cita (*) --------- (*) Factura
         |
         |
Paciente (1) ------ (*) Factura
```

Este documento muestra el flujo completo desde el contrato médico hasta el pago final, incluyendo datos de ejemplo detallados en cada paso.
