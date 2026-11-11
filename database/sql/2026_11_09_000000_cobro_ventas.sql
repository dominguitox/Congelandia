-- MySQL: ejecutar una sola vez, tras respaldar, antes de subir el nuevo POS.
-- Equivale a la migración 2026_11_09_000000_add_cobro_to_salida_table.
ALTER TABLE salida
    ADD COLUMN claveOperacion CHAR(36) NULL,
    ADD COLUMN firmaCobro CHAR(64) NULL,
    ADD COLUMN efectivoRecibido DECIMAL(12,2) NULL,
    ADD COLUMN vuelto DECIMAL(12,2) NOT NULL DEFAULT 0,
    ADD UNIQUE KEY salida_claveoperacion_unique (claveOperacion);

INSERT INTO migrations (migration, batch)
SELECT '2026_11_09_000000_add_cobro_to_salida_table', COALESCE(MAX(batch), 0) + 1
FROM migrations;
