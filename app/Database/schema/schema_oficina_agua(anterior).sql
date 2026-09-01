-- =====================================================================
--  SISTEMA DE GESTION - OFICINA DEL AGUA
--  Script de creacion de base de datos
-- =====================================================================
--  Motor    : MariaDB 10.4.32  (InnoDB)
--  Charset  : utf8mb4 / utf8mb4_unicode_ci
--  Version  : ER final - Semana 2
--  Tablas   : 11
--
--  CONVENCIONES APLICADAS EN TODO EL SCRIPT
--  ----------------------------------------
--  * PK              : entero UNSIGNED AUTO_INCREMENT (tipo ajustado al volumen)
--  * FK              : ON DELETE RESTRICT / ON UPDATE CASCADE
--                      -> nada que tenga historial se puede borrar por accidente
--  * Montos/lecturas : DECIMAL, NUNCA FLOAT (FLOAT pierde centavos al redondear)
--  * Bajas           : logicas (campo `activo` o `estado`), nunca DELETE
--  * Fechas de audit.: created_at / updated_at compatibles con $useTimestamps de CI4
--  * CHECK           : MariaDB 10.2.1+ los aplica de verdad; son la red de
--                      seguridad debajo de la validacion de CodeIgniter
--
--  ADVERTENCIA
--  -----------
--  Este script NO contiene ningun DROP. Si necesita volver a ejecutarlo,
--  elimine la base manualmente primero. Es intencional: evita que un
--  Ctrl+V distraido borre datos de prueba ya cargados.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS oficina_agua
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE oficina_agua;


-- #####################################################################
--  BLOQUE 1 - SEGURIDAD Y ACCESO
--  Sostiene el Must-have "Autenticacion con roles".
-- #####################################################################

-- ---------------------------------------------------------------------
--  roles
--  QUE ES: catalogo de perfiles de acceso del sistema.
--  PARA QUE SIRVE: define que puede hacer cada usuario. Los filtros de
--  CodeIgniter 4 leeran el rol para permitir o bloquear rutas completas
--  (el Lector no debe poder entrar a tarifas, la Secretaria no crea usuarios).
--  Es catalogo cerrado: se siembra al instalar y rara vez cambia.
-- ---------------------------------------------------------------------
CREATE TABLE roles (
  id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(50)      NOT NULL COMMENT 'Administrador, Secretaria, Lector',
  descripcion VARCHAR(150)     NULL,
  activo      TINYINT(1)       NOT NULL DEFAULT 1,
  created_at  DATETIME         NULL,
  updated_at  DATETIME         NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Catalogo de perfiles de acceso';


-- ---------------------------------------------------------------------
--  usuarios
--  QUE ES: las personas que operan el sistema (no los clientes del agua).
--  PARA QUE SIRVE: login, y trazabilidad. Cada lectura y cada pago guardan
--  quien lo registro, que es lo que permite saber que lector visito un
--  predio o que secretaria recibio un dinero.
--  IMPORTANTE: se guarda password_hash, nunca la contraseña en texto.
--  Se usa password_hash() de PHP; por eso VARCHAR(255) y no menos.
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
  id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  rol_id        TINYINT UNSIGNED NOT NULL,
  nombre        VARCHAR(120)     NOT NULL,
  email         VARCHAR(150)     NOT NULL COMMENT 'Credencial de acceso',
  password_hash VARCHAR(255)     NOT NULL COMMENT 'Resultado de password_hash() de PHP',
  activo        TINYINT(1)       NOT NULL DEFAULT 1 COMMENT 'Baja logica: 0 = no puede iniciar sesion',
  created_at    DATETIME         NULL,
  updated_at    DATETIME         NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email),
  KEY idx_usuarios_rol (rol_id),
  CONSTRAINT fk_usuarios_rol
    FOREIGN KEY (rol_id) REFERENCES roles (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Operadores del sistema: administrador, secretaria, lectores';


-- #####################################################################
--  BLOQUE 2 - CATALOGOS DE CONFIGURACION
--  Son los mantenimientos que administra el rol Administrador.
-- #####################################################################

-- ---------------------------------------------------------------------
--  sectores
--  QUE ES: la division geografica de la comunidad (zona, barrio, ruta).
--  PARA QUE SIRVE: es como el lector organiza su trabajo. La pantalla
--  principal del Lector filtra "pendientes de este periodo POR SECTOR",
--  porque nadie recorre 477 predios de un solo tiron: recorre su ruta.
--  POR QUE ES TABLA Y NO UN CAMPO DE TEXTO: si fuera texto libre en
--  servicios, tendria "Matambre", "matambre " y "MATAMBRE" como tres
--  sectores distintos y el filtro dejaria de servir.
-- ---------------------------------------------------------------------
CREATE TABLE sectores (
  id          SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre      VARCHAR(80)       NOT NULL COMMENT 'Zona, barrio o ruta de lectura',
  descripcion VARCHAR(200)      NULL,
  activo      TINYINT(1)        NOT NULL DEFAULT 1,
  created_at  DATETIME          NULL,
  updated_at  DATETIME          NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_sectores_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Zonas o rutas en que se organizan los servicios';


-- ---------------------------------------------------------------------
--  tarifas
--  QUE ES: el precio del agua, con vigencia en el tiempo.
--  PARA QUE SIRVE: calcular cuanto se cobra por el consumo medido.
--
--  ESTA ES LA TABLA MAS DELICADA DEL SISTEMA. Regla de negocio RN-05:
--  UNA TARIFA NUNCA SE EDITA. Cuando la junta sube el precio, NO se
--  modifica la fila existente: se le pone vigente_hasta a la actual y se
--  inserta una nueva. Editarla reescribiria la historia y descuadraria
--  todos los recibos ya emitidos con el precio viejo.
--
--  vigente_hasta = NULL significa "esta es la tarifa vigente hoy".
--  Solo debe existir UNA fila con vigente_hasta NULL a la vez (la regla
--  se aplica en la capa de servicio, dentro de una transaccion).
--
--  FORMULA MVP (cuota_minima = 0):
--      monto = consumo * precio_unitario
--  FORMULA GENERAL que el modelo tambien soporta sin migracion:
--      monto = max(cuota_minima, consumo * precio_unitario)
--  cuota_minima queda en 0 por decision de alcance. Si la oficina confirma
--  que cobra un minimo mensual, solo se cambia el valor y se agrega el
--  max() en el servicio de calculo.
-- ---------------------------------------------------------------------
CREATE TABLE tarifas (
  id              SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  descripcion     VARCHAR(150)      NULL COMMENT 'Ej: Tarifa residencial 2026',
  precio_unitario DECIMAL(10,2)     NOT NULL COMMENT 'Precio por unidad de consumo (m3)',
  cuota_minima    DECIMAL(10,2)     NOT NULL DEFAULT 0.00 COMMENT '0 = sin cobro minimo',
  vigente_desde   DATE              NOT NULL,
  vigente_hasta   DATE              NULL COMMENT 'NULL = tarifa vigente actualmente',
  activo          TINYINT(1)        NOT NULL DEFAULT 1,
  created_at      DATETIME          NULL,
  updated_at      DATETIME          NULL,
  PRIMARY KEY (id),
  KEY idx_tarifas_vigencia (vigente_desde, vigente_hasta),
  CONSTRAINT chk_tarifas_precio   CHECK (precio_unitario > 0),
  CONSTRAINT chk_tarifas_minima   CHECK (cuota_minima >= 0),
  CONSTRAINT chk_tarifas_vigencia CHECK (vigente_hasta IS NULL OR vigente_hasta >= vigente_desde)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historico de tarifas. Nunca se editan, se cierran y se crea una nueva';


-- ---------------------------------------------------------------------
--  periodos
--  QUE ES: el mes de facturacion.
--  PARA QUE SIRVE: dos cosas concretas.
--    1) Es el ancla de "que predios faltan por leer ESTE MES". Sin el,
--       esa consulta seria aritmetica de fechas fragil en cada pantalla.
--    2) estado = 'cerrado' impide que en septiembre alguien registre una
--       lectura de marzo y descuadre los historicos (RN-10).
--  NO NECESITA UN CRUD COMPLETO: el periodo se crea solo al registrarse la
--  primera lectura del mes; la unica accion del administrador es cerrarlo.
-- ---------------------------------------------------------------------
CREATE TABLE periodos (
  id           SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  anio         SMALLINT UNSIGNED NOT NULL,
  mes          TINYINT UNSIGNED  NOT NULL COMMENT '1 a 12',
  estado       ENUM('abierto','cerrado') NOT NULL DEFAULT 'abierto',
  fecha_cierre DATE              NULL,
  created_at   DATETIME          NULL,
  updated_at   DATETIME          NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_periodos_anio_mes (anio, mes),
  CONSTRAINT chk_periodos_mes  CHECK (mes BETWEEN 1 AND 12),
  CONSTRAINT chk_periodos_anio CHECK (anio BETWEEN 2000 AND 2100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mes de facturacion. Cerrarlo bloquea cambios en su historico';


-- #####################################################################
--  BLOQUE 3 - CLIENTES, PREDIOS Y APARATOS
--  Cadena: cliente -> servicio (predio) -> contador (aparato fisico)
-- #####################################################################

-- ---------------------------------------------------------------------
--  clientes
--  QUE ES: la persona titular del servicio.
--  PARA QUE SIRVE: identificar a quien se le cobra y como contactarlo.
--  OJO: aqui NO va la direccion. La direccion pertenece al predio, no a
--  la persona, porque un mismo cliente puede tener varios predios en
--  direcciones distintas. Esa columna vive en `servicios`.
-- ---------------------------------------------------------------------
CREATE TABLE clientes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nombre     VARCHAR(150) NOT NULL,
  telefono   VARCHAR(20)  NULL,
  activo     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at DATETIME     NULL,
  updated_at DATETIME     NULL,
  PRIMARY KEY (id),
  KEY idx_clientes_nombre (nombre) COMMENT 'La secretaria busca por nombre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Titulares de los servicios de agua';


-- ---------------------------------------------------------------------
--  servicios
--  QUE ES: el predio o conexion de agua. ES LA UNIDAD QUE SE FACTURA.
--  PARA QUE SIRVE: es el centro del sistema. Todo lo demas cuelga de aqui:
--  el contador se instala en un servicio, la lectura se hace a un servicio,
--  el recibo se emite por un servicio.
--
--  POR QUE EXISTE SEPARADA DEL CLIENTE: un cliente puede tener casa, local
--  y taller. Cada uno se lee y se cobra por separado.
--  POR QUE EXISTE SEPARADA DEL CONTADOR: el predio es permanente, el
--  aparato se daña y se cambia. Separarlos conserva el historial.
--
--  estado = 'suspendido' significa que el servicio no se factura este mes
--  (corte, solicitud del cliente, predio deshabitado).
-- ---------------------------------------------------------------------
CREATE TABLE servicios (
  id         INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  cliente_id INT UNSIGNED      NOT NULL,
  sector_id  SMALLINT UNSIGNED NOT NULL,
  codigo     VARCHAR(20)       NOT NULL COMMENT 'Codigo visible del servicio, ej: S-0001',
  direccion  VARCHAR(200)      NULL COMMENT 'Ubicacion fisica del predio',
  fecha_alta DATE              NOT NULL,
  estado     ENUM('activo','suspendido') NOT NULL DEFAULT 'activo',
  created_at DATETIME          NULL,
  updated_at DATETIME          NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_servicios_codigo (codigo),
  KEY idx_servicios_cliente (cliente_id),
  KEY idx_servicios_ruta (sector_id, estado)
    COMMENT 'Sostiene la consulta principal del Lector: pendientes por sector',
  CONSTRAINT fk_servicios_cliente
    FOREIGN KEY (cliente_id) REFERENCES clientes (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_servicios_sector
    FOREIGN KEY (sector_id) REFERENCES sectores (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Predios o conexiones de agua. Es la unidad que se factura';


-- ---------------------------------------------------------------------
--  contadores
--  QUE ES: el aparato medidor fisico instalado en un predio.
--  PARA QUE SIRVE: llevar la cadena de mediciones. Un servicio puede tener
--  varios contadores A LO LARGO DEL TIEMPO (uno se daña, se instala otro),
--  pero solo uno activo a la vez.
--
--  lectura_inicial: con cuanto arranca el aparato. Resuelve dos casos:
--    - servicio nuevo sin lecturas previas (arranca en 0)
--    - contador de reemplazo, que arranca en 0 aunque el viejo iba en 9,325.
--  Sin este campo, el primer mes de cada contador no se podria calcular.
--
--  REGLA QUE LA BASE DE DATOS NO PUEDE GARANTIZAR (RN-01):
--  "solo un contador activo por servicio". MariaDB no soporta indices
--  unicos parciales (el WHERE activo = 1 de PostgreSQL), y un
--  UNIQUE(servicio_id, fecha_retiro) tampoco sirve porque MySQL/MariaDB
--  permite multiples NULL en un indice unico, que es justo el valor que
--  tendria el contador vigente. Por eso la regla se implementa en la capa
--  de servicio: al instalar uno nuevo se cierra el anterior dentro de una
--  transaccion. Documentado a proposito para que nadie lo "arregle" luego.
--
--  uq_contadores_id_servicio: parece redundante (id ya es PK), pero es
--  OBLIGATORIO. Es lo que permite que `lecturas` declare una FK compuesta
--  hacia (id, servicio_id) y asi el motor rechace fisicamente una lectura
--  que mezcle el contador de un predio con otro predio distinto.
-- ---------------------------------------------------------------------
CREATE TABLE contadores (
  id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  servicio_id       INT UNSIGNED  NOT NULL,
  numero_serie      VARCHAR(40)   NULL COMMENT 'NULL permitido: no toda oficina lo registra',
  lectura_inicial   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  fecha_instalacion DATE          NOT NULL,
  fecha_retiro      DATE          NULL COMMENT 'NULL = contador vigente',
  activo            TINYINT(1)    NOT NULL DEFAULT 1,
  created_at        DATETIME      NULL,
  updated_at        DATETIME      NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_contadores_serie (numero_serie),
  UNIQUE KEY uq_contadores_id_servicio (id, servicio_id)
    COMMENT 'Necesaria para la FK compuesta de lecturas. No borrar',
  KEY idx_contadores_servicio (servicio_id, activo),
  CONSTRAINT fk_contadores_servicio
    FOREIGN KEY (servicio_id) REFERENCES servicios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_contadores_inicial CHECK (lectura_inicial >= 0),
  CONSTRAINT chk_contadores_fechas
    CHECK (fecha_retiro IS NULL OR fecha_retiro >= fecha_instalacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Aparatos medidores. Historico de instalacion y reemplazo';


-- #####################################################################
--  BLOQUE 4 - NUCLEO OPERATIVO
--  lectura -> recibo -> pago. Es el flujo que hay que demostrar en el demo.
-- #####################################################################

-- ---------------------------------------------------------------------
--  lecturas
--  QUE ES: la medicion que el lector toma en campo, una vez por mes.
--  PARA QUE SIRVE: es el hecho central del sistema. De aqui sale el
--  consumo, el monto y el recibo.
--
--  POR QUE GUARDA servicio_id Y contador_id (no es redundancia evitable):
--    - la FACTURACION sigue al SERVICIO -> por eso el UNIQUE va sobre
--      (servicio_id, periodo_id): un predio se cobra una sola vez al mes,
--      aunque le hayan cambiado el medidor a media semana.
--    - la CADENA DE MEDICIONES sigue al CONTADOR -> la lectura anterior se
--      busca por contador_id, porque el aparato nuevo arranca de cero.
--
--  fk_lecturas_contador es COMPUESTA: garantiza a nivel de motor que el
--  contador indicado realmente pertenece al servicio indicado.
--
--  DENORMALIZACION INTENCIONAL (RN-06): lectura_anterior, consumo y
--  monto_consumo son valores calculados que igual se almacenan. Es un
--  SNAPSHOT: el recibo de marzo debe decir exactamente lo mismo dentro de
--  dos años, aunque despues cambie la tarifa o se corrija una lectura vieja.
--  tarifa_id se guarda ademas para poder auditar QUE tarifa se aplico.
--
--  COMO SE OBTIENE lectura_anterior (RN-03): NUNCA se teclea. Se consulta
--  la ultima lectura del contador_id vigente; si ese contador todavia no
--  tiene lecturas, se usa contadores.lectura_inicial.
-- ---------------------------------------------------------------------
CREATE TABLE lecturas (
  id               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
  servicio_id      INT UNSIGNED      NOT NULL,
  contador_id      INT UNSIGNED      NOT NULL,
  periodo_id       SMALLINT UNSIGNED NOT NULL,
  tarifa_id        SMALLINT UNSIGNED NOT NULL COMMENT 'Trazabilidad de la tarifa aplicada',
  usuario_id       INT UNSIGNED      NOT NULL COMMENT 'Lector que tomo la medicion',
  lectura_anterior DECIMAL(10,2)     NOT NULL DEFAULT 0.00,
  lectura_actual   DECIMAL(10,2)     NOT NULL,
  consumo          DECIMAL(10,2)     NOT NULL COMMENT 'Snapshot: lectura_actual - lectura_anterior',
  monto_consumo    DECIMAL(10,2)     NOT NULL COMMENT 'Snapshot del calculo con la tarifa vigente',
  fecha_lectura    DATE              NOT NULL,
  observaciones    VARCHAR(255)      NULL,
  created_at       DATETIME          NULL,
  updated_at       DATETIME          NULL,
  PRIMARY KEY (id),

  -- LA RESTRICCION MAS IMPORTANTE DE TODO EL MODELO (RN-02):
  -- impide cobrar dos veces el mismo periodo al mismo predio.
  UNIQUE KEY uq_lecturas_servicio_periodo (servicio_id, periodo_id),

  KEY idx_lecturas_contador (contador_id, fecha_lectura)
    COMMENT 'Recuperacion de la lectura previa del aparato',
  KEY fk_lecturas_contador_idx (contador_id, servicio_id),
  KEY idx_lecturas_periodo (periodo_id),
  KEY idx_lecturas_tarifa (tarifa_id),
  KEY idx_lecturas_usuario (usuario_id),

  CONSTRAINT fk_lecturas_servicio
    FOREIGN KEY (servicio_id) REFERENCES servicios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  -- FK COMPUESTA: el contador debe pertenecer a ESE servicio.
  CONSTRAINT fk_lecturas_contador
    FOREIGN KEY (contador_id, servicio_id) REFERENCES contadores (id, servicio_id)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  CONSTRAINT fk_lecturas_periodo
    FOREIGN KEY (periodo_id) REFERENCES periodos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_lecturas_tarifa
    FOREIGN KEY (tarifa_id) REFERENCES tarifas (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_lecturas_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,

  -- RN-04: el contador no puede retroceder. Los casos legitimos que lo
  -- violan (cambio de medidor, vuelta del contador) se resuelven
  -- registrando un contador nuevo, no relajando esta regla.
  CONSTRAINT chk_lecturas_orden   CHECK (lectura_actual >= lectura_anterior),
  CONSTRAINT chk_lecturas_consumo CHECK (consumo >= 0),
  CONSTRAINT chk_lecturas_monto   CHECK (monto_consumo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Mediciones mensuales. Hecho central del sistema';


-- ---------------------------------------------------------------------
--  recibos
--  QUE ES: el documento de cobro que se imprime y se entrega al cliente.
--  PARA QUE SIRVE: separar la MEDICION (un hecho tecnico) del DOCUMENTO
--  (un hecho administrativo, con numero, fecha de emision y posibilidad
--  de anularse). Fusionarlos impediria anular un cobro sin borrar la
--  lectura que lo origino.
--
--  numero NO ES LA PK: la PK es el `id` interno. `numero` es el correlativo
--  del talonario, que puede saltar, reiniciarse con un talonario nuevo o
--  venir de una serie que empezo antes de que existiera el sistema.
--  RN-09: generarlo con MAX(numero)+1 suelto es una condicion de carrera;
--  dos secretarias emitiendo al mismo tiempo sacarian el mismo numero.
--  Debe generarse dentro de una transaccion con bloqueo.
--
--  lectura_id es NOT NULL y UNIQUE: todo recibo nace de una lectura, y una
--  lectura genera a lo sumo un recibo. Los cargos que no son consumo
--  (una reconexion, un ajuste) van en monto_adicional del recibo del mes.
--
--  RN-08: ANULAR NUNCA ES DELETE. Se marca estado = 'anulado' con su motivo.
--  El numero queda quemado pero la serie sigue cuadrando, y un recibo
--  anulado no participa en el estado de cuenta.
-- ---------------------------------------------------------------------
CREATE TABLE recibos (
  id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  lectura_id         INT UNSIGNED  NOT NULL,
  numero             VARCHAR(20)   NOT NULL COMMENT 'Correlativo del talonario. No es la PK',
  fecha_emision      DATE          NOT NULL,
  monto_consumo      DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Copiado de la lectura',
  monto_adicional    DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Cargo extra puntual',
  concepto_adicional VARCHAR(150)  NULL COMMENT 'Descripcion del cargo extra',
  total              DECIMAL(10,2) NOT NULL,
  estado             ENUM('vigente','anulado') NOT NULL DEFAULT 'vigente',
  motivo_anulacion   VARCHAR(200)  NULL,
  created_at         DATETIME      NULL,
  updated_at         DATETIME      NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_recibos_numero (numero),
  UNIQUE KEY uq_recibos_lectura (lectura_id) COMMENT 'Una lectura, un solo recibo',
  KEY idx_recibos_estado_fecha (estado, fecha_emision),
  CONSTRAINT fk_recibos_lectura
    FOREIGN KEY (lectura_id) REFERENCES lecturas (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_recibos_montos CHECK (
    monto_consumo >= 0 AND monto_adicional >= 0 AND total >= 0
  ),
  -- El total debe cuadrar siempre. DECIMAL es aritmetica exacta,
  -- por eso comparar por igualdad aqui es seguro.
  CONSTRAINT chk_recibos_total CHECK (total = monto_consumo + monto_adicional)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Documentos de cobro imprimibles';


-- ---------------------------------------------------------------------
--  pagos
--  QUE ES: el dinero que la secretaria recibe contra un recibo.
--  PARA QUE SIRVE: alimentar el dashboard de estado de cuenta.
--
--  ES 1:N CON RECIBOS, NO 1:1. Cuesta lo mismo y permite abonos parciales
--  sin rediseñar nada.
--
--  RN-07: EL ESTADO "PAGADO" NO SE ALMACENA, SE DERIVA:
--      pendiente  si  SUM(pagos.monto) <  recibos.total
--      pagado     si  SUM(pagos.monto) >= recibos.total
--  Guardar un campo estado_pago se desincroniza el dia que alguien
--  registre un pago por SQL directo. Si el dashboard queda lento, se
--  resuelve con una VISTA, no duplicando el dato.
--
--  referencia: numero de boleta de deposito, de transferencia o de
--  documento, segun el metodo. Es la version generica y sin tabla extra
--  de la conciliacion bancaria, que esta fuera del alcance MVP.
-- ---------------------------------------------------------------------
CREATE TABLE pagos (
  id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  recibo_id     INT UNSIGNED  NOT NULL,
  usuario_id    INT UNSIGNED  NOT NULL COMMENT 'Quien recibio el pago',
  monto         DECIMAL(10,2) NOT NULL,
  fecha_pago    DATE          NOT NULL,
  metodo        ENUM('efectivo','deposito','transferencia') NOT NULL DEFAULT 'efectivo',
  referencia    VARCHAR(50)   NULL COMMENT 'No. de boleta, transferencia o documento',
  observaciones VARCHAR(200)  NULL,
  created_at    DATETIME      NULL,
  updated_at    DATETIME      NULL,
  PRIMARY KEY (id),
  KEY idx_pagos_recibo (recibo_id),
  KEY idx_pagos_fecha (fecha_pago),
  KEY idx_pagos_usuario (usuario_id),
  CONSTRAINT fk_pagos_recibo
    FOREIGN KEY (recibo_id) REFERENCES recibos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_pagos_usuario
    FOREIGN KEY (usuario_id) REFERENCES usuarios (id)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_pagos_monto CHECK (monto > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Cobros recibidos. Permite abonos parciales sobre un mismo recibo';


-- #####################################################################
--  BLOQUE 5 - DATOS BASE MINIMOS
--  Solo el catalogo de roles, que el sistema necesita para funcionar.
--  Los datos de prueba (clientes, servicios, lecturas) van en Seeders de
--  CodeIgniter 4, no aqui: asi cada integrante puede regenerarlos.
-- #####################################################################

INSERT INTO roles (nombre, descripcion, created_at) VALUES
  ('Administrador', 'Usuarios, roles, tarifas y mantenimientos generales', NOW()),
  ('Secretaria',    'Clientes, servicios/predios y registro de pagos',     NOW()),
  ('Lector',        'Consulta de pendientes y registro de lecturas',       NOW());

-- NOTA: el usuario administrador inicial NO se crea aqui a proposito.
-- Su password debe pasar por password_hash() de PHP, asi que se crea con
-- un Seeder de CodeIgniter 4. Insertar un hash a mano en este script
-- terminaria con una contraseña conocida y versionada en GitHub.
