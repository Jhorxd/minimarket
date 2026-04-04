-- Parche para bases ya existentes: gastos operativos (no mezclar con compras a proveedor).
-- Ejecutar una vez en phpMyAdmin o mysql CLI sobre la BD `minimarket`.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `categorias_gasto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_categoria_gasto_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `categorias_gasto` (`id`, `nombre`, `activo`) VALUES
(1, 'Alquiler / Local', 1),
(2, 'Servicios (luz, agua, internet)', 1),
(3, 'Nómina / honorarios', 1),
(4, 'Impuestos / tributos', 1),
(5, 'Mantenimiento / equipos', 1),
(6, 'Marketing / publicidad', 1),
(7, 'Otros', 1);

CREATE TABLE IF NOT EXISTS `gastos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_categoria_gasto` int(11) NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `fecha_gasto` date NOT NULL,
  `observaciones` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `gastos_ibfk_sucursal` (`id_sucursal`),
  KEY `gastos_ibfk_usuario` (`id_usuario`),
  KEY `gastos_ibfk_categoria` (`id_categoria_gasto`),
  KEY `idx_gastos_fecha` (`fecha_gasto`),
  CONSTRAINT `gastos_ibfk_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  CONSTRAINT `gastos_ibfk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `gastos_ibfk_categoria` FOREIGN KEY (`id_categoria_gasto`) REFERENCES `categorias_gasto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
