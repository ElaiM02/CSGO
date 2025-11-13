-- DATABASE CSGO

-- TABLA USUARIO
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    foto VARCHAR(255),
    rol ENUM('pasajero', 'chofer', 'admin') DEFAULT 'pasajero',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo TINYINT(1) DEFAULT 1
);

INSERT INTO usuarios (nombre, apellido, cedula, fecha_nacimiento, email, telefono, usuario, password, rol) VALUES
('Admin', 'UTN', '111111411', '1990-01-01', 'admin@utn.ac.cr', '88888888', 'admin78', '123456', 'admin'),
('Chofer', 'Uno', '222224222', '1985-05-05', 'chofer@test.com', '77777777', 'chofer78', '123456', 'chofer'),
('Ana', 'Pasajera', '333393333', '1995-08-20', 'ana@test.com', '66666666', 'ana78', '123456', 'pasajero');

select * from usuarios


-- TABLA VEHICULOS
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    ano YEAR NOT NULL,
    color VARCHAR(30) NOT NULL,
    placa VARCHAR(15) UNIQUE NOT NULL,
    foto VARCHAR(255) NULL,
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion DATETIME null,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);


select * from vehiculos

-- TABLA VIAJES (RIDES)
CREATE TABLE viajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chofer_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    nombre_viaje VARCHAR(100) NOT NULL,
    origen VARCHAR(100) NOT NULL,
    destino VARCHAR(100) NOT NULL,
    fecha_hora_salida DATETIME NOT NULL,
    hora_llegada TIME NULL,
    dias_semana JSON NULL,
    precio_por_asiento DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cupos_totales INT NOT NULL DEFAULT 4,
    cupos_disponibles INT NOT NULL,

    estado ENUM('activo', 'cancelado', 'completado') DEFAULT 'activo',
    notas TEXT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- CLAVES FORANEAS
    CONSTRAINT fk_viaje_chofer 
        FOREIGN KEY (chofer_id) REFERENCES usuarios(id) 
        ON DELETE CASCADE,
        
    CONSTRAINT fk_viaje_vehiculo 
        FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) 
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

select * from viajes;


-- TABALA SOLICITUDES VIEJES 
CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    viaje_id INT NOT NULL,
    pasajero_id INT NOT NULL,
    estado ENUM('pendiente', 'aceptada', 'rechazada', 'cancelada') DEFAULT 'pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (viaje_id) REFERENCES viajes(id) ON DELETE CASCADE,
    FOREIGN KEY (pasajero_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_solicitud (viaje_id, pasajero_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


select * from solicitudes