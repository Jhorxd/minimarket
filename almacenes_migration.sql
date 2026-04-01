-- ============================================================
-- MIGRACIÓN: Módulo de Almacenes Globales
-- Minimarket – Ejecutar en phpMyAdmin
-- ============================================================

-- 1. CREAR TABLA ALMACENES
CREATE TABLE IF NOT EXISTS `almacenes` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `activo`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nombre_almacen` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. INSERTAR ALMACÉN POR DEFECTO
INSERT IGNORE INTO `almacenes` (`nombre`, `descripcion`) 
VALUES ('Almacen 01', 'Almacén principal por defecto');

-- 3. AGREGAR COLUMNA id_almacen A PRODUCTOS
ALTER TABLE `productos` 
    ADD COLUMN `id_almacen` INT(11) DEFAULT NULL AFTER `id_categoria`;

-- 4. ASIGNAR TODOS LOS PRODUCTOS AL ALMACEN 01
--    (Suponiendo que el ID del recien insertado es el primero o buscándolo por nombre)
UPDATE `productos` 
SET `id_almacen` = (SELECT id FROM `almacenes` WHERE nombre = 'Almacen 01' LIMIT 1);

-- 5. AGREGAR CLAVE FORÁNEA
ALTER TABLE `productos`
    ADD CONSTRAINT `fk_producto_almacen`
    FOREIGN KEY (`id_almacen`) REFERENCES `almacenes`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- INFO FINAL
SELECT 
    'Almacenes creados:' AS info, 
    COUNT(*) AS total 
FROM `almacenes`
UNION ALL
SELECT 
    'Productos en Almacen 01:', 
    COUNT(*) 
FROM `productos` 
WHERE id_almacen = (SELECT id FROM `almacenes` WHERE nombre = 'Almacen 01' LIMIT 1);
