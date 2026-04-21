-- ======================================
-- ELIMINAR Y CREAR BASE DE DATOS
-- ======================================

DROP DATABASE IF EXISTS beatcell_db;
CREATE DATABASE beatcell_db;
USE beatcell_db;

-- ======================================
-- TABLA USUARIOS
-- ======================================

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,

    rol ENUM(
        'ADMINISTRADOR',
        'JEFE',
        'ASISTENTE'
    ) DEFAULT 'ASISTENTE'
);

INSERT INTO usuarios (usuario, password, nombre, rol) VALUES
('beatcell','123456','Administrador','ADMINISTRADOR');

-- ======================================
-- TABLA CURSOS
-- ======================================

CREATE TABLE cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    nombre_curso VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO cursos (nombre_curso) VALUES
('Robótica'),
('Electrónica'),
('Reparación de celulares'),
('Reparación de PC'),
('Ofimática');

-- ======================================
-- TABLA GRUPOS
-- ======================================

CREATE TABLE grupos (
    id_grupo INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,

    nombre_grupo VARCHAR(50) NOT NULL,
    dias VARCHAR(100),
    hora_inicio TIME,
    hora_fin TIME,

    FOREIGN KEY (id_curso)
        REFERENCES cursos(id_curso)
);

-- ======================================
-- HORARIOS ESPECIALES
-- ======================================

CREATE TABLE horarios_especiales (
    id_horario INT AUTO_INCREMENT PRIMARY KEY,

    id_grupo INT NOT NULL,

    dia_semana VARCHAR(20) NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
);

-- ======================================
-- TABLA CARRERAS
-- ======================================

CREATE TABLE carreras (
    id_carrera INT AUTO_INCREMENT PRIMARY KEY,
    nombre_carrera VARCHAR(100) UNIQUE
);

INSERT INTO carreras (nombre_carrera) VALUES
('Desarrollo de Software'),
('Diseño Gráfico'),
('Electrónica'),
('Redes y Comunicaciones'),
('Mecatrónica');

-- ======================================
-- TABLA ALUMNOS
-- ======================================

CREATE TABLE alumnos (
    id_alumno INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),

    telefonopadres VARCHAR(20),
    telefonoapoderado VARCHAR(20),

    contacto_pago VARCHAR(50) DEFAULT 'Alumno',

    edad INT,
    email VARCHAR(150),
    direccion TEXT,

    nombre_apoderado VARCHAR(150),
    dni_apoderado VARCHAR(20),
    correo_apoderado VARCHAR(150),

    notificar_emergencia VARCHAR(50),

    tipo_ciclo VARCHAR(50),
    medio_captacion VARCHAR(50),

    fecha_baja DATE,
    fecha_registro DATE DEFAULT CURRENT_DATE
);

-- ======================================
-- TABLA MATRICULAS
-- ======================================

CREATE TABLE matriculas (
    id_matricula INT AUTO_INCREMENT PRIMARY KEY,

    id_alumno INT NOT NULL,
    id_grupo INT NOT NULL,

    tipo VARCHAR(50) DEFAULT 'MATRICULA',

    monto_matricula DECIMAL(10,2) DEFAULT 0.00,
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,

    fecha_matricula DATE DEFAULT CURRENT_DATE,
    estado VARCHAR(50) DEFAULT 'Activo',

    FOREIGN KEY (id_alumno)
        REFERENCES alumnos(id_alumno),

    FOREIGN KEY (id_grupo)
        REFERENCES grupos(id_grupo)
);

-- ======================================
-- TABLA PRACTICANTES
-- ======================================

CREATE TABLE practicantes (
    id_practicante INT AUTO_INCREMENT PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    telefono_emergencia VARCHAR(20),
    dni VARCHAR(20),

    id_carrera INT,

    horario VARCHAR(50),

    edad INT,
    email VARCHAR(150),
    direccion TEXT,

    nombre_apoderado VARCHAR(150),
    dni_apoderado VARCHAR(20),
    correo_apoderado VARCHAR(150),
    telefono_apoderado VARCHAR(20),

    notificar_emergencia VARCHAR(50),
    modalidad_horario VARCHAR(50),

    observacion TEXT,

    fecha_baja DATE,
    fecha_registro DATE DEFAULT CURRENT_DATE,

    FOREIGN KEY (id_carrera)
        REFERENCES carreras(id_carrera)
);

-- ======================================
-- PROMOCIONES
-- ======================================

CREATE TABLE promociones (
    id_promocion INT AUTO_INCREMENT PRIMARY KEY,

    nombre_promocion VARCHAR(100),
    descripcion TEXT,

    fecha_inicio DATE,
    fecha_fin DATE,

    activa BOOLEAN DEFAULT TRUE
);

-- ======================================
-- MATRICULAS PROMOCIONES
-- ======================================

CREATE TABLE matriculas_promociones (
    id INT AUTO_INCREMENT PRIMARY KEY,

    id_matricula INT NOT NULL,
    id_promocion INT NOT NULL,

    fecha_asignacion DATE DEFAULT CURRENT_DATE,

    FOREIGN KEY (id_matricula)
        REFERENCES matriculas(id_matricula),

    FOREIGN KEY (id_promocion)
        REFERENCES promociones(id_promocion)
);

-- ======================================
-- INSERTAR PROMOCIÓN "Inscripción Doble"
-- Ejecutar en beatcell_db
-- ======================================
 
INSERT INTO promociones (
    nombre_promocion,
    descripcion,
    fecha_inicio,
    fecha_fin,
    activa
) VALUES (
    'Inscripción Doble',
    'Aplica cuando un alumno se inscribe en dos cursos diferentes, o cuando dos alumnos se matriculan a la vez. Ciclo Normal: S/100/mes (en vez de S/150). Ciclo Acelerado: S/200/mes (en vez de S/250).',
    CURDATE(),
    NULL,
    TRUE
);
 

-- ======================================
-- ASISTENCIAS ALUMNOS
-- ======================================

CREATE TABLE asistencias (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,

    id_alumno INT NOT NULL,
    fecha DATE NOT NULL,

    hora_entrada TIME,
    hora_salida TIME,

    estado VARCHAR(20) DEFAULT 'Pendiente',

    tareas_asignadas TEXT,
    tareas_terminadas TEXT,

    FOREIGN KEY (id_alumno)
        REFERENCES alumnos(id_alumno)
);

-- ======================================
-- ASISTENCIAS PRACTICANTES
-- ======================================

CREATE TABLE asistencias_practicantes (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,

    id_practicante INT NOT NULL,
    fecha DATE NOT NULL,

    hora_entrada TIME,
    hora_salida TIME,

    estado VARCHAR(20) DEFAULT 'Pendiente',

    tareas_asignadas TEXT,
    tareas_terminadas TEXT,

    FOREIGN KEY (id_practicante)
        REFERENCES practicantes(id_practicante)
);

-- ======================================
-- PLANES DE PAGO
-- ======================================

CREATE TABLE planes_pago (

    id_plan INT AUTO_INCREMENT PRIMARY KEY,

    id_matricula INT NOT NULL,

    monto_base DECIMAL(10,2) NOT NULL,
    tipo_descuento VARCHAR(50) DEFAULT 'Ninguno',
    porcentaje_descuento DECIMAL(5,2) DEFAULT 0,

    monto_final DECIMAL(10,2) NOT NULL,

    cantidad_cuotas INT NOT NULL,
    es_becado BOOLEAN DEFAULT FALSE,

    fecha_inicio DATE,
    fecha_creacion DATE DEFAULT CURRENT_DATE,

    estado VARCHAR(50) DEFAULT 'Activo',

    FOREIGN KEY (id_matricula)
        REFERENCES matriculas(id_matricula)
);

CREATE INDEX idx_plan_matricula
ON planes_pago(id_matricula);

-- ======================================
-- CUOTAS
-- ======================================

CREATE TABLE cuotas (

    id_cuota INT AUTO_INCREMENT PRIMARY KEY,

    id_plan INT NOT NULL,
    numero_cuota INT NOT NULL,

    monto_cuota DECIMAL(10,2) NOT NULL,
    monto_pagado DECIMAL(10,2) DEFAULT 0.00,

    fecha_vencimiento DATE,
    fecha_pago DATE,

    metodo_pago VARCHAR(50),

    dias_gracia INT DEFAULT 0,
    estado VARCHAR(50) DEFAULT 'Pendiente',

    UNIQUE (id_plan, numero_cuota),

    FOREIGN KEY (id_plan)
        REFERENCES planes_pago(id_plan)
);

CREATE INDEX idx_cuotas_plan
ON cuotas(id_plan);

-- ======================================
-- MOVIMIENTOS FINANCIEROS
-- ======================================

CREATE TABLE movimientos_financieros (

    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,

    id_alumno INT NOT NULL,
    id_matricula INT,
    id_cuota INT,

    tipo_movimiento VARCHAR(50) NOT NULL,

    monto DECIMAL(10,2) NOT NULL,

    fecha DATE DEFAULT CURRENT_DATE,

    observacion TEXT,

    numero_boleta VARCHAR(50),
    ruta_pdf TEXT,

    FOREIGN KEY (id_alumno)
        REFERENCES alumnos(id_alumno),

    FOREIGN KEY (id_matricula)
        REFERENCES matriculas(id_matricula),

    FOREIGN KEY (id_cuota)
        REFERENCES cuotas(id_cuota)
);


