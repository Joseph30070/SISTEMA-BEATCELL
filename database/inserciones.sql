-- ======================================
-- GRUPOS (4 NORMALES)
-- ======================================

INSERT INTO grupos (id_curso, nombre_grupo, dias, hora_inicio, hora_fin) VALUES

(1, 'Robótica - A', 'Lunes/Miércoles', '08:00:00', '10:00:00'),
(2, 'Electrónica - A', 'Martes/Jueves', '10:00:00', '12:00:00'),
(3, 'Celulares - A', 'Lunes/Miércoles/Viernes', '14:00:00', '16:00:00'),
(4, 'PC - A', 'Martes/Jueves', '16:00:00', '18:00:00');


-- ======================================
-- HORARIO ESPECIAL (1)
-- ======================================

INSERT INTO horarios_especiales (id_grupo, dia_semana, hora_inicio, hora_fin) VALUES
(1, 'Sábado', '09:00:00', '12:00:00');


-- ======================================
-- ALUMNOS (5 COMPLETOS)
-- ======================================

INSERT INTO alumnos (
nombre, dni, telefono, telefonopadres, telefonoapoderado,
contacto_pago, edad, email, direccion,
nombre_apoderado, dni_apoderado, correo_apoderado,
notificar_emergencia, tipo_ciclo, medio_captacion
) VALUES

('Juan Pérez López','71234567','987111111','987222222','987333333',
'Alumno',18,'juan@gmail.com','Av. Lima 123',
'Carlos Pérez','40123456','apoderado1@gmail.com',
'Si','Regular','Facebook'),

('María Gómez Rojas','72345678','987444444','987555555','987666666',
'Apoderado',19,'maria@gmail.com','Jr. Arequipa 456',
'Lucía Rojas','40234567','apoderado2@gmail.com',
'Si','Regular','TikTok'),

('Kevin Torres Díaz','73456789','987777777','987888888','987999999',
'Alumno',20,'kevin@gmail.com','Av. Grau 789',
'Pedro Torres','40345678','apoderado3@gmail.com',
'No','Intensivo','Instagram'),

('Ana Castillo Vargas','74567890','986111111','986222222','986333333',
'Apoderado',21,'ana@gmail.com','Av. Brasil 321',
'Rosa Vargas','40456789','apoderado4@gmail.com',
'Si','Regular','Referencia'),

('Luis Ramírez Soto','75678901','985111111','985222222','985333333',
'Alumno',22,'luis@gmail.com','Av. Colonial 654',
'Juan Soto','40567890','apoderado5@gmail.com',
'No','Intensivo','Web');


-- ======================================
-- MATRÍCULAS (ALUMNOS YA ASIGNADOS A GRUPO + HORARIO)
-- ======================================

INSERT INTO matriculas (
id_alumno, id_grupo, tipo, monto_matricula, monto_pagado, estado
) VALUES

-- Juan → Robótica (L/M 08-10)
(1, 1, 'MATRICULA', 50.00, 0.00, 'Activo'),

-- María → Electrónica (M/J 10-12)
(2, 2, 'MATRICULA', 50.00, 0.00, 'Activo'),

-- Kevin → Celulares (L/M/V 14-16)
(3, 3, 'MATRICULA', 50.00, 0.00, 'Activo'),

-- Ana → PC (M/J 16-18)
(4, 4, 'MATRICULA', 50.00, 0.00, 'Activo'),

-- Luis → Robótica (mismo grupo permitido)
(5, 1, 'MATRICULA', 50.00, 0.00, 'Activo');


-- ======================================
-- PRÁCTICANTES (5 COMPLETOS)
-- ======================================

INSERT INTO practicantes (
nombre, telefono, telefono_emergencia, dni,
id_carrera, horario, edad, email, direccion,
nombre_apoderado, dni_apoderado, correo_apoderado, telefono_apoderado,
notificar_emergencia, modalidad_horario, observacion
) VALUES

('Carlos Medina Ruiz','999111222','999111333','81234567',
1,'Mañana',20,'carlosm@gmail.com','Av. Los Olivos 123',
'Pedro Medina','50123456','padre1@gmail.com','999444555',
'Si','Presencial','Buen rendimiento'),

('Lucía Fernández Soto','999222333','999222444','82345678',
2,'Tarde',21,'luciaf@gmail.com','Av. San Juan 456',
'María Soto','50234567','madre2@gmail.com','999555666',
'Si','Presencial','Excelente actitud'),

('Jorge Ramírez Peña','999333444','999333555','83456789',
3,'Mañana',22,'jorger@gmail.com','Av. Central 789',
'Luis Ramírez','50345678','padre3@gmail.com','999666777',
'No','Virtual','Regular'),

('Valeria Torres Lima','999444555','999444666','84567890',
4,'Tarde',23,'valeriat@gmail.com','Av. Norte 321',
'Rosa Lima','50456789','madre4@gmail.com','999777888',
'Si','Presencial','Destacada'),

('Miguel Chávez Rojas','999555666','999555777','85678901',
5,'Noche',24,'miguelc@gmail.com','Av. Sur 654',
'Juan Chávez','50567890','padre5@gmail.com','999888999',
'No','Virtual','Requiere seguimiento');

