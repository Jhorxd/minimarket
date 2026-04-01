-- ============================================================
-- MIGRACIÓN: Módulo de Categorías Globales
-- Minimarket – Ejecutar en phpMyAdmin
-- ============================================================

-- 1. CREAR TABLA CATEGORIAS (global, sin id_sucursal)
CREATE TABLE IF NOT EXISTS `categorias` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `nombre`      VARCHAR(100) NOT NULL,
  `descripcion` VARCHAR(255) DEFAULT NULL,
  `color`       VARCHAR(7) NOT NULL DEFAULT '#3b82f6',
  `icono`       VARCHAR(50) NOT NULL DEFAULT 'fa-tag',
  `activo`      TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. MIGRAR CATEGORÍAS ÚNICAS DESDE PRODUCTOS EXISTENTES
--    (Inserta una categoría por cada texto único en productos.categoria)
-- ============================================================
INSERT IGNORE INTO `categorias` (`nombre`, `color`, `icono`)
SELECT DISTINCT
    TRIM(categoria),
    -- Asignar colores automáticos según posición
    ELT(
        (ROW_NUMBER() OVER (ORDER BY MIN(id)) % 10) + 1,
        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#06b6d4', '#f97316', '#84cc16', '#ec4899', '#6366f1'
    ),
    'fa-tag'
FROM `productos`
WHERE categoria IS NOT NULL AND TRIM(categoria) != ''
ORDER BY MIN(id);

-- ============================================================
-- 3. AGREGAR COLUMNA id_categoria A PRODUCTOS
-- ============================================================
ALTER TABLE `productos`
    ADD COLUMN `id_categoria` INT(11) DEFAULT NULL AFTER `categoria`;

-- ============================================================
-- 4. ASIGNAR id_categoria A PRODUCTOS EXISTENTES
--    (Vincula cada producto a su categoría por nombre coincidente)
-- ============================================================
UPDATE `productos` p
INNER JOIN `categorias` c ON TRIM(c.nombre) = TRIM(p.categoria)
SET p.id_categoria = c.id;

-- ============================================================
-- 5. AGREGAR CLAVE FORÁNEA
-- ============================================================
ALTER TABLE `productos`
    ADD CONSTRAINT `fk_producto_categoria`
    FOREIGN KEY (`id_categoria`) REFERENCES `categorias`(`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================
-- INFO FINAL
-- ============================================================
SELECT
    'Categorías creadas:' AS info,
    COUNT(*) AS total
FROM `categorias`
UNION ALL
SELECT
    'Productos vinculados:',
    COUNT(*)
FROM `productos`
WHERE id_categoria IS NOT NULL;
