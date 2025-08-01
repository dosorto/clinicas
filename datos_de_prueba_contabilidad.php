<?php

// Este archivo crea datos de prueba directamente en la base de datos para ContabilidadMedica

// 1. CargoMedico de ejemplo
// ID: 1
// Médico: Dr. Juan Pérez
// Descripción: Consulta de cardiología
// Monto total: L. 1,500.00
// Estado: pendiente

// 2. LiquidacionHonorario de ejemplo
// ID: 101
// CargoMedico: #1
// Monto Total: L. 1,500.00
// Porcentaje Centro: 20%
// Monto Centro: L. 300.00
// Monto Médico: L. 1,200.00
// Estado: pendiente

// 3. PagoHonorario de ejemplo (cuando se realiza el pago)
// ID: 201
// Liquidación: #101
// Monto: L. 1,200.00
// Fecha de Pago: 30/07/2023
// Método de Pago: transferencia
// Referencia: Transferencia #12345
// Estado: completado

// Instrucciones para el usuario:
// 1. En el admin panel de Filament, ve a "Contabilidad Médica" -> "Cargos Médicos"
// 2. Observa el cargo médico de ejemplo
// 3. Haz clic en "Crear Liquidación" para ese cargo
// 4. Observa la liquidación creada
// 5. Ve a "Contabilidad Médica" -> "Pagos de Honorarios"
// 6. Haz clic en "Registrar Pago" y selecciona la liquidación
// 7. Completa el formulario y haz clic en el botón grande "GUARDAR PAGO"

// Este archivo sirve como guía de datos de prueba y flujo para que puedas entender
// mejor el proceso de ContabilidadMedica en el sistema.
