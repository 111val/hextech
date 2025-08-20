<?php
include 'conexion.php';

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre");

if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $productos_kit = $_POST['productos'];
    $cantidades = $_POST['cantidades'];
    
    // Insertar kit
    $stmt = $conn->prepare("INSERT INTO kits (nombre, descripcion, precio) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $nombre, $descripcion, $precio);
    
    if ($stmt->execute()) {
        $kit_id = $conn->insert_id;
        
        // Insertar productos del kit
        for ($i = 0; $i < count($productos_kit); $i++) {
            if ($productos_kit[$i] && $cantidades[$i] > 0) {
                $stmt2 = $conn->prepare("INSERT INTO kit_productos (kit_id, producto_id, cantidad) VALUES (?, ?, ?)");
                $stmt2->bind_param("iii", $kit_id, $productos_kit[$i], $cantidades[$i]);
                $stmt2->execute();
            }
        }
        
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Kit - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📦 Crear Kit</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
            </nav>
        </header>

        <section class="form-section">
            <form method="POST" class="kit-form">
                <div class="form-group">
                    <label for="nombre">Nombre del Kit:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="precio">Precio del Kit:</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                </div>

                <h3>Productos del Kit</h3>
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
                    <button type="submit" class="btn btn-primary">💾 Crear Kit</button>
                    <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
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