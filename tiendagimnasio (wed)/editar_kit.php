<?php
include 'conexion.php';

$id = intval($_GET['id']);
$kit = $conn->query("SELECT * FROM kits WHERE id = $id")->fetch_assoc();

if (!$kit) {
    header("Location: kits.php");
    exit;
}

// Obtener productos actuales del kit
$productos_actuales = $conn->query("SELECT kp.*, p.nombre 
                                   FROM kit_productos kp 
                                   JOIN productos p ON kp.producto_id = p.id 
                                   WHERE kp.kit_id = $id");

if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $productos_kit = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    
    // Actualizar información del kit
    $stmt = $conn->prepare("UPDATE kits SET nombre=?, descripcion=?, precio=? WHERE id=?");
    $stmt->bind_param("ssdi", $nombre, $descripcion, $precio, $id);
    
    if ($stmt->execute()) {
        // Eliminar productos actuales del kit
        $conn->query("DELETE FROM kit_productos WHERE kit_id = $id");
        
        // Insertar nuevos productos del kit
        for ($i = 0; $i < count($productos_kit); $i++) {
            if ($productos_kit[$i] && $cantidades[$i] > 0) {
                $stmt2 = $conn->prepare("INSERT INTO kit_productos (kit_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt2->bind_param("iii", $id, $productos_kit[$i], $cantidades[$i]);
                $stmt2->execute();
            }
        }
        
        header("Location: kits.php");
        exit;
    }
}

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Kit - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ Editar Kit</h1>
            <nav>
                <a href="kits.php" class="btn btn-secondary">⬅️ Volver</a>
            </nav>
        </header>

        <section class="form-section">
            <form method="POST" class="kit-form">
                <div class="form-group">
                    <label for="nombre">Nombre del Kit:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($kit['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($kit['descripcion']) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="precio">Precio del Kit:</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?= $kit['precio'] ?>" required>
                </div>

                <h3>Productos Actuales del Kit</h3>
                <div class="kits-actuales">
                    <ul>
                        <?php while($prod_actual = $productos_actuales->fetch_assoc()): ?>
                        <li><?= htmlspecialchars($prod_actual['nombre']) ?> x<?= $prod_actual['cantidad'] ?></li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <h3>Nuevos Productos del Kit</h3>
                <div id="productos-container">
                    <div class="producto-kit-row">
                        <select name="productos[]" class="producto-select">
                            <option value="">Seleccionar producto...</option>
                            <?php while($producto = $productos->fetch_assoc()): ?>
                            <option value="<?= $producto['id'] ?>"><?= htmlspecialchars($producto['nombre']) ?> (Stock: <?= $producto['stock'] ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <input type="number" name="cantidades[]" placeholder="Cantidad" min="1" class="cantidad-input">
                        <button type="button" onclick="eliminarProducto(this)" class="btn btn-danger btn-sm">❌</button>
                    </div>
                </div>

                <button type="button" onclick="agregarProducto()" class="btn btn-success">➕ Agregar Producto</button>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Actualizar Kit</button>
                    <a href="kits.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </section>
    </div>

    <script>
        function agregarProducto() {
            const container = document.getElementById('productos-container');
            const newRow = container.firstElementChild.cloneNode(true);
            newRow.querySelector('select').value = '';
            newRow.querySelector('input').value = '';
            container.appendChild(newRow);
        }

        function eliminarProducto(btn) {
            const container = document.getElementById('productos-container');
            if (container.children.length > 1) {
                btn.parentElement.remove();
            }
        }
    </script>
</body>
</html>