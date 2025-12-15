
-- database: condomino   (MYSQL)

CREATE DATABASE IF NOT EXISTS condomino CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE condomino;

-- 1) Users (usuarios + roles)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  role ENUM('admin','sindico','residente','vigilante') DEFAULT 'residente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Units (departamentos/unidades)
CREATE TABLE units (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tower VARCHAR(50) DEFAULT NULL,
  number VARCHAR(50) NOT NULL,
  floor INT DEFAULT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE (tower, number)
);

-- 3) Residents (relaciona usuario <-> unidad: dueño o residente)
CREATE TABLE residents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  unit_id INT NOT NULL,
  type ENUM('propietario','residente') DEFAULT 'residente',
  since DATE DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- 4) Payments (pagos realizados por unidades)
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  unit_id INT NOT NULL,
  user_id INT DEFAULT NULL, -- quien registró o quien pagó
  concept VARCHAR(200) NOT NULL, -- cuota, multa, reserva, extra
  amount DECIMAL(12,2) NOT NULL,
  month TINYINT DEFAULT NULL, -- 1..12
  year SMALLINT DEFAULT NULL,
  type ENUM('cuota','multa','extra','reserva') DEFAULT 'cuota',
  paid_at DATETIME DEFAULT NULL,
  method VARCHAR(80) DEFAULT NULL,
  reference VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 5) Expenses (gastos de la administración)
CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  concept VARCHAR(200) NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  date DATE NOT NULL,
  category VARCHAR(100) DEFAULT NULL, -- luz, agua, mantenimiento
  vendor VARCHAR(150) DEFAULT NULL,
  invoice_number VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6) Documents / Comprobantes / Facturas (archivos y metadatos)
CREATE TABLE documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  expense_id INT DEFAULT NULL,
  filename VARCHAR(255) NOT NULL,
  path VARCHAR(255) NOT NULL,
  uploaded_by INT DEFAULT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL,
  FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 7) Amenities (areas comunes)
CREATE TABLE amenities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  rules TEXT,
  fee DECIMAL(12,2) DEFAULT 0,
  requires_approval TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8) Reservations (reservas de areas)
CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  amenity_id INT NOT NULL,
  unit_id INT NOT NULL,
  start_datetime DATETIME NOT NULL,
  end_datetime DATETIME NOT NULL,
  status ENUM('pendiente','aprobada','rechazada') DEFAULT 'pendiente',
  requested_by INT DEFAULT NULL,
  approved_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
  FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 9) Monthly statements (estado de cuenta mensual de la privada)
CREATE TABLE monthly_statements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  period_month TINYINT NOT NULL, -- 1..12
  period_year SMALLINT NOT NULL,
  total_income DECIMAL(14,2) DEFAULT 0,
  total_expenses DECIMAL(14,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(period_month, period_year)
);

-- 10) Unit statements (estado de cuenta por residente/unidad)
-- Este registro acumula movimientos por unidad para mostrar si es moroso
CREATE TABLE unit_movements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  unit_id INT NOT NULL,
  description VARCHAR(255),
  amount DECIMAL(12,2) NOT NULL, -- positivo = cargo, negativo = pago
  date DATE NOT NULL,
  reference VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
);

-- 11) Tickets / Maintenance
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  unit_id INT DEFAULT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  priority ENUM('baja','media','alta') DEFAULT 'media',
  status ENUM('abierto','en_progreso','cerrado') DEFAULT 'abierto',
  created_by INT DEFAULT NULL,
  assigned_to INT DEFAULT NULL,
  closed_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- 12) Communications (comunicados a residentes)
CREATE TABLE communications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  body TEXT NOT NULL,
  published_by INT DEFAULT NULL,
  pinned TINYINT(1) DEFAULT 0,
  visible_from DATETIME DEFAULT NULL,
  visible_to DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (published_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 13) Guards (vigilantes)
CREATE TABLE guards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  badge_number VARCHAR(100) DEFAULT NULL,
  active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 14) Patrol logs / Bitácoras (observaciones por turno)
CREATE TABLE guard_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  guard_id INT NOT NULL,
  shift_date DATE NOT NULL,
  start_time TIME DEFAULT NULL,
  end_time TIME DEFAULT NULL,
  observations TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (guard_id) REFERENCES guards(id) ON DELETE CASCADE
);

-- 15) Visits (registro de entradas y salidas)
CREATE TABLE visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  unit_id INT DEFAULT NULL,
  visitor_name VARCHAR(200) NOT NULL,
  vehicle_plate VARCHAR(50) DEFAULT NULL,
  entry_time DATETIME DEFAULT NULL,
  exit_time DATETIME DEFAULT NULL,
  authorized_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
  FOREIGN KEY (authorized_by) REFERENCES users(id) ON DELETE SET NULL
);
