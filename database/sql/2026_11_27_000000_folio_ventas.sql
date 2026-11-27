-- MySQL: respaldar antes de importar una sola vez mediante phpMyAdmin.
-- Aplicar antes de publicar 1.12.0; las salidas anteriores conservan folio NULL.
CREATE TABLE folio_venta_dia (
    fecha DATE NOT NULL PRIMARY KEY,
    ultimo INT UNSIGNED NOT NULL DEFAULT 0
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE salida
    ADD COLUMN folio VARCHAR(20) NULL,
    ADD UNIQUE KEY salida_folio_unique (folio);

INSERT INTO migrations (migration, batch)
SELECT '2026_11_27_000000_add_folio_ventas', COALESCE(MAX(batch), 0) + 1
FROM migrations;
