<?php
include 'conexion.php';

// Crear pedido a distribuidor
if ($_POST && isset($_POST['crear_pedido_distribuidor'])) {
    $distribuidor = $_POST['distribuidor'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $productos = $_POST['productos_pedido'];
    $cantidades = $_POST['cantidades_pedido'];
    $observaciones = $_POST['observaciones'];
    
    $total_productos = 0;
    $detalle_productos = [];
    
    for ($i = 0; $i < count($productos); $i++) {
        if ($productos[$i] && $cantidades[$i] > 0) {
            $producto = $conn->query("SELECT nombre, precio FROM productos WHERE id = " . $productos[$i])->fetch_assoc();
            $total_productos += $cantidades[$i];
            $detalle_productos[] = $producto['nombre'] . " x" . $cantidades[$i];
        }
    }
    
    $detalle_texto = implode(", ", $detalle_productos);
    
    $stmt = $conn->prepare("INSERT INTO pedidos_distribuidor (distribuidor, email, telefono, productos_detalle, total_productos, observaciones, estado) VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')");
    $stmt->bind_param("ssssiss", $distribuidor, $email, $telefono, $detalle_texto, $total_productos, $observaciones);
    
    if ($stmt->execute()) {
        echo "<script>alert('Pedido enviado al distribuidor exitosamente'); window.location='pedidos_distribuidor.php';</script>";
    }
}

// Obtener productos con stock crítico
$productos_criticos = $conn->query("SELECT * FROM productos WHERE stock = 0 OR stock <= (stock_minimo / 2) ORDER BY stock ASC");

// Obtener pedidos a distribuidores
$pedidos_distribuidor = $conn->query("SELECT * FROM pedidos_distribuidor ORDER BY fecha DESC");

// Obtener todos los productos para el formulario
$todos_productos = $conn->query("SELECT * FROM productos ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos a Distribuidores - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🚨 Pedidos a Distribuidores</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
                <a href="reportes.php" class="btn btn-info">📊 Reportes</a>
            </nav>
        </header>

        <!-- Productos críticos -->
        <section class="distribuidor-section">
            <h2>⚠️ Productos con Stock Crítico</h2>
            <?php if ($productos_criticos->num_rows > 0): ?>
            <div class="kits-grid">
                <?php while($producto = $productos_criticos->fetch_assoc()): ?>
                <div class="pedido-distribuidor-card">
                    <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
                    <div class="productos-necesarios">
                        <p><strong>Stock actual:</strong> <?= $producto['stock'] ?></p>
                        <p><strong>Stock mínimo:</strong> <?= $producto['stock_minimo'] ?></p>
                        <p><strong>Precio:</strong> $<?= number_format($producto['precio'], 2) ?></p>
                        <?php if($producto['stock'] == 0): ?>
                            <span class="urgente-badge">🚫 SIN STOCK</span>
                        <?php else: ?>
                            <span class="status-badge warning">⚠️ CRÍTICO</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                ✅ No hay productos con stock crítico en este momento.
            </div>
            <?php endif; ?>
        </section>

        <!-- Formulario para nuevo pedido -->
        <section class="form-section">
            <div class="distribuidor-form">
                <h3>📞 Realizar Pedido a Distribuidor</h3>
                <form method="POST" class="pedido-form">
                    <div class="distribuidor-info">
                        <div class="form-group">
                            <label for="distribuidor">Nombre del Distribuidor:</label>
                            <input type="text" id="distribuidor" name="distribuidor" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" required>
                        </div>
                    </div>

                    <h4>Productos a Solicitar</h4>
                    <div id="productos-distribuidor-container">
                        <div class="producto-kit-row">
                            <select name="productos_pedido[]" class="producto-select">
                                <option value="">Seleccionar producto...</option>
                                <?php while($producto = $todos_productos->fetch_assoc()): ?>
                                <option value="<?= $producto['id'] ?>"><?= htmlspecialchars($producto['nombre']) ?> (Stock: <?= $producto['stock'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                            <input type="number" name="cantidades_pedido[]" placeholder="Cantidad" min="1" class="cantidad-input">
                            <button type="button" onclick="eliminarProductoDistribuidor(this)" class="btn btn-danger btn-sm">❌</button>
                        </div>
                    </div>

                    <button type="button" onclick="agregarProductoDistribuidor()" class="btn btn-success">➕ Agregar Producto</button>

                    <div class="form-group">
                        <label for="observaciones">Observaciones:</label>
                        <textarea id="observaciones" name="observaciones" rows="3" placeholder="Urgencia, condiciones especiales, etc."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="crear_pedido_distribuidor" class="btn-distribuidor">🚀 Enviar Pedido</button>
                    </div>
                </form>
            </div>
        </section>

        <!-- Historial de pedidos -->
        <section class="pedidos-section">
            <h2>📋 Historial de Pedidos a Distribuidores</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Distribuidor</th>
                            <th>Contacto</th>
                            <th>Productos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($pedido = $pedidos_distribuidor->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $pedido['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($pedido['fecha'])) ?></td>
                            <td><?= htmlspecialchars($pedido['distribuidor']) ?></td>
                            <td>
                                📧 <?= htmlspecialchars($pedido['email']) ?><br>
                                📞 <?= htmlspecialchars($pedido['telefono']) ?>
                            </td>
                            <td><?= htmlspecialchars($pedido['productos_detalle']) ?></td>
                            <td>
                                <?php
                                $estado_class = '';
                                $estado_icon = '';
                                switch($pedido['estado']) {
                                    case 'Pendiente':
                                        $estado_class = 'warning';
                                        $estado_icon = '⏳';
                                        break;
                                    case 'Enviado':
                                        $estado_class = 'info';
                                        $estado_icon = '📦';
                                        break;
                                    case 'Recibido':
                                        $estado_class = 'success';
                                        $estado_icon = '✅';
                                        break;
                                    case 'Cancelado':
                                        $estado_class = 'danger';
                                        $estado_icon = '❌';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?= $estado_class ?>"><?= $estado_icon ?> <?= $pedido['estado'] ?></span>
                            </td>
                            <td class="acciones">
                                <a href="actualizar_pedido_distribuidor.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-warning">✏️</a>
                                <?php if($pedido['estado'] == 'Recibido'): ?>
                                <a href="recibir_stock.php?id=<?= $pedido['id'] ?>" class="btn btn-sm btn-success">📥</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function agregarProductoDistribuidor() {
            const container = document.getElementById('productos-distribuidor-container');
            const newRow = container.firstElementChild.cloneNode(true);
            newRow.querySelector('select').value = '';
            newRow.querySelector('input').value = '';
            container.appendChild(newRow);
        }

        function eliminarProductoDistribuidor(btn) {
            const container = document.getElementById('productos-distribuidor-container');
            if (container.children.length > 1) {
                btn.parentElement.remove();
            }
        }
    </script>
</body>
</html>