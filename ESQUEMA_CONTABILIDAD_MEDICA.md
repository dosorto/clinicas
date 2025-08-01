# 📊 ESTRUCTURA BASE DE DATOS - CONTABILIDAD MÉDICA

## 🏥 TABLAS DEL SISTEMA DE CONTABILIDAD MÉDICA

### 1. **contratos_medicos** (Contratos de Médicos)
```sql
CREATE TABLE contratos_medicos (
    id                  BIGINT PRIMARY KEY AUTO_INCREMENT,
    medico_id           BIGINT NOT NULL,                    -- FK: medicos.id
    salario_quincenal   DECIMAL(10,2) NOT NULL,            -- Salario cada 15 días
    salario_mensual     DECIMAL(10,2) NOT NULL,            -- Salario mensual
    porcentaje_servicio DECIMAL(5,2) NOT NULL,             -- % de comisión por servicios
    fecha_inicio        DATE NOT NULL,                     -- Inicio del contrato
    fecha_fin           DATE NULL,                         -- Fin del contrato (NULL = vigente)
    activo              ENUM('SI', 'NO') NOT NULL,         -- Estado del contrato
    centro_id           BIGINT NULL,                       -- FK: centros_medicos.id
    created_by          INT NULL,
    updated_by          INT NULL,
    deleted_by          INT NULL,
    created_at          TIMESTAMP,
    updated_at          TIMESTAMP,
    deleted_at          TIMESTAMP NULL
);
```

### 2. **cargos_medicos** (Cargos/Facturas a Médicos)
```sql
CREATE TABLE cargos_medicos (
    id               BIGINT PRIMARY KEY AUTO_INCREMENT,
    medico_id        BIGINT NOT NULL,                      -- FK: medicos.id
    contrato_id      BIGINT NOT NULL,                      -- FK: contratos_medicos.id
    descripcion      TEXT NOT NULL,                        -- Descripción del cargo
    periodo_inicio   DATE NOT NULL,                        -- Inicio del período
    periodo_fin      DATE NOT NULL,                        -- Fin del período
    subtotal         DECIMAL(10,2) NOT NULL,               -- Subtotal sin impuestos
    impuesto_total   DECIMAL(10,2) NOT NULL,               -- Total de impuestos
    total            DECIMAL(10,2) NOT NULL,               -- Total del cargo
    estado           ENUM('PENDIENTE', 'PAGADA', 'ANULADA', 'PARCIAL'),
    observaciones    TEXT NULL,                            -- Observaciones adicionales
    centro_id        BIGINT NULL,                          -- FK: centros_medicos.id
    created_by       INT NULL,
    updated_by       INT NULL,
    deleted_by       INT NULL,
    created_at       TIMESTAMP,
    updated_at       TIMESTAMP,
    deleted_at       TIMESTAMP NULL
);
```

### 3. **pagos_cargos_medicos** (Pagos de Cargos Médicos)
```sql
CREATE TABLE pagos_cargos_medicos (
    id             BIGINT PRIMARY KEY AUTO_INCREMENT,
    cargo_id       BIGINT NOT NULL,                        -- FK: cargos_medicos.id
    fecha_pago     TIMESTAMP NOT NULL,                     -- Fecha y hora del pago
    monto_pagado   DECIMAL(10,2) NOT NULL,                 -- Monto pagado
    metodo_pago    ENUM('EFECTIVO', 'TRANSFERENCIA', 'CHEQUE'),
    referencia     VARCHAR(255) NULL,                      -- Referencia del pago
    observaciones  TEXT NULL,                              -- Observaciones del pago
    centro_id      BIGINT NULL,                            -- FK: centros_medicos.id
    created_by     INT NULL,
    updated_by     INT NULL,
    deleted_by     INT NULL,
    created_at     TIMESTAMP,
    updated_at     TIMESTAMP,
    deleted_at     TIMESTAMP NULL
);
```

### 4. **liquidaciones_honorarios** (Liquidaciones de Honorarios)
```sql
CREATE TABLE liquidaciones_honorarios (
    id                BIGINT PRIMARY KEY AUTO_INCREMENT,
    medico_id         BIGINT NOT NULL,                      -- FK: medicos.id
    periodo_inicio    DATE NOT NULL,                        -- Inicio del período de liquidación
    periodo_fin       DATE NOT NULL,                        -- Fin del período de liquidación
    monto_total       DECIMAL(10,2) NOT NULL,               -- Monto total a liquidar
    estado            ENUM('PENDIENTE', 'PAGADA', 'PARCIAL', 'ANULADA'),
    tipo_liquidacion  ENUM('PORCENTAJE', 'FIJO', 'MIXTO'),  -- Tipo de cálculo
    centro_id         BIGINT NULL,                          -- FK: centros_medicos.id
    created_by        INT NULL,
    updated_by        INT NULL,
    deleted_by        INT NULL,
    created_at        TIMESTAMP,
    updated_at        TIMESTAMP,
    deleted_at        TIMESTAMP NULL
);
```

### 5. **liquidaciones_detalles** (Detalles de Liquidaciones)
```sql
CREATE TABLE liquidaciones_detalles (
    id                   BIGINT PRIMARY KEY AUTO_INCREMENT,
    liquidacion_id       BIGINT NOT NULL,                   -- FK: liquidaciones_honorarios.id
    factura_detalle_id   BIGINT NOT NULL,                   -- FK: factura_detalles.id
    porcentaje_honorario DECIMAL(5,2) NOT NULL,             -- % del honorario
    monto_honorario      DECIMAL(10,2) NOT NULL,            -- Monto del honorario
    centro_id            BIGINT NULL,                       -- FK: centros_medicos.id
    created_by           INT NULL,
    updated_by           INT NULL,
    deleted_by           INT NULL,
    created_at           TIMESTAMP,
    updated_at           TIMESTAMP,
    deleted_at           TIMESTAMP NULL
);
```

### 6. **pagos_honorarios** (Pagos de Honorarios)
```sql
CREATE TABLE pagos_honorarios (
    id                   BIGINT PRIMARY KEY AUTO_INCREMENT,
    liquidacion_id       BIGINT NOT NULL,                   -- FK: liquidaciones_honorarios.id
    fecha_pago           TIMESTAMP NOT NULL,                -- Fecha y hora del pago
    monto_pagado         DECIMAL(10,2) NOT NULL,            -- Monto pagado
    metodo_pago          ENUM('TRANSFERENCIA', 'CHEQUE', 'EFECTIVO'),
    referencia_bancaria  VARCHAR(255) NULL,                 -- Referencia bancaria
    retencion_isr_pct    DECIMAL(5,2) DEFAULT 0,            -- % de retención ISR
    retencion_isr_monto  DECIMAL(10,2) DEFAULT 0,           -- Monto de retención ISR
    observaciones        TEXT NULL,                         -- Observaciones del pago
    centro_id            BIGINT NULL,                       -- FK: centros_medicos.id
    created_by           INT NULL,
    updated_by           INT NULL,
    deleted_by           INT NULL,
    created_at           TIMESTAMP,
    updated_at           TIMESTAMP,
    deleted_at           TIMESTAMP NULL
);
```

## 🔗 RELACIONES ENTRE TABLAS

```
📋 FLUJO DE CONTABILIDAD MÉDICA:

1. MÉDICOS → tienen → CONTRATOS_MEDICOS (salarios base)
2. CONTRATOS_MEDICOS → generan → CARGOS_MEDICOS (facturas)
3. CARGOS_MEDICOS → se pagan con → PAGOS_CARGOS_MEDICOS
4. MÉDICOS → tienen → LIQUIDACIONES_HONORARIOS (por servicios)
5. LIQUIDACIONES_HONORARIOS → detallan → LIQUIDACIONES_DETALLES
6. LIQUIDACIONES_HONORARIOS → se pagan con → PAGOS_HONORARIOS
```

## 📊 FOREIGN KEYS (Claves Foráneas)

| Tabla                    | Campo              | Referencia           |
|-------------------------|--------------------|---------------------|
| contratos_medicos       | medico_id          | medicos.id          |
| contratos_medicos       | centro_id          | centros_medicos.id  |
| cargos_medicos          | medico_id          | medicos.id          |
| cargos_medicos          | contrato_id        | contratos_medicos.id|
| cargos_medicos          | centro_id          | centros_medicos.id  |
| pagos_cargos_medicos    | cargo_id           | cargos_medicos.id   |
| pagos_cargos_medicos    | centro_id          | centros_medicos.id  |
| liquidaciones_honorarios| medico_id          | medicos.id          |
| liquidaciones_honorarios| centro_id          | centros_medicos.id  |
| liquidaciones_detalles  | liquidacion_id     | liquidaciones_honorarios.id |
| liquidaciones_detalles  | factura_detalle_id | factura_detalles.id |
| liquidaciones_detalles  | centro_id          | centros_medicos.id  |
| pagos_honorarios        | liquidacion_id     | liquidaciones_honorarios.id |
| pagos_honorarios        | centro_id          | centros_medicos.id  |

## 💰 CAMPOS MONETARIOS

Todos los campos monetarios usan `DECIMAL(10,2)`:
- **10 dígitos** en total
- **2 decimales** para centavos
- Ejemplo: 99,999,999.99 (casi 100 millones)

## 📅 CAMPOS DE FECHA

- `DATE`: Solo fecha (YYYY-MM-DD)
- `TIMESTAMP`: Fecha y hora (YYYY-MM-DD HH:MM:SS)

## 🔒 AUDITORÍA

Todas las tablas incluyen campos de auditoría:
- `created_by`, `updated_by`, `deleted_by`: ID del usuario
- `created_at`, `updated_at`: Timestamps automáticos
- `deleted_at`: Soft delete (borrado lógico)
