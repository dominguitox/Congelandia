-- MySQL: respaldar y ejecutar una sola vez antes de actualizar los archivos.
-- Equivale a 2026_11_09_010000_create_registro_acciones_table.
CREATE TABLE registro_acciones (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idUsuario BIGINT UNSIGNED NULL,
    usuarioNombre VARCHAR(100) NOT NULL,
    accion VARCHAR(100) NOT NULL,
    modulo VARCHAR(50) NOT NULL,
    ruta VARCHAR(100) NOT NULL,
    referencia VARCHAR(255) NULL,
    fecha DATETIME NOT NULL,
    CONSTRAINT registro_acciones_idusuario_foreign FOREIGN KEY (idUsuario)
        REFERENCES usuario (idUsuario) ON DELETE SET NULL,
    INDEX registro_acciones_idusuario_fecha_index (idUsuario, fecha),
    INDEX registro_acciones_modulo_fecha_index (modulo, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO migrations (migration, batch)
SELECT '2026_11_09_010000_create_registro_acciones_table', COALESCE(MAX(batch), 0) + 1
FROM migrations;
