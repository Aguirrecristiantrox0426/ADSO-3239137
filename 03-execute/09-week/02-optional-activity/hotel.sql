CREATE DATABASE IF NOT EXISTS hotel;
USE hotel;

-- =========================
-- 🔹 TABLA BASE (AUDITORÍA)
-- =========================

-- NOTA: se aplica en todas las tablas

-- =========================
-- 🟢 PARAMETRIZACIÓN
-- =========================

CREATE TABLE cliente (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(20),
    numero_documento VARCHAR(50),
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    status VARCHAR(30) DEFAULT 'ACTIVE'
);

CREATE TABLE tipo_dia (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100),
    fecha DATE,
    aplica_temporada BOOLEAN,
    aplica_feriado BOOLEAN,
    aplica_especial BOOLEAN,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    status VARCHAR(30) DEFAULT 'ACTIVE'
);

CREATE TABLE metodo_pago (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100),
    requiere_referencia BOOLEAN,
    permite_pago_parcial BOOLEAN,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    status VARCHAR(30) DEFAULT 'ACTIVE'
);

CREATE TABLE empresa (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    nit VARCHAR(50),
    razon_social VARCHAR(150),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(150),
    sitio_web VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    status VARCHAR(30) DEFAULT 'ACTIVE'
);

CREATE TABLE informacion_legal (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED,
    tipo_documento_legal VARCHAR(50),
    numero_documento_legal VARCHAR(50),
    descripcion TEXT,
    fecha_expedicion DATE,
    fecha_vencimiento DATE,
    FOREIGN KEY (empresa_id) REFERENCES empresa(id)
);

CREATE TABLE precio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_habitacion_id BIGINT UNSIGNED,
    tipo_dia_id BIGINT UNSIGNED,
    valor DECIMAL(10,2),
    fecha_inicio DATE,
    fecha_fin DATE,
    condicion VARCHAR(100)
);

-- =========================
-- 🔵 DISTRIBUCIÓN
-- =========================

CREATE TABLE tipo_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100),
    capacidad_base INT,
    capacidad_maxima INT
);

CREATE TABLE estado_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100),
    permite_reserva BOOLEAN,
    permite_check_in BOOLEAN
);

CREATE TABLE sede (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    empresa_id BIGINT UNSIGNED,
    nombre VARCHAR(100),
    direccion VARCHAR(150),
    ciudad VARCHAR(50),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    FOREIGN KEY (empresa_id) REFERENCES empresa(id)
);

CREATE TABLE habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sede_id BIGINT UNSIGNED,
    tipo_habitacion_id BIGINT UNSIGNED,
    estado_habitacion_id BIGINT UNSIGNED,
    numero VARCHAR(10),
    piso INT,
    capacidad INT,
    descripcion VARCHAR(150),
    FOREIGN KEY (sede_id) REFERENCES sede(id),
    FOREIGN KEY (tipo_habitacion_id) REFERENCES tipo_habitacion(id),
    FOREIGN KEY (estado_habitacion_id) REFERENCES estado_habitacion(id)
);

-- =========================
-- 🟣 INVENTARIO
-- =========================

CREATE TABLE proveedor (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    nit VARCHAR(50),
    telefono VARCHAR(20),
    correo VARCHAR(100),
    direccion VARCHAR(150)
);

CREATE TABLE producto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    proveedor_id BIGINT UNSIGNED,
    nombre VARCHAR(100),
    descripcion VARCHAR(150),
    valor_venta DECIMAL(10,2),
    stock_actual INT,
    stock_minimo INT,
    FOREIGN KEY (proveedor_id) REFERENCES proveedor(id)
);

CREATE TABLE servicio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    descripcion VARCHAR(150),
    valor_venta DECIMAL(10,2),
    disponible BOOLEAN
);

CREATE TABLE seguimiento_producto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    producto_id BIGINT UNSIGNED,
    tipo_movimiento VARCHAR(50),
    cantidad INT,
    fecha_movimiento DATETIME,
    observacion VARCHAR(150),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

-- =========================
-- 🟡 PRESTACIÓN DE SERVICIO
-- =========================

CREATE TABLE reserva_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id BIGINT UNSIGNED,
    habitacion_id BIGINT UNSIGNED,
    fecha_inicio DATE,
    fecha_fin DATE,
    cantidad_persona INT,
    estado_reserva VARCHAR(50),
    valor_estimado DECIMAL(10,2),
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (habitacion_id) REFERENCES habitacion(id)
);

CREATE TABLE cancelacion_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_habitacion_id BIGINT UNSIGNED,
    motivo VARCHAR(150),
    fecha_cancelacion DATETIME,
    aplica_penalidad BOOLEAN,
    valor_penalidad DECIMAL(10,2),
    FOREIGN KEY (reserva_habitacion_id) REFERENCES reserva_habitacion(id)
);

CREATE TABLE disponibilidad_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    habitacion_id BIGINT UNSIGNED,
    fecha_inicio DATE,
    fecha_fin DATE,
    disponible BOOLEAN,
    motivo_no_disponible VARCHAR(150),
    FOREIGN KEY (habitacion_id) REFERENCES habitacion(id)
);

CREATE TABLE estadia (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_habitacion_id BIGINT UNSIGNED,
    cliente_id BIGINT UNSIGNED,
    habitacion_id BIGINT UNSIGNED,
    fecha_inicio DATE,
    fecha_fin DATE,
    estado_estadia VARCHAR(50),
    FOREIGN KEY (reserva_habitacion_id) REFERENCES reserva_habitacion(id),
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (habitacion_id) REFERENCES habitacion(id)
);

CREATE TABLE check_in (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_habitacion_id BIGINT UNSIGNED,
    empleado_id BIGINT UNSIGNED,
    fecha_hora_ingreso DATETIME,
    observacion VARCHAR(150)
);

CREATE TABLE check_out (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estadia_id BIGINT UNSIGNED,
    empleado_id BIGINT UNSIGNED,
    fecha_hora_salida DATETIME,
    observacion VARCHAR(150),
    valor_total DECIMAL(10,2)
);

CREATE TABLE venta_producto (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estadia_id BIGINT UNSIGNED,
    producto_id BIGINT UNSIGNED,
    cantidad INT,
    valor_unitario DECIMAL(10,2),
    valor_total DECIMAL(10,2),
    FOREIGN KEY (estadia_id) REFERENCES estadia(id),
    FOREIGN KEY (producto_id) REFERENCES producto(id)
);

CREATE TABLE venta_servicio (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estadia_id BIGINT UNSIGNED,
    servicio_id BIGINT UNSIGNED,
    cantidad INT,
    valor_unitario DECIMAL(10,2),
    valor_total DECIMAL(10,2),
    FOREIGN KEY (estadia_id) REFERENCES estadia(id),
    FOREIGN KEY (servicio_id) REFERENCES servicio(id)
);

-- =========================
-- 🔴 FACTURACIÓN
-- =========================

CREATE TABLE factura (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cliente_id BIGINT UNSIGNED,
    estadia_id BIGINT UNSIGNED,
    numero_factura VARCHAR(50),
    fecha_emision DATETIME,
    subtotal DECIMAL(10,2),
    impuesto DECIMAL(10,2),
    descuento DECIMAL(10,2),
    total DECIMAL(10,2),
    estado_factura VARCHAR(50),
    FOREIGN KEY (cliente_id) REFERENCES cliente(id),
    FOREIGN KEY (estadia_id) REFERENCES estadia(id)
);

CREATE TABLE detalle_compra (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    factura_id BIGINT UNSIGNED,
    producto_id BIGINT UNSIGNED,
    servicio_id BIGINT UNSIGNED,
    descripcion VARCHAR(150),
    cantidad INT,
    valor_unitario DECIMAL(10,2),
    valor_total DECIMAL(10,2),
    FOREIGN KEY (factura_id) REFERENCES factura(id)
);

CREATE TABLE pago_parcial (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reserva_habitacion_id BIGINT UNSIGNED,
    factura_id BIGINT UNSIGNED,
    metodo_pago_id BIGINT UNSIGNED,
    valor DECIMAL(10,2),
    fecha_pago DATETIME,
    referencia_pago VARCHAR(100),
    FOREIGN KEY (reserva_habitacion_id) REFERENCES reserva_habitacion(id),
    FOREIGN KEY (factura_id) REFERENCES factura(id),
    FOREIGN KEY (metodo_pago_id) REFERENCES metodo_pago(id)
);

-- =========================
-- 🔐 SEGURIDAD
-- =========================

CREATE TABLE persona (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo_documento VARCHAR(20),
    numero_documento VARCHAR(50),
    nombre VARCHAR(100),
    apellido VARCHAR(100),
    telefono VARCHAR(20),
    correo VARCHAR(100)
);

CREATE TABLE usuario (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    persona_id BIGINT UNSIGNED,
    username VARCHAR(50),
    password_hash VARCHAR(255),
    ultimo_acceso DATETIME,
    bloqueado BOOLEAN,
    FOREIGN KEY (persona_id) REFERENCES persona(id)
);

CREATE TABLE rol (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100)
);

CREATE TABLE permiso (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50),
    descripcion VARCHAR(100),
    accion VARCHAR(50)
);

CREATE TABLE usuario_rol (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id BIGINT UNSIGNED,
    rol_id BIGINT UNSIGNED,
    FOREIGN KEY (usuario_id) REFERENCES usuario(id),
    FOREIGN KEY (rol_id) REFERENCES rol(id)
);

CREATE TABLE rol_permiso (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol_id BIGINT UNSIGNED,
    permiso_id BIGINT UNSIGNED,
    FOREIGN KEY (rol_id) REFERENCES rol(id),
    FOREIGN KEY (permiso_id) REFERENCES permiso(id)
);

-- =========================
-- 🔧 MANTENIMIENTO
-- =========================

CREATE TABLE mantenimiento_habitacion (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    habitacion_id BIGINT UNSIGNED,
    empleado_id BIGINT UNSIGNED,
    tipo_mantenimiento VARCHAR(50),
    fecha_inicio DATE,
    fecha_fin DATE,
    estado_mantenimiento VARCHAR(50),
    observacion VARCHAR(150),
    FOREIGN KEY (habitacion_id) REFERENCES habitacion(id)
);
USE hotel;

-- =========================
-- 🔹 INSERTS BASE
-- =========================

-- EMPRESA
INSERT INTO empresa (nombre, nit, razon_social, telefono, correo, direccion, sitio_web) VALUES
('Hotel Bogotá', '900123', 'Hotel Bogotá SAS', '300123', 'hotel@mail.com', 'Centro', 'www.hotel.com'),
('Hotel Norte', '900124', 'Hotel Norte SAS', '300124', 'norte@mail.com', 'Norte', 'www.norte.com'),
('Hotel Sur', '900125', 'Hotel Sur SAS', '300125', 'sur@mail.com', 'Sur', 'www.sur.com'),
('Hotel Este', '900126', 'Hotel Este SAS', '300126', 'este@mail.com', 'Este', 'www.este.com'),
('Hotel Oeste', '900127', 'Hotel Oeste SAS', '300127', 'oeste@mail.com', 'Oeste', 'www.oeste.com');

-- SEDE
INSERT INTO sede (empresa_id, nombre, direccion, ciudad, telefono, correo) VALUES
(1,'Centro','Calle 1','Bogotá','111','c1@mail.com'),
(1,'Norte','Calle 2','Bogotá','222','c2@mail.com'),
(2,'Sur','Calle 3','Bogotá','333','c3@mail.com'),
(3,'Este','Calle 4','Bogotá','444','c4@mail.com'),
(4,'Oeste','Calle 5','Bogotá','555','c5@mail.com');

-- CLIENTE
INSERT INTO cliente (tipo_documento, numero_documento, nombre, apellido, telefono, correo, direccion) VALUES
('CC','1','Juan','Perez','111','juan@mail.com','Bogotá'),
('CC','2','Ana','Lopez','222','ana@mail.com','Bogotá'),
('CC','3','Luis','Gomez','333','luis@mail.com','Bogotá'),
('CC','4','Maria','Diaz','444','maria@mail.com','Bogotá'),
('CC','5','Carlos','Ruiz','555','carlos@mail.com','Bogotá');

-- TIPO HABITACION
INSERT INTO tipo_habitacion (nombre, descripcion, capacidad_base, capacidad_maxima) VALUES
('Simple','1 cama',1,2),
('Doble','2 camas',2,4),
('Suite','lujo',2,5),
('Familiar','grande',4,6),
('Premium','vip',2,3);

-- ESTADO HABITACION
INSERT INTO estado_habitacion (nombre, descripcion, permite_reserva, permite_check_in) VALUES
('Disponible','ok',1,1),
('Ocupada','uso',0,0),
('Mantenimiento','no',0,0),
('Limpieza','espera',0,0),
('Reservada','pendiente',1,0);

-- HABITACION
INSERT INTO habitacion (sede_id, tipo_habitacion_id, estado_habitacion_id, numero, piso, capacidad) VALUES
(1,1,1,'101',1,2),
(1,2,1,'102',1,4),
(2,3,1,'201',2,5),
(2,4,1,'202',2,6),
(3,5,1,'301',3,3);

-- PROVEEDOR
INSERT INTO proveedor (nombre, nit, telefono, correo, direccion) VALUES
('Proveedor 1','123','111','p1@mail.com','Bogotá'),
('Proveedor 2','124','222','p2@mail.com','Bogotá'),
('Proveedor 3','125','333','p3@mail.com','Bogotá'),
('Proveedor 4','126','444','p4@mail.com','Bogotá'),
('Proveedor 5','127','555','p5@mail.com','Bogotá');

-- PRODUCTO
INSERT INTO producto (proveedor_id, nombre, descripcion, valor_venta, stock_actual, stock_minimo) VALUES
(1,'Agua','botella',2000,50,10),
(2,'Gaseosa','lata',3000,40,10),
(3,'Snack','papas',5000,30,5),
(4,'Cerveza','botella',7000,20,5),
(5,'Chocolate','dulce',4000,25,5);

-- SERVICIO
INSERT INTO servicio (nombre, descripcion, valor_venta, disponible) VALUES
('Spa','relajación',50000,1),
('Lavandería','ropa',20000,1),
('Transporte','aeropuerto',30000,1),
('Room Service','comida',25000,1),
('Tour','ciudad',60000,1);

-- RESERVA
INSERT INTO reserva_habitacion (cliente_id, habitacion_id, fecha_inicio, fecha_fin, cantidad_persona, estado_reserva, valor_estimado) VALUES
(1,1,'2026-06-01','2026-06-03',2,'ACTIVA',200000),
(2,2,'2026-06-02','2026-06-04',2,'ACTIVA',250000),
(3,3,'2026-06-03','2026-06-05',3,'ACTIVA',300000),
(4,4,'2026-06-04','2026-06-06',4,'ACTIVA',350000),
(5,5,'2026-06-05','2026-06-07',2,'ACTIVA',400000);

-- ESTADIA
INSERT INTO estadia (reserva_habitacion_id, cliente_id, habitacion_id, fecha_inicio, fecha_fin, estado_estadia) VALUES
(1,1,1,'2026-06-01','2026-06-03','ACTIVA'),
(2,2,2,'2026-06-02','2026-06-04','ACTIVA'),
(3,3,3,'2026-06-03','2026-06-05','ACTIVA'),
(4,4,4,'2026-06-04','2026-06-06','ACTIVA'),
(5,5,5,'2026-06-05','2026-06-07','ACTIVA');

-- FACTURA
INSERT INTO factura (cliente_id, estadia_id, numero_factura, fecha_emision, subtotal, impuesto, descuento, total, estado_factura) VALUES
(1,1,'F001',NOW(),100000,19000,0,119000,'PAGADA'),
(2,2,'F002',NOW(),120000,22800,0,142800,'PAGADA'),
(3,3,'F003',NOW(),150000,28500,0,178500,'PENDIENTE'),
(4,4,'F004',NOW(),200000,38000,0,238000,'PAGADA'),
(5,5,'F005',NOW(),250000,47500,0,297500,'PENDIENTE');

-- =========================
-- ✏️ UPDATE
-- =========================

UPDATE cliente SET telefono='999999' WHERE id=1;
UPDATE habitacion SET estado_habitacion_id=2 WHERE id=1;
UPDATE reserva_habitacion SET estado_reserva='CANCELADA' WHERE id=2;
UPDATE estadia SET estado_estadia='FINALIZADA' WHERE id=1;
UPDATE empresa SET nombre='Hotel Bogotá Premium' WHERE id=1;

-- =========================
-- ❌ DELETE (seguros)
-- =========================

DELETE FROM factura WHERE id=5;
DELETE FROM estadia WHERE id=5;
DELETE FROM reserva_habitacion WHERE id=5;
DELETE FROM habitacion WHERE id=5;
DELETE FROM cliente WHERE id=5;

-- =========================
-- 🔍 CONSULTAS (INNER JOIN)
-- =========================

-- Cliente + reserva + habitación
SELECT c.nombre, h.numero, r.fecha_inicio, r.fecha_fin
FROM cliente c
INNER JOIN reserva_habitacion r ON c.id = r.cliente_id
INNER JOIN habitacion h ON h.id = r.habitacion_id;

-- Estadía completa
SELECT c.nombre, h.numero, e.fecha_inicio, e.fecha_fin
FROM estadia e
INNER JOIN cliente c ON e.cliente_id = c.id
INNER JOIN habitacion h ON e.habitacion_id = h.id;

-- Empresa + sede
SELECT e.nombre, s.nombre AS sede
FROM empresa e
INNER JOIN sede s ON e.id = s.empresa_id;

-- Facturación
SELECT c.nombre, f.numero_factura, f.total
FROM factura f
INNER JOIN cliente c ON f.cliente_id = c.id;