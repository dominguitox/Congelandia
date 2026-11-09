-- Alternativa a la migración Laravel para instalaciones MySQL sin terminal.
-- Respaldar la base antes de importar. Aplicar antes de publicar estos formularios.
-- Puede repetirse: conserva registros, claves foráneas e historial de migraciones.
ALTER TABLE `ingreso` MODIFY `idProveedor` BIGINT UNSIGNED NULL;

INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_09_020000_allow_ingresos_without_proveedor', COALESCE(MAX(`batch`), 0) + 1
FROM `migrations`
HAVING NOT EXISTS (
    SELECT 1 FROM `migrations`
    WHERE `migration` = '2026_11_09_020000_allow_ingresos_without_proveedor'
);
