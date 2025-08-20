<?php
include 'conexion.php';

if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $stock_minimo = $_POST['stock_minimo'];
    
    $imagen = '';
    if ($_FILES['imagen']['name']) {
        $imagen = time() . '_' . $_FILES['imagen']['name'];
        move_uploaded_file($_FILES['imagen']['tmp_name'], 'imagenes/' . $imagen);
    }
    
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo, imagen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiis", $nombre, $descripcion, $precio, $stock, $stock_minimo, $imagen);
    
    if ($stmt->execute()) {
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
    <title>Agregar Producto - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>➕ Agregar Producto</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
            </nav>
        </header>

        <section class="form-section">
            <form method="POST" enctype="multipart/form-data" class="producto-form">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock Inicial:</label>
                        <input type="number" id="stock" name="stock" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo:</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" min="1" value="5" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen del Producto:</label>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Guardar Producto</button>
                    <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>