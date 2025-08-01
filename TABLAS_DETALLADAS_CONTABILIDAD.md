# 📊 TABLAS DE CONTABILIDAD MÉDICA - ESTRUCTURA COMPLETA

## 📋 TABLA 1: `contratos_medicos`
**Descripción:** Contratos laborales de los médicos con salarios y porcentajes

| Campo               | Tipo           | Descripción                          |
|---------------------|----------------|--------------------------------------|
| `id`                | BIGINT PK      | Identificador único del contrato     |
| `medico_id`         | BIGINT FK      | ID del médico (→ medicos.id)         |
| `salario_quincenal` | DECIMAL(10,2)  | Salario cada 15 días                 |
| `salario_mensual`   | DECIMAL(10,2)  | Salario mensual                      |
| `porcentaje_servicio` | DECIMAL(5,2) | % de comisión por servicios          |
| `fecha_inicio`      | DATE           | Fecha de inicio del contrato         |
| `fecha_fin`         | DATE NULL      | Fecha fin (NULL = vigente)           |
| `activo`            | ENUM           | 'SI', 'NO'                           |
| `centro_id`         | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`        | INT            | Usuario que creó                     |
| `updated_by`        | INT            | Usuario que modificó                 |
| `deleted_by`        | INT            | Usuario que eliminó                  |
| `created_at`        | TIMESTAMP      | Fecha de creación                    |
| `updated_at`        | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`        | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 💰 TABLA 2: `cargos_medicos`
**Descripción:** Facturas/cargos generados a los médicos

| Campo            | Tipo           | Descripción                          |
|------------------|----------------|--------------------------------------|
| `id`             | BIGINT PK      | Identificador único del cargo        |
| `medico_id`      | BIGINT FK      | ID del médico (→ medicos.id)         |
| `contrato_id`    | BIGINT FK      | ID del contrato (→ contratos_medicos.id) |
| `descripcion`    | TEXT           | Descripción detallada del cargo      |
| `periodo_inicio` | DATE           | Fecha inicio del período             |
| `periodo_fin`    | DATE           | Fecha fin del período                |
| `subtotal`       | DECIMAL(10,2)  | Subtotal sin impuestos               |
| `impuesto_total` | DECIMAL(10,2)  | Total de impuestos                   |
| `total`          | DECIMAL(10,2)  | Total del cargo                      |
| `estado`         | ENUM           | 'PENDIENTE', 'PAGADA', 'ANULADA', 'PARCIAL' |
| `observaciones`  | TEXT NULL      | Observaciones adicionales            |
| `centro_id`      | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`     | INT            | Usuario que creó                     |
| `updated_by`     | INT            | Usuario que modificó                 |
| `deleted_by`     | INT            | Usuario que eliminó                  |
| `created_at`     | TIMESTAMP      | Fecha de creación                    |
| `updated_at`     | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`     | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 💳 TABLA 3: `pagos_cargos_medicos`
**Descripción:** Registros de pagos realizados a los cargos médicos

| Campo          | Tipo           | Descripción                          |
|----------------|----------------|--------------------------------------|
| `id`           | BIGINT PK      | Identificador único del pago         |
| `cargo_id`     | BIGINT FK      | ID del cargo (→ cargos_medicos.id)   |
| `fecha_pago`   | TIMESTAMP      | Fecha y hora del pago                |
| `monto_pagado` | DECIMAL(10,2)  | Monto pagado                         |
| `metodo_pago`  | ENUM           | 'EFECTIVO', 'TRANSFERENCIA', 'CHEQUE' |
| `referencia`   | VARCHAR(255)   | Referencia del pago/transacción      |
| `observaciones` | TEXT NULL     | Observaciones del pago               |
| `centro_id`    | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`   | INT            | Usuario que creó                     |
| `updated_by`   | INT            | Usuario que modificó                 |
| `deleted_by`   | INT            | Usuario que eliminó                  |
| `created_at`   | TIMESTAMP      | Fecha de creación                    |
| `updated_at`   | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`   | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 📊 TABLA 4: `liquidaciones_honorarios`
**Descripción:** Liquidaciones de honorarios por servicios médicos

| Campo              | Tipo           | Descripción                          |
|--------------------|----------------|--------------------------------------|
| `id`               | BIGINT PK      | Identificador único de liquidación   |
| `medico_id`        | BIGINT FK      | ID del médico (→ medicos.id)         |
| `periodo_inicio`   | DATE           | Fecha inicio del período liquidado   |
| `periodo_fin`      | DATE           | Fecha fin del período liquidado      |
| `monto_total`      | DECIMAL(10,2)  | Monto total a liquidar               |
| `estado`           | ENUM           | 'PENDIENTE', 'PAGADA', 'PARCIAL', 'ANULADA' |
| `tipo_liquidacion` | ENUM           | 'PORCENTAJE', 'FIJO', 'MIXTO'        |
| `centro_id`        | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`       | INT            | Usuario que creó                     |
| `updated_by`       | INT            | Usuario que modificó                 |
| `deleted_by`       | INT            | Usuario que eliminó                  |
| `created_at`       | TIMESTAMP      | Fecha de creación                    |
| `updated_at`       | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`       | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 🔍 TABLA 5: `liquidaciones_detalles`
**Descripción:** Detalles específicos de cada liquidación (qué servicios incluye)

| Campo                 | Tipo           | Descripción                          |
|-----------------------|----------------|--------------------------------------|
| `id`                  | BIGINT PK      | Identificador único del detalle      |
| `liquidacion_id`      | BIGINT FK      | ID liquidación (→ liquidaciones_honorarios.id) |
| `factura_detalle_id`  | BIGINT FK      | ID factura detalle (→ factura_detalles.id) |
| `porcentaje_honorario` | DECIMAL(5,2)  | Porcentaje del honorario             |
| `monto_honorario`     | DECIMAL(10,2)  | Monto del honorario                  |
| `centro_id`           | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`          | INT            | Usuario que creó                     |
| `updated_by`          | INT            | Usuario que modificó                 |
| `deleted_by`          | INT            | Usuario que eliminó                  |
| `created_at`          | TIMESTAMP      | Fecha de creación                    |
| `updated_at`          | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`          | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 💸 TABLA 6: `pagos_honorarios`
**Descripción:** Pagos realizados de las liquidaciones de honorarios

| Campo                | Tipo           | Descripción                          |
|----------------------|----------------|--------------------------------------|
| `id`                 | BIGINT PK      | Identificador único del pago         |
| `liquidacion_id`     | BIGINT FK      | ID liquidación (→ liquidaciones_honorarios.id) |
| `fecha_pago`         | TIMESTAMP      | Fecha y hora del pago                |
| `monto_pagado`       | DECIMAL(10,2)  | Monto pagado                         |
| `metodo_pago`        | ENUM           | 'TRANSFERENCIA', 'CHEQUE', 'EFECTIVO' |
| `referencia_bancaria` | VARCHAR(255)  | Referencia bancaria del pago         |
| `retencion_isr_pct`  | DECIMAL(5,2)   | Porcentaje de retención ISR          |
| `retencion_isr_monto` | DECIMAL(10,2) | Monto de retención ISR               |
| `observaciones`      | TEXT NULL      | Observaciones del pago               |
| `centro_id`          | BIGINT FK      | ID del centro (→ centros_medicos.id) |
| `created_by`         | INT            | Usuario que creó                     |
| `updated_by`         | INT            | Usuario que modificó                 |
| `deleted_by`         | INT            | Usuario que eliminó                  |
| `created_at`         | TIMESTAMP      | Fecha de creación                    |
| `updated_at`         | TIMESTAMP      | Fecha de modificación                |
| `deleted_at`         | TIMESTAMP NULL | Fecha de eliminación (soft delete)   |

---

## 🔗 RELACIONES CLAVE

```
MÉDICOS
├── CONTRATOS_MEDICOS (1:N)
│   └── CARGOS_MEDICOS (1:N)
│       └── PAGOS_CARGOS_MEDICOS (1:N)
│
└── LIQUIDACIONES_HONORARIOS (1:N)
    ├── LIQUIDACIONES_DETALLES (1:N)
    └── PAGOS_HONORARIOS (1:N)
```

## 📝 NOTAS IMPORTANTES

1. **PK** = Primary Key (Clave Primaria)
2. **FK** = Foreign Key (Clave Foránea)
3. **DECIMAL(10,2)** = Hasta 99,999,999.99
4. **DECIMAL(5,2)** = Hasta 999.99 (para porcentajes)
5. **ENUM** = Valores predefinidos
6. **NULL** = Puede estar vacío
7. **Soft Delete** = No se borra físicamente, solo se marca como eliminado
