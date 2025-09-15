-- Usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  pass_hash VARCHAR(255) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Partidas
CREATE TABLE partidas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  modo ENUM('seguimiento','digitalizado') DEFAULT 'digitalizado',
  jugada TINYINT UNSIGNED DEFAULT 1,
  estado ENUM('colocar','fin') DEFAULT 'colocar',
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Colocaciones
CREATE TABLE colocaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  recinto VARCHAR(40) NOT NULL,
  slot TINYINT UNSIGNED NOT NULL,
  especie VARCHAR(32) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  UNIQUE KEY uq_coloc_slot (partida_id, recinto, slot),
  INDEX ix_coloc_partida (partida_id)
);

-- Dinosaurios
CREATE TABLE dinosaurios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  codigo VARCHAR(32) UNIQUE NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Recintos
CREATE TABLE recintos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  codigo VARCHAR(32) UNIQUE NOT NULL,
  slots TINYINT UNSIGNED NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
