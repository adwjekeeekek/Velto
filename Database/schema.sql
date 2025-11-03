CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  clave_hash VARCHAR(255) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS dinosaurios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  codigo VARCHAR(32) UNIQUE NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS recintos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL,
  codigo VARCHAR(32) UNIQUE NOT NULL,
  slots TINYINT UNSIGNED NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS partidas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  modo ENUM('seguimiento','digitalizado') DEFAULT 'digitalizado',
  jugada TINYINT UNSIGNED DEFAULT 1,
  estado ENUM('dado','colocar','fin') DEFAULT 'colocar',
  dado VARCHAR(20) DEFAULT NULL,
  turno_id INT DEFAULT NULL,
  ronda TINYINT UNSIGNED DEFAULT 1,
  bolsa TEXT DEFAULT NULL,
  ronda_actual TINYINT UNSIGNED DEFAULT 1,
  turno_actual TINYINT UNSIGNED DEFAULT 1,
  jugadores_colocaron TEXT DEFAULT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX ix_partida_turno (turno_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS jugadores_partida (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  usuario_id INT NULL,
  nombre VARCHAR(50) NOT NULL,
  orden TINYINT UNSIGNED NOT NULL,
  puntos INT NOT NULL DEFAULT 0,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  INDEX ix_jp_partida (partida_id),
  UNIQUE KEY uq_jp_orden (partida_id, orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS colocaciones (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  jugador_id INT NULL,
  recinto VARCHAR(40) NOT NULL,
  slot TINYINT UNSIGNED NOT NULL,
  especie VARCHAR(32) NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  FOREIGN KEY (jugador_id) REFERENCES jugadores_partida(id) ON DELETE SET NULL,
  UNIQUE KEY uq_coloc_slot (partida_id, jugador_id, recinto, slot),
  INDEX ix_coloc_partida (partida_id),
  INDEX ix_coloc_jugador (jugador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS manos_partida (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  jugador_id INT NOT NULL,
  dinosaurios TEXT NOT NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  FOREIGN KEY (jugador_id) REFERENCES jugadores_partida(id) ON DELETE CASCADE,
  UNIQUE KEY uq_mano_jugador (partida_id, jugador_id),
  INDEX ix_mano_partida (partida_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS puntajes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  jugador_id INT NOT NULL,
  categoria VARCHAR(40) NOT NULL,
  puntos INT NOT NULL,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  FOREIGN KEY (jugador_id) REFERENCES jugadores_partida(id) ON DELETE CASCADE,
  INDEX ix_punt_partida (partida_id),
  INDEX ix_punt_jugador (jugador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS eventos (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  partida_id INT NOT NULL,
  jugador_id INT NULL,
  tipo VARCHAR(30) NOT NULL,
  payload JSON NULL,
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE,
  FOREIGN KEY (jugador_id) REFERENCES jugadores_partida(id) ON DELETE SET NULL,
  INDEX ix_evt_partida_creado (partida_id, creado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE OR REPLACE VIEW v_partida_score AS
SELECT 
  jp.partida_id,
  jp.id AS jugador_id,
  jp.nombre,
  COALESCE(SUM(p.puntos), 0) AS puntos
FROM jugadores_partida jp
LEFT JOIN puntajes p ON jp.id = p.jugador_id
GROUP BY jp.partida_id, jp.id, jp.nombre;

CREATE OR REPLACE VIEW v_ranking_global AS
SELECT 
  jp.nombre,
  SUM(COALESCE(p.puntos, 0)) AS puntos_totales,
  COUNT(DISTINCT jp.partida_id) AS partidas
FROM jugadores_partida jp
LEFT JOIN puntajes p ON jp.id = p.jugador_id
GROUP BY jp.nombre
ORDER BY puntos_totales DESC, partidas DESC, jp.nombre ASC;
