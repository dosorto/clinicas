# SALDO PENDIENTE REACTIVO: COMPORTAMIENTO CORRECTO

## Problema Resuelto
**Usuario**: "sigue apareciendo una cifra que no es, debe salir el mismo del total a Pagar solo que este debe ser reactivo, que cambie dependiendo de cuanto se haya pagado"

## ✅ Comportamiento Implementado

### 🎯 **Lógica Correcta**
```
Saldo Pendiente = max(0, Total a Pagar - Total Pagado)
```

### 📊 **Flujo Reactivo**
1. **Al cargar factura**: `Saldo Pendiente = Total a Pagar`
2. **Al agregar pago**: `Saldo Pendiente se reduce`
3. **Al completar pago**: `Saldo Pendiente = L. 0.00`
4. **Con pago excesivo**: `Saldo Pendiente = L. 0.00, Cambio > 0`

## Ejemplo Práctico

### 📋 **Factura con Total L. 816.00**

| Paso | Acción | Total Pagado | Saldo Pendiente | Estado |
|------|--------|--------------|-----------------|--------|
| 1 | Sin pagos | L. 0.00 | **L. 816.00** | Pendiente |
| 2 | Pago L. 300 | L. 300.00 | **L. 516.00** | Parcial |
| 3 | Pago L. 500 más | L. 800.00 | **L. 16.00** | Parcial |
| 4 | Pago L. 16 más | L. 816.00 | **L. 0.00** | Pagada |
| 5 | Pago L. 200 más | L. 1,016.00 | **L. 0.00** | Pagada |

### 🎨 **Colores Dinámicos**
- **🔴 Rojo**: Sin pagos (Saldo = Total)
- **🟡 Amarillo**: Pago parcial (Saldo > 0, Pagado > 0)
- **🟢 Verde**: Pagado completo (Saldo = 0)

## Implementación

### 1. Campo `saldo_pendiente` Mejorado
```php
Forms\Components\TextInput::make('saldo_pendiente')
    ->label('Saldo Pendiente')
    ->prefix('L.')
    ->disabled()
    ->dehydrated(false)
    ->live()
    ->default(function (callable $get) {
        // Inicializar con el total a pagar
        $subtotal = (float) ($get('subtotal') ?? 0);
        $impuesto = (float) ($get('impuesto_total') ?? 0);
        $descuento = (float) ($get('descuento_total') ?? 0);
        return $subtotal + $impuesto - $descuento;
    })
    ->formatStateUsing(function (callable $get, $state) {
        // Calcular dinámicamente
        $totalAPagar = calcularTotalAPagar($get);
        $totalPagado = calcularTotalPagado($get);
        $saldoPendiente = max(0, $totalAPagar - $totalPagado);
        return number_format($saldoPendiente, 2);
    })
```

### 2. Callback del Repeater Reactivo
```php
->afterStateUpdated(function ($state, callable $set, callable $get) {
    // Calcular totales cada vez que cambia un monto
    $totalPagado = calcularTotalPagado($get);
    $totalAPagar = calcularTotalAPagar($get);
    $saldoPendiente = max(0, $totalAPagar - $totalPagado);
    $cambio = max(0, $totalPagado - $totalAPagar);
    
    // Actualizar campos inmediatamente
    $set('../../total_pagado', number_format($totalPagado, 2));
    $set('../../saldo_pendiente', number_format($saldoPendiente, 2));
    $set('../../cambio', number_format($cambio, 2));
})
```

### 3. Logs de Debug
```php
\Log::info("Saldo Pendiente Reactivo", [
    'monto_modificado' => $state,
    'total_a_pagar' => $totalAPagar,
    'total_pagado' => $totalPagado,
    'saldo_pendiente' => $saldoPendiente,
    'cambio' => $cambio
]);
```

## Resultado Esperado

### ✅ **Con tus datos (Total L. 816, Pagos L. 1000)**
- **Total a Pagar**: L. 816.00 (fijo)
- **Total Pagado**: L. 1,000.00 (suma de pagos)
- **Saldo Pendiente**: L. 0.00 ✅ (fue L. 896.00 ❌)
- **Cambio a Devolver**: L. 184.00 ✅

### 🔄 **Reactividad**
- **Al escribir monto**: Campo se actualiza inmediatamente
- **Al agregar pago**: Saldo se reduce en tiempo real
- **Al eliminar pago**: Saldo aumenta automáticamente
- **Sin delay**: Cambios instantáneos

## Validación

### 📝 **Para Probar**
1. **Crear nueva factura** (Total: L. 816.00)
2. **Verificar inicial**: Saldo Pendiente = L. 816.00
3. **Agregar pago L. 500**: Saldo = L. 316.00
4. **Agregar pago L. 500**: Saldo = L. 0.00, Cambio = L. 184.00
5. **Verificar logs** en `storage/logs/laravel.log`

### 📊 **Verificación en Logs**
```
[INFO] Saldo Pendiente Reactivo: {
    "monto_modificado": 500,
    "total_a_pagar": 816,
    "total_pagado": 1000,
    "saldo_pendiente": 0,    ← Debe ser 0
    "cambio": 184            ← Debe ser 184
}
```

## Características

### ✅ **Funcionalidades**
- **Inicia como Total a Pagar**: Valor por defecto correcto
- **Se reduce con pagos**: Matemática correcta
- **Nunca negativo**: `max(0, diferencia)`
- **Actualización inmediata**: Sin esperas
- **Colores dinámicos**: Visual feedback
- **Logs detallados**: Para debugging

### ✅ **Estados Posibles**
1. **Sin pagos**: Saldo = Total a Pagar (rojo)
2. **Pago parcial**: 0 < Saldo < Total (amarillo)
3. **Pago completo**: Saldo = 0 (verde)
4. **Pago excesivo**: Saldo = 0, Cambio > 0 (verde)

El **Saldo Pendiente** ahora funciona exactamente como debe: empieza igual al **Total a Pagar** y se va reduciendo conforme se realizan pagos hasta llegar a cero.
