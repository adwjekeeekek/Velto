INSERT INTO dinosaurios (nombre, codigo) VALUES
('Triceratops', 'triceratops'),
('T-Rex', 'trex'),
('Velociraptor', 'velociraptor'),
('Stegosaurus', 'stegosaurus'),
('Brachiosaurus', 'brachiosaurus'),
('Pterodactilo', 'pterodactilo');

INSERT INTO recintos (nombre, codigo, slots) VALUES
('Bosque de la Semejanza', 'bosque_semejanza', 6),
('Prado de la Diferencia', 'prado_diferencia', 6),
('Pradera del Amor', 'pradera_amor', 6),
('Trío Frondoso', 'trio_frondoso', 3),
('Rey de la Selva', 'rey_selva', 1),
('Isla Solitaria', 'isla_solitaria', 6),
('Río', 'rio', 6);

INSERT INTO usuarios (nombre, email, clave_hash) VALUES
('admina', 'admin@velto.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Juan', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('María', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Pedro', 'pedro@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
