-- MySQL: respaldar antes de importar. No reemplaza la base existente.
-- Aplicar DESPUÉS de 2026_11_09_000000_cobro_ventas.sql, si aún no se aplicó.
CREATE TABLE IF NOT EXISTS operaciones_offline (
    id CHAR(36) NOT NULL PRIMARY KEY,
    idUsuario BIGINT UNSIGNED NOT NULL,
    firma CHAR(64) NOT NULL,
    tipo VARCHAR(16) NOT NULL,
    fechaLocal DATETIME NOT NULL,
    datos JSON NOT NULL,
    estado VARCHAR(16) NOT NULL,
    resultado JSON NOT NULL,
    created_at TIMESTAMP NOT NULL,
    INDEX operaciones_offline_idusuario_estado_index (idUsuario, estado),
    CONSTRAINT operaciones_offline_idusuario_foreign FOREIGN KEY (idUsuario) REFERENCES usuario(idUsuario)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO migrations (migration, batch)
SELECT '2026_11_10_000000_create_operaciones_offline_table', COALESCE(MAX(batch), 0) + 1
FROM migrations
HAVING NOT EXISTS (
    SELECT 1 FROM migrations WHERE migration = '2026_11_10_000000_create_operaciones_offline_table'
);
