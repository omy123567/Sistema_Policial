-- ==================== CREAR BASE DE DATOS ====================
CREATE DATABASE IF NOT EXISTS sistema_policial;
USE sistema_policial;

-- ==================== TABLA DE ROLES ====================
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==================== TABLA DE USUARIOS ====================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_completo VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    permisos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- ==================== TABLA DE PERSONAL ====================
CREATE TABLE IF NOT EXISTS personal (
    id INT PRIMARY KEY AUTO_INCREMENT,
    legajo VARCHAR(20) UNIQUE NOT NULL,
    jerarquia VARCHAR(50),
    apellido VARCHAR(50) NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    sexo ENUM('Masculino', 'Femenino', 'Otro'),
    oficina VARCHAR(100),
    fecha_nacimiento DATE,
    tiene_arma BOOLEAN DEFAULT FALSE,
    arma_marca VARCHAR(50),
    arma_modelo VARCHAR(50),
    arma_serie VARCHAR(50),
    sin_arma_justificacion TEXT,
    nro_credencial VARCHAR(50),
    nro_licencia_conducir VARCHAR(50),
    fecha_vencimiento_licencia DATE,
    obra_social VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_legajo (legajo),
    INDEX idx_dni (dni),
    INDEX idx_apellido_nombre (apellido, nombre)
);

-- ==================== TABLA DE CATÁLOGOS ====================
CREATE TABLE IF NOT EXISTS catalogos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo VARCHAR(50) NOT NULL,
    valor VARCHAR(100) NOT NULL,
    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tipo_valor (tipo, valor),
    INDEX idx_tipo (tipo)
);

-- ==================== TABLA DE MARCAS DE ARMAS ====================
CREATE TABLE IF NOT EXISTS armas_marcas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==================== TABLA DE MODELOS DE ARMAS ====================
CREATE TABLE IF NOT EXISTS armas_modelos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    marca_id INT,
    modelo VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (marca_id) REFERENCES armas_marcas(id) ON DELETE CASCADE,
    UNIQUE KEY unique_marca_modelo (marca_id, modelo)
);

-- ==================== TABLA DE RECARGOS ====================
CREATE TABLE IF NOT EXISTS recargos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    tipo_recargo VARCHAR(100),
    oficina VARCHAR(100),
    personal_id INT,
    observaciones TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (personal_id) REFERENCES personal(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_fecha (fecha),
    INDEX idx_personal (personal_id)
);

-- ==================== TABLA DE EXPEDIENTES ====================
CREATE TABLE IF NOT EXISTS expedientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nro_expediente VARCHAR(50) UNIQUE NOT NULL,
    fecha DATE NOT NULL,
    tipo_oficio VARCHAR(100),
    juzgado_origen VARCHAR(200),
    dependencia VARCHAR(100),
    tipo_requerimiento VARCHAR(100),
    resumen TEXT,
    nro_informe_tecnico VARCHAR(50),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_nro_expediente (nro_expediente),
    INDEX idx_fecha (fecha)
);

-- ==================== TABLA DE ELEVACIONES ====================
CREATE TABLE IF NOT EXISTS elevaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediente_id INT,
    fecha_hora DATETIME NOT NULL,
    recibido_por VARCHAR(100),
    notas TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE CASCADE
);

-- ==================== TABLA DE ADJUNTOS ====================
CREATE TABLE IF NOT EXISTS adjuntos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediente_id INT,
    nombre_archivo VARCHAR(255),
    ruta_archivo VARCHAR(500),
    tipo_archivo VARCHAR(100),
    tamano INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE CASCADE
);

-- ==================== TABLA DE LICENCIAS ====================
CREATE TABLE IF NOT EXISTS licencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agente_id INT,
    tipo_licencia VARCHAR(100),
    estado ENUM('Pendiente', 'Aprobada', 'Rechazada', 'En Curso', 'Finalizada') DEFAULT 'Pendiente',
    fecha_inicio DATE NOT NULL,
    dias_habiles INT DEFAULT 0,
    dias_viaje INT DEFAULT 0,
    contar_fines_semana BOOLEAN DEFAULT FALSE,
    fecha_fin DATE,
    observaciones TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agente_id) REFERENCES personal(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
);

-- ==================== TABLA DE FERIADOS ====================
CREATE TABLE IF NOT EXISTS feriados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fecha DATE UNIQUE NOT NULL,
    motivo VARCHAR(200),
    tipo ENUM('Nacional', 'Provincial', 'Municipal') DEFAULT 'Nacional',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha)
);

-- ==================== TABLA DE CONFIGURACIÓN ====================
CREATE TABLE IF NOT EXISTS configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    clave VARCHAR(50) UNIQUE NOT NULL,
    valor TEXT,
    tipo VARCHAR(20) DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ==================== TABLA DE BITÁCORA ====================
CREATE TABLE IF NOT EXISTS bitacora (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(100),
    tabla_afectada VARCHAR(50),
    registro_id INT,
    detalles TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (created_at)
);

-- ==================== TABLA DE NOTIFICACIONES ====================
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    titulo VARCHAR(200),
    mensaje TEXT,
    leido BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_leido (usuario_id, leido)
);

-- ==================== DATOS INICIALES ====================

-- Insertar roles
INSERT INTO roles (nombre) VALUES 
('Administrador'),
('Supervisor'),
('Usuario')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar usuarios predefinidos (contraseña: admin123 y supervisor123)
-- Las contraseñas están hasheadas con bcrypt
INSERT INTO usuarios (nombre_completo, username, email, password, rol_id, estado) VALUES 
('Administrador del Sistema', 'admin', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Activo'),
('Supervisor General', 'supervisor', 'supervisor@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 'Activo')
ON DUPLICATE KEY UPDATE nombre_completo = VALUES(nombre_completo);

-- Insertar catálogos
INSERT INTO catalogos (tipo, valor, orden) VALUES
-- Jerarquías
('jerarquias', 'Oficial Principal', 1),
('jerarquias', 'Oficial Inspector', 2),
('jerarquias', 'Oficial Subinspector', 3),
('jerarquias', 'Oficial Ayudante', 4),
('jerarquias', 'Suboficial Principal', 5),
('jerarquias', 'Suboficial Mayor', 6),
('jerarquias', 'Suboficial', 7),
('jerarquias', 'Cabo Principal', 8),
('jerarquias', 'Cabo', 9),
('jerarquias', 'Agente', 10),
-- Obras Sociales
('obras_sociales', 'OSDE', 1),
('obras_sociales', 'Medife', 2),
('obras_sociales', 'Swiss Medical', 3),
('obras_sociales', 'PAMI', 4),
('obras_sociales', 'IOSFA', 5),
-- Tipos de Recargo
('tipos_recargo', 'Llegada tarde', 1),
('tipos_recargo', 'Falta injustificada', 2),
('tipos_recargo', 'Incumplimiento de deberes', 3),
('tipos_recargo', 'Mal desempeño', 4),
('tipos_recargo', 'Falta de respeto', 5),
-- Tipos de Oficio
('tipos_oficio', 'Oficio Judicial', 1),
('tipos_oficio', 'Oficio Fiscal', 2),
('tipos_oficio', 'Oficio Administrativo', 3),
('tipos_oficio', 'Oficio Policial', 4),
-- Tipos de Requerimiento
('tipos_requerimiento', 'Informe técnico', 1),
('tipos_requerimiento', 'Peritaje', 2),
('tipos_requerimiento', 'Relevamiento', 3),
('tipos_requerimiento', 'Investigación', 4),
-- Tipos de Licencia
('tipos_licencia', 'Ordinaria', 1),
('tipos_licencia', 'Por enfermedad', 2),
('tipos_licencia', 'Especial', 3),
('tipos_licencia', 'Por estudio', 4),
('tipos_licencia', 'Maternidad/Paternidad', 5),
-- Oficinas
('oficinas', 'Comisaría 1ra', 1),
('oficinas', 'Comisaría 2da', 2),
('oficinas', 'Departamento de Investigaciones', 3),
('oficinas', 'Oficina de Tránsito', 4),
('oficinas', 'División de Operaciones', 5),
-- Dependencias
('dependencias', 'Ministerio de Seguridad', 1),
('dependencias', 'Jefatura de Policía', 2),
('dependencias', 'Tribunales', 3),
-- Juzgados
('juzgados', 'Juzgado Federal N°1', 1),
('juzgados', 'Juzgado Federal N°2', 2),
('juzgados', 'Juzgado de Garantías N°1', 3),
('juzgados', 'Juzgado de Garantías N°2', 4),
('juzgados', 'Juzgado de Instrucción N°1', 5)
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Insertar configuración inicial
INSERT INTO configuracion (clave, valor, tipo) VALUES
('nombre_sistema', 'Sistema de Gestión Policial', 'text'),
('logo_sistema', '', 'image'),
('widgets_dashboard', '["stats","jerarquias","recargos","expedientes","licencias","licencias_vencer"]', 'json')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);

-- Insertar feriados 2024-2025
INSERT INTO feriados (fecha, motivo, tipo) VALUES
('2024-01-01', 'Año Nuevo', 'Nacional'),
('2024-02-12', 'Carnaval', 'Nacional'),
('2024-02-13', 'Carnaval', 'Nacional'),
('2024-03-24', 'Día de la Memoria', 'Nacional'),
('2024-04-02', 'Día del Veterano', 'Nacional'),
('2024-05-01', 'Día del Trabajador', 'Nacional'),
('2024-05-25', 'Revolución de Mayo', 'Nacional'),
('2024-06-20', 'Bandera', 'Nacional'),
('2024-07-09', 'Independencia', 'Nacional'),
('2024-12-08', 'Inmaculada Concepción', 'Nacional'),
('2024-12-25', 'Navidad', 'Nacional'),
('2025-01-01', 'Año Nuevo', 'Nacional'),
('2025-03-03', 'Carnaval', 'Nacional'),
('2025-03-04', 'Carnaval', 'Nacional'),
('2025-03-24', 'Día de la Memoria', 'Nacional'),
('2025-04-02', 'Día del Veterano', 'Nacional'),
('2025-05-01', 'Día del Trabajador', 'Nacional'),
('2025-05-25', 'Revolución de Mayo', 'Nacional'),
('2025-06-20', 'Bandera', 'Nacional'),
('2025-07-09', 'Independencia', 'Nacional'),
('2025-12-08', 'Inmaculada Concepción', 'Nacional'),
('2025-12-25', 'Navidad', 'Nacional')
ON DUPLICATE KEY UPDATE motivo = VALUES(motivo);