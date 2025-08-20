<?php
include 'conexion.php';

$productos = $conn->query("SELECT * FROM productos ORDER BY nombre");
$kits = $conn->query("SELECT * FROM kits ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Gimnasio - Gestión de Stock</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🏋️ Tienda Gimnasio</h1>
            <nav>
                <a href="agregar.php" class="btn btn-primary">➕ Agregar Producto</a>
                <a href="agregar_kit.php" class="btn btn-success">📦 Crear Kit</a>
                <a href="kits.php" class="btn btn-info">🛒 kits</a>
                <a href="reportes.php" class="btn btn-warning">📊 Reportes</a>
                <a href="pedidos_distribuidor.php" class="btn btn-danger">🚨 Pedidos Distribuidor</a>
            </nav>
        </header>

        <section class="productos-section">
            <h2>📋 Productos</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($producto = $productos->fetch_assoc()): ?>
                        <tr class="<?= $producto['stock'] <= $producto['stock_minimo'] ? 'stock-bajo' : '' ?>">
                            <td>
                                <?php if($producto['imagen']): ?>
                                    <img src="imagenes/<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>" class="producto-img">
                                <?php else: ?>
                                    <div class="no-image">📷</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td>$<?= number_format($producto['precio'], 2) ?></td>
                            <td>
                                <span class="stock-badge <?= $producto['stock'] == 0 ? 'sin-stock' : ($producto['stock'] <= $producto['stock_minimo'] ? 'bajo' : 'normal') ?>">
                                    <?= $producto['stock'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if($producto['stock'] == 0): ?>
                                    <span class="status-badge danger">🚫 Sin Stock</span>
                                <?php elseif($producto['stock'] <= $producto['stock_minimo']): ?>
                                    <span class="status-badge warning">⚠️ Stock Bajo</span>
                                <?php else: ?>
                                    <span class="status-badge success">✅ Disponible</span>
                                <?php endif; ?>
                            </td>
                            <td class="acciones">
                                <a href="editar.php?id=<?= $producto['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                                <a href="eliminar.php?id=<?= $producto['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="kits-section">
            <h2>📦 Kits Disponibles</h2>
            <div class="kits-grid">
                <?php while($kit = $kits->fetch_assoc()): ?>
                <div class="kit-card">
                    <h3><?= htmlspecialchars($kit['nombre']) ?></h3>
                    <p class="descripcion"><?= htmlspecialchars($kit['descripcion']) ?></p>
                    <p class="precio">$<?= number_format($kit['precio'], 2) ?></p>

                    <h4>Productos incluidos:</h4>
                    <ul class="productos-kit">
                        <?php
                        $productos_kit = $conn->query("SELECT p.nombre, kp.cantidad, p.stock
                                                      FROM kit_productos kp
                                                      JOIN productos p ON kp.producto_id = p.id
                                                      WHERE kp.kit_id = " . $kit['id']);
                        $puede_armarse = true;
                        while($prod = $productos_kit->fetch_assoc()):
                            if($prod['stock'] < $prod['cantidad']) $puede_armarse = false;
                        ?>
                        <li class="<?= $prod['stock'] < $prod['cantidad'] ? 'sin-stock' : '' ?>">
                            <?= htmlspecialchars($prod['nombre']) ?> x<?= $prod['cantidad'] ?>
                            (Stock: <?= $prod['stock'] ?>)
                        </li>
                        <?php endwhile; ?>
                    </ul>

                    <div class="kit-status">
                        <?php if($puede_armarse): ?>
                            <span class="status-badge success">✅ Disponible</span>
                        <?php else: ?>
                            <span class="status-badge danger">❌ Sin Stock</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</body>
</html>