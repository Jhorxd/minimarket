<?php
$db = new mysqli('localhost', 'root', '', 'minimarket_2');
if ($db->connect_error) die('Connect Error: ' . $db->connect_error);

$queries = [
    "ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS talla VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS color VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE venta_detalles ADD COLUMN IF NOT EXISTS diseno VARCHAR(100) DEFAULT NULL",
    "ALTER TABLE compra_detalle ADD COLUMN IF NOT EXISTS talla VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE compra_detalle ADD COLUMN IF NOT EXISTS color VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE compra_detalle ADD COLUMN IF NOT EXISTS diseno VARCHAR(100) DEFAULT NULL"
];

foreach ($queries as $q) {
    if (!$db->query($q)) {
        echo "Error: " . $db->error . "\n";
    } else {
        echo "Success: " . $q . "\n";
    }
}
?>
