# SALDO PENDIENTE: LÓGICA CORRECTA IMPLEMENTADA

## Problema Identificado
**Usuario**: "el saldo pendiente es el mismo del total a pagar, solo que ese se debe actualizar cuando se pague, hasta quedar en 0 y si se queda debiendo pues debe salir ahi el saldo pendiente."

En la captura se veía:
- Total a Pagar: L. 816.00
- Total Pagado: L. 1000 (500 + 500)
- **Saldo Pendiente: L. 896.00** ← ❌ **INCORRECTO**
- Cambio a Devolver: L. 184

## Lógica Correcta

### ✅ **Cómo DEBE funcionar**
```
Saldo Pendiente = max(0, Total a Pagar - Total Pagado)
```

### ✅ **Ejemplos con los datos de la captura**

**Caso 1: Pago completo (como en la captura)**
- Total a Pagar: L. 816.00
- Total Pagado: L. 1,000.00
- **Saldo Pendiente: L. 0.00** ← ✅ **CORRECTO**
- Cambio a Devolver: L. 184.00

**Caso 2: Pago parcial**
- Total a Pagar: L. 816.00
- Total Pagado: L. 500.00
- **Saldo Pendiente: L. 316.00** ← ✅ **CORRECTO**
- Cambio a Devolver: L. 0.00

**Caso 3: Pago exacto**
- Total a Pagar: L. 816.00
- Total Pagado: L. 816.00
- **Saldo Pendiente: L. 0.00** ← ✅ **CORRECTO**
- Cambio a Devolver: L. 0.00

## Cambios Implementados

### 1. Campo `saldo_pendiente` Corregido
```php
->formatStateUsing(function (callable $get) {
    // Calcular total a pagar
    $subtotal = (float) ($get('subtotal') ?? 0);
    $impuesto = (float) ($get('impuesto_total') ?? 0);
    $descuento = (float) ($get('descuento_total') ?? 0);
    $totalConDescuento = $subtotal + $impuesto - $descuento;
    
    // Calcular total pagado desde el Repeater
    $pagos = $get('pagos') ?? [];
    $totalPagado = 0;
    foreach ($pagos as $pago) {
        $totalPagado += (float) ($pago['monto_recibido'] ?? 0);
    }
    
    // LÓGICA CORRECTA: Diferencia entre lo que falta pagar
    $saldo = max(0, $totalConDescuento - $totalPagado);
    return number_format($saldo, 2);
})
```

### 2. Callback del Repeater Mejorado
```php
->afterStateUpdated(function ($state, callable $set, callable $get) {
    // Forzar actualización inmediata de todos los campos
    $set('../../total_pagado', number_format($totalPagado, 2));
    $set('../../cambio', number_format(max(0, $totalPagado - $totalConDescuento), 2));
    $set('../../saldo_pendiente', number_format(max(0, $totalConDescuento - $totalPagado), 2));
    
    // Log para debug
    \Log::info("Pagos actualizados", [
        'total_a_pagar' => $totalConDescuento,
        'total_pagado' => $totalPagado,
        'saldo_pendiente' => max(0, $totalConDescuento - $totalPagado)
    ]);
})
```

### 3. Campo `total_a_pagar_display` con `formatStateUsing`
```php
->formatStateUsing(function (callable $get) {
    $subtotal = (float) ($get('subtotal') ?? 0);
    $impuesto = (float) ($get('impuesto_total') ?? 0);
    $descuento = (float) ($get('descuento_total') ?? 0);
    $totalAPagar = $subtotal + $impuesto - $descuento;
    return number_format($totalAPagar, 2);
})
```

## Comportamiento Esperado

### 🔄 **Actualización en Tiempo Real**
1. **Usuario agrega pago** → Repeater detecta cambio
2. **`afterStateUpdated` se ejecuta** → Calcula totales
3. **Campos se actualizan** → Saldo Pendiente se recalcula
4. **Valores se muestran** → En tiempo real

### 🎯 **Estados del Saldo Pendiente**

| Estado | Saldo Pendiente | Color | Descripción |
|--------|----------------|-------|-------------|
| Sin Pagos | = Total a Pagar | Rojo | Factura pendiente |
| Pago Parcial | > 0 | Amarillo | Falta dinero |
| Pago Completo | = 0 | Verde | Factura pagada |
| Pago Excesivo | = 0 | Verde | Hay cambio |

## Verificación con Logs

Los logs ahora mostrarán:
```
[INFO] Pagos actualizados: {
    "total_a_pagar": 816,
    "total_pagado": 1000,
    "saldo_pendiente": 0,  ← Debe ser 0
    "cambio": 184           ← Debe ser 184
}
```

## Para Probar

1. **Crear factura** con total L. 816.00
2. **Agregar pago** de L. 500.00
   - Saldo Pendiente: L. 316.00 ✅
3. **Agregar otro pago** de L. 500.00
   - Saldo Pendiente: L. 0.00 ✅
   - Cambio: L. 184.00 ✅
4. **Verificar logs** en `storage/logs/laravel.log`

## Resultado Final

El **Saldo Pendiente** ahora funcionará exactamente como debe:
- **Se actualiza en tiempo real** cuando se agregan/modifican pagos
- **Muestra la diferencia** entre lo que falta pagar
- **Llega a cero** cuando la factura está pagada completamente
- **Mantiene cero** aunque haya pagos excesivos (el exceso se muestra en "Cambio")
