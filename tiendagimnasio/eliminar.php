<?php
include 'conexion.php';

$id = intval($_GET['id']);

// Verificar si el producto está en algún kit
$en_kit = $conn->query("SELECT COUNT(*) as total FROM kit_productos WHERE producto_id = $id")->fetch_assoc()['total'];

if ($en_kit > 0) {
    echo "<script>alert('No se puede eliminar el producto porque está incluido en uno o más kits.'); window.location='index.php';</script>";
    exit;
}

// Obtener imagen para eliminarla
$producto = $conn->query("SELECT imagen FROM productos WHERE id = $id")->fetch_assoc();

if ($conn->query("DELETE FROM productos WHERE id = $id")) {
    if ($producto['imagen']) {
        unlink('imagenes/' . $producto['imagen']);
    }
}

header("Location: index.php");
?>