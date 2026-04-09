-- ======================================
-- GRUPOS
-- ======================================

INSERT INTO grupos (id_curso, nombre_grupo, dias, hora_inicio, hora_fin) VALUES
(2, 'Grupo P', 'Martes, Jueves, Sábado', '14:00:00', '16:00:00'),
(2, 'Grupo Z', 'Lunes, Miércoles, Viernes', '10:00:00', '12:00:00'),
(3, 'Grupo R', 'Lunes, Martes, Viernes, Sábado', '16:30:00', '18:30:00'),
(5, 'Grupo A', 'Lunes, Miércoles', '08:00:00', '10:00:00');



-- ======================================
-- ALUMNOS
-- ======================================

INSERT INTO alumnos (nombre, dni, telefono) VALUES
('Juan Perez', '10000001', '900000001'),
('Maria Lopez', '10000002', '900000002'),
('Carlos Sanchez', '10000003', '900000003'),
('Ana Torres', '10000004', '900000004'),
('Luis Ramirez', '10000005', '900000005'),
('Sofia Castillo', '10000006', '900000006'),
('Pedro Vargas', '10000007', '900000007'),
('Lucia Mendoza', '10000008', '900000008'),
('Diego Flores', '10000009', '900000009'),
('Valeria Cruz', '10000010', '900000010');



-- ======================================
-- MATRICULAS
-- ======================================

INSERT INTO matriculas (id_alumno, id_grupo, tipo, monto_matricula) VALUES
(1, 1, 'MATRICULA', 50),
(2, 2, 'MATRICULA', 50),
(3, 3, 'MATRICULA', 50),
(4, 4, 'MATRICULA', 50),
(5, 1, 'MATRICULA', 50),
(6, 2, 'MATRICULA', 50),
(7, 3, 'MATRICULA', 50),
(8, 4, 'MATRICULA', 50),
(9, 1, 'MATRICULA', 50),
(10, 2, 'MATRICULA', 50);



-- =======================================
-- PRACTICANTES
-- =======================================

INSERT INTO practicantes (
    nombre,
    telefono,
    telefono_emergencia,
    dni,
    id_carrera,
    horario,
    observacion
) VALUES
('Luis Gomez', '911111111', '922222222', '20000001', 1, 'Mañana', 'Buen desempeño'),
('Andrea Rojas', '933333333', '944444444', '20000002', 2, 'Tarde', 'Apoyo en laboratorio'),
('Miguel Torres', '955555555', '966666666', '20000003', 3, 'Mañana', 'Responsable'),
('Daniela Perez', '977777777', '988888888', '20000004', 4, 'Tarde', 'En capacitación'),
('Jorge Castillo', '999999999', '900000000', '20000005', 5, 'Mañana', 'Nuevo ingreso');



-- ======================================
-- ASISTENCIAS
-- ======================================

INSERT INTO asistencias (
    id_alumno,
    fecha,
    hora_entrada,
    hora_salida,
    estado
) VALUES

-- LUNES 06/04/2026
(1,'2026-04-06','10:01:15',NULL,'Asistió'),
(2,'2026-04-06',NULL,NULL,'Ausente'),
(3,'2026-04-06','10:05:10',NULL,'Asistió'),
(4,'2026-04-06','10:07:22',NULL,'Asistió'),
(5,'2026-04-06','10:10:00',NULL,'Asistió'),
(6,'2026-04-06',NULL,NULL,'Ausente'),
(7,'2026-04-06','10:15:33',NULL,'Asistió'),
(8,'2026-04-06','10:18:45',NULL,'Asistió'),
(9,'2026-04-06',NULL,NULL,'Ausente'),
(10,'2026-04-06','10:22:10',NULL,'Asistió'),

-- MARTES 07/04/2026
(1,'2026-04-07','11:54:42',NULL,'Asistió'),
(2,'2026-04-07',NULL,NULL,'Ausente'),
(3,'2026-04-07','11:54:44',NULL,'Asistió'),
(4,'2026-04-07','12:00:14',NULL,'Asistió'),
(5,'2026-04-07','12:03:10',NULL,'Asistió'),
(6,'2026-04-07',NULL,NULL,'Ausente'),
(7,'2026-04-07','12:05:20',NULL,'Asistió'),
(8,'2026-04-07','12:06:50',NULL,'Asistió'),
(9,'2026-04-07',NULL,NULL,'Ausente'),
(10,'2026-04-07','12:08:15',NULL,'Asistió'),

-- MIERCOLES 08/04/2026
(1,'2026-04-08','11:27:35',NULL,'Asistió'),
(2,'2026-04-08',NULL,NULL,'Ausente'),
(3,'2026-04-08','11:30:12',NULL,'Asistió'),
(4,'2026-04-08','12:04:59',NULL,'Asistió'),
(5,'2026-04-08','12:06:22',NULL,'Asistió'),
(6,'2026-04-08',NULL,NULL,'Ausente'),
(7,'2026-04-08','12:10:11',NULL,'Asistió'),
(8,'2026-04-08','12:12:45',NULL,'Asistió'),
(9,'2026-04-08',NULL,NULL,'Ausente'),
(10,'2026-04-08','12:15:00',NULL,'Asistió'),

-- JUEVES 09/04/2026
(1,'2026-04-09',NULL,NULL,'Ausente'),
(2,'2026-04-09',NULL,NULL,'Ausente'),
(3,'2026-04-09','10:49:15',NULL,'Asistió'),
(4,'2026-04-09','10:49:21',NULL,'Asistió'),
(5,'2026-04-09','10:50:00',NULL,'Asistió'),
(6,'2026-04-09',NULL,NULL,'Ausente'),
(7,'2026-04-09','10:55:33',NULL,'Asistió'),
(8,'2026-04-09','10:57:44',NULL,'Asistió'),
(9,'2026-04-09',NULL,NULL,'Ausente'),
(10,'2026-04-09','11:00:10',NULL,'Asistió');
