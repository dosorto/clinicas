# 🎯 Sistema de Cuentas por Cobrar y Pagos - Documentación Completa

## ✅ Estado del Sistema

El sistema de Cuentas por Cobrar está **completamente funcional** y listo para usar.

## 📊 Características Implementadas

### 🔍 **Visualización de Cuentas por Cobrar**
- ✅ Lista completa de todas las cuentas pendientes
- ✅ Filtros por estado (PENDIENTE, PARCIAL, VENCIDA)
- ✅ Información detallada de cada cuenta
- ✅ Estadísticas en tiempo real en el dashboard

### 💰 **Sistema de Pagos**
- ✅ Procesamiento de pagos desde la tabla de cuentas
- ✅ Pagos parciales y completos
- ✅ Actualización automática de estados
- ✅ Validaciones de montos

### 🔄 **Actualización Automática**
- ✅ Estados de facturas se actualizan automáticamente
- ✅ Saldos pendientes se recalculan automáticamente
- ✅ Cuentas por cobrar se sincronizan con pagos

## 🚀 Cómo Usar el Sistema

### **1. Ver Cuentas por Cobrar**
1. Ve a **"Gestión de Facturación" → "Cuentas Pendientes"**
2. Verás todas las cuentas con saldo pendiente
3. Usa los filtros para ver estados específicos

### **2. Procesar un Pago (Método Directo)**
1. En la tabla de Cuentas por Cobrar, encuentra la cuenta a pagar
2. Haz clic en el botón **"Pagar"** (ícono de dólar) en la fila
3. Se abrirá un formulario con:
   - Información de la factura y paciente
   - Montos (total, pagado, pendiente)
   - Campo para ingresar el monto a pagar
   - Tipo de pago
   - Fecha de pago
4. Completa los datos y haz clic en **"Procesar Pago"**
5. ✅ El sistema automáticamente:
   - Crea el registro de pago
   - Actualiza el estado de la factura
   - Actualiza el saldo pendiente en la cuenta por cobrar

### **3. Procesar un Pago (Página Dedicada)**
1. Ve a **"Gestión de Facturación" → "Cuentas Pendientes"**
2. Haz clic en **"Procesar Pago"** (botón verde en la parte superior)
3. Usa las opciones para buscar:
   - **Por Número de Factura**: Ingresa el número completo (ej: 001-001-01-00000013)
   - **Por Paciente**: Selecciona el paciente para ver sus facturas pendientes
4. Una vez encontrada la factura, completa el formulario de pago
5. Procesa el pago

## 📋 Estados del Sistema

### **Estados de Facturas**
- **PENDIENTE**: Sin pagos realizados
- **PARCIAL**: Pagos parciales realizados, aún queda saldo
- **PAGADA**: Completamente pagada

### **Estados de Cuentas por Cobrar**
- **PENDIENTE**: Sin pagos realizados
- **PARCIAL**: Pagos parciales, saldo pendiente > 0
- **PAGADA**: Completamente pagada, saldo = 0
- **VENCIDA**: Fecha de vencimiento pasada y saldo > 0

## 🔧 Funcionalidades Automáticas

### **Cuando se procesa un pago:**
1. Se crea un registro en `pagos_facturas`
2. Se actualiza automáticamente el estado de la factura
3. Se recalcula automáticamente el saldo pendiente en `cuentas_por_cobrars`
4. Se actualiza el estado de la cuenta por cobrar

### **Validaciones:**
- ✅ No se puede pagar más del saldo pendiente
- ✅ Se requiere tipo de pago válido
- ✅ Se valida que exista la factura y cuenta

## 📊 Estadísticas del Dashboard

El widget en el dashboard muestra:
- **Cuentas Pendientes**: Total de cuentas con saldo > 0
- **Saldo Total Pendiente**: Suma de todos los saldos pendientes
- **Cuentas Vencidas**: Cuentas con fecha de vencimiento pasada

## 🎯 Estado Actual de Datos

```
📊 Total de cuentas por cobrar: 13
💰 Cuentas con saldo pendiente: 12 (1 ya está pagada completamente)
💵 Saldo total pendiente: ~L.40,000.00
```

## ⚡ Flujo Completo de Ejemplo

1. **Paciente tiene factura pendiente** → Se crea automáticamente cuenta por cobrar
2. **Usuario procesa pago parcial** → Factura pasa a estado "PARCIAL"
3. **Usuario procesa pago final** → Factura pasa a estado "PAGADA", cuenta por cobrar a saldo 0
4. **Dashboard se actualiza** automáticamente con las nuevas estadísticas

---

## 🎉 ¡Sistema Listo para Producción!

El sistema está completamente funcional y listo para usar. Los usuarios pueden procesar pagos tanto desde la tabla principal como desde la página dedicada de pagos, y todos los estados se actualizan automáticamente.
