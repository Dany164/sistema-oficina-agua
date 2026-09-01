-- =========================================================
-- IMPORTANTE: para que los triggers registren QUIÉN hizo el
-- cambio, la aplicación debe ejecutar, en la misma conexión,
-- justo antes de cada INSERT/UPDATE/DELETE en las tablas
-- auditadas:
--     SET @usuario_actual = <id_del_usuario_logueado>;
-- Si no se setea, usuario_id quedará en NULL.
-- =========================================================
CREATE DATABASE IF NOT EXISTS oficina_agua;
USE oficina_agua;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------
-- Tb_Roles
-- ---------------------------------------------------------
CREATE TABLE Tb_Roles (
    rol_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(50) NOT NULL,
    PRIMARY KEY (rol_id),
    UNIQUE KEY uk_roles_nombre (nombre)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Clientes
-- ---------------------------------------------------------
CREATE TABLE Tb_Clientes (
    cliente_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100) NOT NULL,
    telefono    VARCHAR(20) NULL,
    direccion   VARCHAR(255) NOT NULL,
    PRIMARY KEY (cliente_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Tipos_Servicio
-- ---------------------------------------------------------
CREATE TABLE Tb_Tipos_Servicio (
    tipo_servicio_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tipo_servicio      VARCHAR(50) NOT NULL,
    litros_incluidos   INT NULL,
    PRIMARY KEY (tipo_servicio_id),
    UNIQUE KEY uk_tipos_servicio_nombre (tipo_servicio)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Usuarios
-- ---------------------------------------------------------
CREATE TABLE Tb_Usuarios (
    usuario_id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre         VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL,
    password_hash  VARCHAR(255) NOT NULL,
    rol_id         INT UNSIGNED NOT NULL,
    PRIMARY KEY (usuario_id),
    UNIQUE KEY uk_usuarios_email (email),
    KEY idx_usuarios_rol (rol_id),
    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (rol_id) REFERENCES Tb_Roles(rol_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Contadores  (auditada)
-- ---------------------------------------------------------
CREATE TABLE Tb_Contadores (
    contador_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    numero_registro     VARCHAR(50) NOT NULL,
    direccion_servicio  VARCHAR(50) NOT NULL,
    estado              BOOLEAN NOT NULL,
    cliente_id          INT UNSIGNED NOT NULL,
    tipo_servicio_id    INT UNSIGNED NOT NULL,
    PRIMARY KEY (contador_id),
    UNIQUE KEY uk_contadores_numero_registro (numero_registro),
    KEY idx_contadores_cliente (cliente_id),
    KEY idx_contadores_tipo_servicio (tipo_servicio_id),
    CONSTRAINT fk_contadores_cliente
        FOREIGN KEY (cliente_id) REFERENCES Tb_Clientes(cliente_id),
    CONSTRAINT fk_contadores_tipo_servicio
        FOREIGN KEY (tipo_servicio_id) REFERENCES Tb_Tipos_Servicio(tipo_servicio_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Tarifas  (auditada)
-- ---------------------------------------------------------
CREATE TABLE Tb_Tarifas (
    tarifa_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    monto_por_unidad   DECIMAL(10,2) NOT NULL,
    vigente_desde      DATE NOT NULL,
    vigente_hasta      DATE NULL,
    tipo_servicio_id   INT UNSIGNED NOT NULL,
    PRIMARY KEY (tarifa_id),
    KEY idx_tarifas_tipo_servicio (tipo_servicio_id),
    CONSTRAINT fk_tarifas_tipo_servicio
        FOREIGN KEY (tipo_servicio_id) REFERENCES Tb_Tipos_Servicio(tipo_servicio_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Metodos_Pago
-- ---------------------------------------------------------
CREATE TABLE Tb_Metodos_Pago (
    metodos_pago_id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    metodo           VARCHAR(30) NOT NULL,
    PRIMARY KEY (metodos_pago_id),
    UNIQUE KEY uk_metodos_pago_nombre (metodo)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_lecturas  (auditada)
-- ---------------------------------------------------------
CREATE TABLE Tb_lecturas (
    lectura_id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    lectura_anterior   INT NOT NULL,
    lectura_actual     INT NOT NULL,
    consumo_litros     INT NOT NULL,
    litros_exceso      INT NULL,
    monto_base         DECIMAL(10,2) NOT NULL,
    monto_exceso       DECIMAL(10,2) NULL,
    monto_total        DECIMAL(10,2) NOT NULL,
    fecha              DATE NOT NULL,
    contador_id        INT UNSIGNED NOT NULL,
    usuario_lector_id  INT UNSIGNED NOT NULL,
    tarifa_base_id     INT UNSIGNED NOT NULL,
    tarifa_exceso_id   INT UNSIGNED NULL,
    PRIMARY KEY (lectura_id),
    -- evita registrar dos lecturas del mismo contador el mismo día;
    -- si facturas mensual, cambia esto por UNIQUE(contador_id, YEAR(fecha), MONTH(fecha))
    -- (requeriría columnas generadas, ver nota al final del archivo)
    UNIQUE KEY uk_lecturas_contador_fecha (contador_id, fecha),
    KEY idx_lecturas_contador (contador_id),
    KEY idx_lecturas_usuario_lector (usuario_lector_id),
    KEY idx_lecturas_tarifa_base (tarifa_base_id),
    KEY idx_lecturas_tarifa_exceso (tarifa_exceso_id),
    CONSTRAINT fk_lecturas_contador
        FOREIGN KEY (contador_id) REFERENCES Tb_Contadores(contador_id),
    CONSTRAINT fk_lecturas_usuario_lector
        FOREIGN KEY (usuario_lector_id) REFERENCES Tb_Usuarios(usuario_id),
    CONSTRAINT fk_lecturas_tarifa_base
        FOREIGN KEY (tarifa_base_id) REFERENCES Tb_Tarifas(tarifa_id),
    CONSTRAINT fk_lecturas_tarifa_exceso
        FOREIGN KEY (tarifa_exceso_id) REFERENCES Tb_Tarifas(tarifa_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Pagos  (auditada)
-- ---------------------------------------------------------
CREATE TABLE Tb_Pagos (
    pago_id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    monto            DECIMAL(10,2) NOT NULL,
    fecha_pago       DATE NOT NULL,
    numero_recibo    VARCHAR(20) NOT NULL,
    lectura_id       INT UNSIGNED NOT NULL,
    usuario_id       INT UNSIGNED NOT NULL,
    metodos_pago_id  INT UNSIGNED NOT NULL,
    observaciones    VARCHAR(255) NULL,
    PRIMARY KEY (pago_id),
    -- un pago cubre siempre la lectura completa -> nunca puede haber 2 pagos
    -- para la misma lectura (evita duplicados por doble clic / reintento)
    UNIQUE KEY uk_pagos_lectura (lectura_id),
    UNIQUE KEY uk_pagos_numero_recibo (numero_recibo),
    KEY idx_pagos_usuario (usuario_id),
    KEY idx_pagos_metodo (metodos_pago_id),
    CONSTRAINT fk_pagos_lectura
        FOREIGN KEY (lectura_id) REFERENCES Tb_lecturas(lectura_id),
    CONSTRAINT fk_pagos_usuario
        FOREIGN KEY (usuario_id) REFERENCES Tb_Usuarios(usuario_id),
    CONSTRAINT fk_pagos_metodo
        FOREIGN KEY (metodos_pago_id) REFERENCES Tb_Metodos_Pago(metodos_pago_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Tb_Auditorias  (genérica — sin FK real hacia las tablas auditadas)
-- ---------------------------------------------------------
CREATE TABLE Tb_Auditorias (
    auditoria_id      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    tabla             VARCHAR(50) NOT NULL,
    registro_id       INT NOT NULL,
    accion            VARCHAR(10) NOT NULL,
    datos_anteriores  JSON NULL,
    datos_nuevos      JSON NULL,
    fecha             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usuario_id        INT UNSIGNED NULL,
    PRIMARY KEY (auditoria_id),
    KEY idx_auditorias_usuario (usuario_id),
    KEY idx_auditorias_tabla_registro (tabla, registro_id),
    CONSTRAINT fk_auditorias_usuario
        FOREIGN KEY (usuario_id) REFERENCES Tb_Usuarios(usuario_id)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;


-- =========================================================
-- TRIGGERS DE AUDITORÍA
-- 3 por tabla auditada (INSERT / UPDATE / DELETE) = 12 en total
-- =========================================================

DELIMITER $$

-- ---------------------------------------------------------
-- Tb_Tarifas
-- ---------------------------------------------------------
CREATE TRIGGER trg_tarifas_insert
AFTER INSERT ON Tb_Tarifas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_nuevos, fecha)
    VALUES ('Tb_Tarifas', NEW.tarifa_id, 'INSERT', @usuario_actual,
        JSON_OBJECT(
            'monto_por_unidad', NEW.monto_por_unidad,
            'vigente_desde', NEW.vigente_desde,
            'vigente_hasta', NEW.vigente_hasta,
            'tipo_servicio_id', NEW.tipo_servicio_id
        ), NOW());
END$$

CREATE TRIGGER trg_tarifas_update
AFTER UPDATE ON Tb_Tarifas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, fecha)
    VALUES ('Tb_Tarifas', NEW.tarifa_id, 'UPDATE', @usuario_actual,
        JSON_OBJECT(
            'monto_por_unidad', OLD.monto_por_unidad,
            'vigente_desde', OLD.vigente_desde,
            'vigente_hasta', OLD.vigente_hasta,
            'tipo_servicio_id', OLD.tipo_servicio_id
        ),
        JSON_OBJECT(
            'monto_por_unidad', NEW.monto_por_unidad,
            'vigente_desde', NEW.vigente_desde,
            'vigente_hasta', NEW.vigente_hasta,
            'tipo_servicio_id', NEW.tipo_servicio_id
        ), NOW());
END$$

CREATE TRIGGER trg_tarifas_delete
AFTER DELETE ON Tb_Tarifas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, fecha)
    VALUES ('Tb_Tarifas', OLD.tarifa_id, 'DELETE', @usuario_actual,
        JSON_OBJECT(
            'monto_por_unidad', OLD.monto_por_unidad,
            'vigente_desde', OLD.vigente_desde,
            'vigente_hasta', OLD.vigente_hasta,
            'tipo_servicio_id', OLD.tipo_servicio_id
        ), NOW());
END$$

-- ---------------------------------------------------------
-- Tb_Contadores
-- ---------------------------------------------------------
CREATE TRIGGER trg_contadores_insert
AFTER INSERT ON Tb_Contadores
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_nuevos, fecha)
    VALUES ('Tb_Contadores', NEW.contador_id, 'INSERT', @usuario_actual,
        JSON_OBJECT(
            'numero_registro', NEW.numero_registro,
            'direccion_servicio', NEW.direccion_servicio,
            'estado', NEW.estado,
            'cliente_id', NEW.cliente_id,
            'tipo_servicio_id', NEW.tipo_servicio_id
        ), NOW());
END$$

CREATE TRIGGER trg_contadores_update
AFTER UPDATE ON Tb_Contadores
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, fecha)
    VALUES ('Tb_Contadores', NEW.contador_id, 'UPDATE', @usuario_actual,
        JSON_OBJECT(
            'numero_registro', OLD.numero_registro,
            'direccion_servicio', OLD.direccion_servicio,
            'estado', OLD.estado,
            'cliente_id', OLD.cliente_id,
            'tipo_servicio_id', OLD.tipo_servicio_id
        ),
        JSON_OBJECT(
            'numero_registro', NEW.numero_registro,
            'direccion_servicio', NEW.direccion_servicio,
            'estado', NEW.estado,
            'cliente_id', NEW.cliente_id,
            'tipo_servicio_id', NEW.tipo_servicio_id
        ), NOW());
END$$

CREATE TRIGGER trg_contadores_delete
AFTER DELETE ON Tb_Contadores
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, fecha)
    VALUES ('Tb_Contadores', OLD.contador_id, 'DELETE', @usuario_actual,
        JSON_OBJECT(
            'numero_registro', OLD.numero_registro,
            'direccion_servicio', OLD.direccion_servicio,
            'estado', OLD.estado,
            'cliente_id', OLD.cliente_id,
            'tipo_servicio_id', OLD.tipo_servicio_id
        ), NOW());
END$$

-- ---------------------------------------------------------
-- Tb_lecturas
-- ---------------------------------------------------------
CREATE TRIGGER trg_lecturas_insert
AFTER INSERT ON Tb_lecturas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_nuevos, fecha)
    VALUES ('Tb_lecturas', NEW.lectura_id, 'INSERT', @usuario_actual,
        JSON_OBJECT(
            'lectura_anterior', NEW.lectura_anterior,
            'lectura_actual', NEW.lectura_actual,
            'consumo_litros', NEW.consumo_litros,
            'litros_exceso', NEW.litros_exceso,
            'monto_base', NEW.monto_base,
            'monto_exceso', NEW.monto_exceso,
            'monto_total', NEW.monto_total,
            'fecha', NEW.fecha,
            'contador_id', NEW.contador_id,
            'usuario_lector_id', NEW.usuario_lector_id,
            'tarifa_base_id', NEW.tarifa_base_id,
            'tarifa_exceso_id', NEW.tarifa_exceso_id
        ), NOW());
END$$

CREATE TRIGGER trg_lecturas_update
AFTER UPDATE ON Tb_lecturas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, fecha)
    VALUES ('Tb_lecturas', NEW.lectura_id, 'UPDATE', @usuario_actual,
        JSON_OBJECT(
            'lectura_anterior', OLD.lectura_anterior,
            'lectura_actual', OLD.lectura_actual,
            'consumo_litros', OLD.consumo_litros,
            'litros_exceso', OLD.litros_exceso,
            'monto_base', OLD.monto_base,
            'monto_exceso', OLD.monto_exceso,
            'monto_total', OLD.monto_total,
            'fecha', OLD.fecha,
            'contador_id', OLD.contador_id,
            'usuario_lector_id', OLD.usuario_lector_id,
            'tarifa_base_id', OLD.tarifa_base_id,
            'tarifa_exceso_id', OLD.tarifa_exceso_id
        ),
        JSON_OBJECT(
            'lectura_anterior', NEW.lectura_anterior,
            'lectura_actual', NEW.lectura_actual,
            'consumo_litros', NEW.consumo_litros,
            'litros_exceso', NEW.litros_exceso,
            'monto_base', NEW.monto_base,
            'monto_exceso', NEW.monto_exceso,
            'monto_total', NEW.monto_total,
            'fecha', NEW.fecha,
            'contador_id', NEW.contador_id,
            'usuario_lector_id', NEW.usuario_lector_id,
            'tarifa_base_id', NEW.tarifa_base_id,
            'tarifa_exceso_id', NEW.tarifa_exceso_id
        ), NOW());
END$$

CREATE TRIGGER trg_lecturas_delete
AFTER DELETE ON Tb_lecturas
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, fecha)
    VALUES ('Tb_lecturas', OLD.lectura_id, 'DELETE', @usuario_actual,
        JSON_OBJECT(
            'lectura_anterior', OLD.lectura_anterior,
            'lectura_actual', OLD.lectura_actual,
            'consumo_litros', OLD.consumo_litros,
            'litros_exceso', OLD.litros_exceso,
            'monto_base', OLD.monto_base,
            'monto_exceso', OLD.monto_exceso,
            'monto_total', OLD.monto_total,
            'fecha', OLD.fecha,
            'contador_id', OLD.contador_id,
            'usuario_lector_id', OLD.usuario_lector_id,
            'tarifa_base_id', OLD.tarifa_base_id,
            'tarifa_exceso_id', OLD.tarifa_exceso_id
        ), NOW());
END$$

-- ---------------------------------------------------------
-- Tb_Pagos
-- ---------------------------------------------------------
CREATE TRIGGER trg_pagos_insert
AFTER INSERT ON Tb_Pagos
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_nuevos, fecha)
    VALUES ('Tb_Pagos', NEW.pago_id, 'INSERT', @usuario_actual,
        JSON_OBJECT(
            'monto', NEW.monto,
            'fecha_pago', NEW.fecha_pago,
            'numero_recibo', NEW.numero_recibo,
            'lectura_id', NEW.lectura_id,
            'usuario_id', NEW.usuario_id,
            'metodos_pago_id', NEW.metodos_pago_id,
            'observaciones', NEW.observaciones
        ), NOW());
END$$

CREATE TRIGGER trg_pagos_update
AFTER UPDATE ON Tb_Pagos
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, datos_nuevos, fecha)
    VALUES ('Tb_Pagos', NEW.pago_id, 'UPDATE', @usuario_actual,
        JSON_OBJECT(
            'monto', OLD.monto,
            'fecha_pago', OLD.fecha_pago,
            'numero_recibo', OLD.numero_recibo,
            'lectura_id', OLD.lectura_id,
            'usuario_id', OLD.usuario_id,
            'metodos_pago_id', OLD.metodos_pago_id,
            'observaciones', OLD.observaciones
        ),
        JSON_OBJECT(
            'monto', NEW.monto,
            'fecha_pago', NEW.fecha_pago,
            'numero_recibo', NEW.numero_recibo,
            'lectura_id', NEW.lectura_id,
            'usuario_id', NEW.usuario_id,
            'metodos_pago_id', NEW.metodos_pago_id,
            'observaciones', NEW.observaciones
        ), NOW());
END$$

CREATE TRIGGER trg_pagos_delete
AFTER DELETE ON Tb_Pagos
FOR EACH ROW
BEGIN
    INSERT INTO Tb_Auditorias (tabla, registro_id, accion, usuario_id, datos_anteriores, fecha)
    VALUES ('Tb_Pagos', OLD.pago_id, 'DELETE', @usuario_actual,
        JSON_OBJECT(
            'monto', OLD.monto,
            'fecha_pago', OLD.fecha_pago,
            'numero_recibo', OLD.numero_recibo,
            'lectura_id', OLD.lectura_id,
            'usuario_id', OLD.usuario_id,
            'metodos_pago_id', OLD.metodos_pago_id,
            'observaciones', OLD.observaciones
        ), NOW());
END$$

DELIMITER ;

-- =========================================================
-- NOTA sobre uk_lecturas_contador_fecha:
-- Tal como está, evita 2 lecturas del MISMO contador el MISMO
-- día exacto. Si el ciclo real es mensual (una lectura por
-- contador por mes, sin importar el día), reemplazar por
-- columnas generadas:
--
-- ALTER TABLE Tb_lecturas
--     ADD COLUMN anio_periodo SMALLINT AS (YEAR(fecha)) STORED,
--     ADD COLUMN mes_periodo  TINYINT  AS (MONTH(fecha)) STORED,
--     ADD UNIQUE KEY uk_lecturas_contador_periodo
--         (contador_id, anio_periodo, mes_periodo);
-- =========================================================