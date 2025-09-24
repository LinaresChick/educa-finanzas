-- Opción más segura (ejecutar por separado):

-- 1. Insertar usuario y docente para MAYDA BRAVO TORRES
INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES 
('MAYDA BRAVO TORRES', 'mayda.bravo@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Docente', 'activo');

INSERT INTO docentes (id_usuario, nombres, apellidos, especialidad, estado) VALUES 
(LAST_INSERT_ID(), 'MAYDA', 'BRAVO TORRES', 'Educación Primaria', 'activo');

INSERT INTO salones (id_grado, id_seccion, id_docente, anio, cupo_maximo, estado) 
VALUES (1, 1, LAST_INSERT_ID(), 2025, 30, 'activo');

-- 2. Insertar usuario y docente para JULIO CESAR CAVIEDES ALVAREZ
INSERT INTO usuarios (nombre, correo, password, rol, estado) VALUES 
('JULIO CESAR CAVIEDES ALVAREZ', 'julio.caviedes@educa.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Docente', 'activo');

INSERT INTO docentes (id_usuario, nombres, apellidos, especialidad, estado) VALUES 
(LAST_INSERT_ID(), 'JULIO CESAR', 'CAVIEDES ALVAREZ', 'Educación Secundaria', 'activo');

INSERT INTO salones (id_grado, id_seccion, id_docente, anio, cupo_maximo, estado) 
VALUES (7, 28, LAST_INSERT_ID(), 2025, 30, 'activo');
