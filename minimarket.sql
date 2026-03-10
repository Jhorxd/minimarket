-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 10, 2026 at 02:47 PM
-- Server version: 5.7.44
-- PHP Version: 7.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `minimarket`
--

-- --------------------------------------------------------

--
-- Table structure for table `cajas`
--

CREATE TABLE `cajas` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `monto_apertura` decimal(10,2) DEFAULT NULL,
  `monto_cierre` decimal(10,2) DEFAULT NULL,
  `fecha_apertura` datetime DEFAULT NULL,
  `fecha_cierre` datetime DEFAULT NULL,
  `estado` enum('Abierta','Cerrada') DEFAULT 'Abierta'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `proveedor` varchar(200) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `compra_detalle`
--

CREATE TABLE `compra_detalle` (
  `id` int(11) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_compra` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `kardex`
--

CREATE TABLE `kardex` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida') DEFAULT NULL,
  `motivo` enum('Venta','Compra','Ajuste','Traslado') DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `stock_resultante` decimal(10,2) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text,
  `categoria` varchar(100) DEFAULT NULL,
  `precio_compra` decimal(10,2) DEFAULT '0.00',
  `precio_venta` decimal(10,2) DEFAULT '0.00',
  `stock` decimal(10,2) DEFAULT '0.00',
  `stock_minimo` int(11) DEFAULT '5',
  `id_sucursal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sucursales`
--

CREATE TABLE `sucursales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` text,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sucursales`
--

INSERT INTO `sucursales` (`id`, `nombre`, `direccion`, `estado`) VALUES
(1, 'Sede Central', 'Av. Principal 123 - Lima', 1);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','vendedor') DEFAULT 'vendedor',
  `id_sucursal` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `rol`, `id_sucursal`, `estado`) VALUES
(1, 'Administrador del Sistema', 'admin', '0192023a7bbd73250516f069df18b500', 'admin', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_caja` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `venta_detalle`
--

CREATE TABLE `venta_detalle` (
  `id` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cajas`
--
ALTER TABLE `cajas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indexes for table `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indexes for table `sucursales`
--
ALTER TABLE `sucursales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `id_sucursal` (`id_sucursal`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_caja` (`id_caja`);

--
-- Indexes for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cajas`
--
ALTER TABLE `cajas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compra_detalle`
--
ALTER TABLE `compra_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sucursales`
--
ALTER TABLE `sucursales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cajas`
--
ALTER TABLE `cajas`
  ADD CONSTRAINT `cajas_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `cajas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);

--
-- Constraints for table `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Constraints for table `compra_detalle`
--
ALTER TABLE `compra_detalle`
  ADD CONSTRAINT `compra_detalle_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `compra_detalle_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Constraints for table `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `kardex_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`);

--
-- Constraints for table `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursales` (`id`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_caja`) REFERENCES `cajas` (`id`);

--
-- Constraints for table `venta_detalle`
--
ALTER TABLE `venta_detalle`
  ADD CONSTRAINT `venta_detalle_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venta_detalle_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
