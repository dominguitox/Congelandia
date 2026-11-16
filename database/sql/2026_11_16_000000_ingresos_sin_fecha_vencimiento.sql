-- Respaldar la base antes de aplicar. Conserva las fechas históricas existentes.
ALTER TABLE detalle_ingreso MODIFY fechaVencimiento DATE NULL;
