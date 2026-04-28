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