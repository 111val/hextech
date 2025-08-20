<?php
include 'conexion.php';

$id = intval($_GET['id']);
$producto = $conn->query("SELECT * FROM productos WHERE id = $id")->fetch_assoc();

if (!$producto) {
    header("Location: index.php");
    exit;
}

if ($_POST) {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $stock_minimo = $_POST['stock_minimo'];
    
    $imagen = $producto['imagen'];
    if ($_FILES['imagen']['name']) {
        if ($imagen) unlink('imagenes/' . $imagen);
        $imagen = time() . '_' . $_FILES['imagen']['name'];
        move_uploaded_file($_FILES['imagen']['tmp_name'], 'imagenes/' . $imagen);
    }
    
    $stmt = $conn->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, stock_minimo=?, imagen=? WHERE id=?");
    $stmt->bind_param("ssdiisi", $nombre, $descripcion, $precio, $stock, $stock_minimo, $imagen, $id);
    
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
    <title>Editar Producto - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ Editar Producto</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
            </nav>
        </header>

        <section class="form-section">
            <form method="POST" enctype="multipart/form-data" class="producto-form">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($producto['descripcion']) ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?= $producto['precio'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock">Stock:</label>
                        <input type="number" id="stock" name="stock" min="0" value="<?= $producto['stock'] ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="stock_minimo">Stock Mínimo:</label>
                        <input type="number" id="stock_minimo" name="stock_minimo" min="1" value="<?= $producto['stock_minimo'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen del Producto:</label>
                    <?php if($producto['imagen']): ?>
                        <div class="imagen-actual">
                            <img src="imagenes/<?= $producto['imagen'] ?>" alt="Imagen actual" style="max-width: 200px;">
                            <p>Imagen actual</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="imagen" name="imagen" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">💾 Actualizar Producto</button>
                    <a href="index.php" class="btn btn-secondary">❌ Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>