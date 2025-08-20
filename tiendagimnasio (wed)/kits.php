<?php
include 'conexion.php';

// Eliminar kit
if (isset($_GET['eliminar_kit'])) {
    $kit_id = intval($_GET['eliminar_kit']);
    
    // Verificar si el kit está en algún pedido
    $en_pedido = $conn->query("SELECT COUNT(*) as total FROM pedido_kits WHERE kit_id = $kit_id")->fetch_assoc()['total'];
    
    if ($en_pedido > 0) {
        echo "<script>alert('No se puede eliminar el kit porque está incluido en uno o más pedidos.'); window.location='kits.php';</script>";
        exit;
    }
    
    // Eliminar kit (los productos del kit se eliminan automáticamente por CASCADE)
    if ($conn->query("DELETE FROM kits WHERE id = $kit_id")) {
        echo "<script>alert('Kit eliminado exitosamente'); window.location='kits.php';</script>";
    }
    exit;
}

// Crear pedido
if ($_POST && isset($_POST['crear_pedido'])) {
    $kits_pedido = $_POST['kits'];
    $cantidades = $_POST['cantidades'];
    $total = 0;
    
    // Verificar stock disponible
    $stock_suficiente = true;
    $errores = [];
    
    for ($i = 0; $i < count($kits_pedido); $i++) {
        if ($kits_pedido[$i] && $cantidades[$i] > 0) {
            $kit_id = $kits_pedido[$i];
            $cantidad_pedida = $cantidades[$i];
            
            // Verificar stock de productos del kit
            $productos_kit = $conn->query("SELECT kp.producto_id, kp.cantidad, p.nombre, p.stock
                                          FROM kit_productos kp
                                          JOIN productos p ON kp.producto_id = p.id
                                          WHERE kp.kit_id = $kit_id");
            
            while ($prod = $productos_kit->fetch_assoc()) {
                $stock_necesario = $prod['cantidad'] * $cantidad_pedida;
                if ($prod['stock'] < $stock_necesario) {
                    $stock_suficiente = false;
                    $errores[] = "Stock insuficiente para " . $prod['nombre'] . " (necesario: $stock_necesario, disponible: " . $prod['stock'] . ")";
                }
            }
        }
    }
    
    if ($stock_suficiente) {
        // Crear pedido
        $stmt = $conn->prepare("INSERT INTO pedidos (total) VALUES (0)");
        $stmt->execute();
        $pedido_id = $conn->insert_id;
        
        // Agregar kits al pedido y actualizar stock
        for ($i = 0; $i < count($kits_pedido); $i++) {
            if ($kits_pedido[$i] && $cantidades[$i] > 0) {
                $kit_id = $kits_pedido[$i];
                $cantidad = $cantidades[$i];
                
                // Obtener precio del kit
                $kit = $conn->query("SELECT precio FROM kits WHERE id = $kit_id")->fetch_assoc();
                $precio_unitario = $kit['precio'];
                $subtotal = $precio_unitario * $cantidad;
                $total += $subtotal;
                
                // Insertar en pedido_kits
                $stmt = $conn->prepare("INSERT INTO pedido_kits (pedido_id, kit_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiid", $pedido_id, $kit_id, $cantidad, $precio_unitario);
                $stmt->execute();
                
                // Actualizar stock de productos
                $productos_kit = $conn->query("SELECT producto_id, cantidad FROM kit_productos WHERE kit_id = $kit_id");
                while ($prod = $productos_kit->fetch_assoc()) {
                    $stock_a_descontar = $prod['cantidad'] * $cantidad;
                    $conn->query("UPDATE productos SET stock = stock - $stock_a_descontar WHERE id = " . $prod['producto_id']);
                }
            }
        }
        
        // Actualizar total del pedido
        $conn->query("UPDATE pedidos SET total = $total WHERE id = $pedido_id");
        
        echo "<script>alert('Pedido creado exitosamente'); window.location='kits.php';</script>";
    } else {
        $mensaje_error = implode("\\n", $errores);
        echo "<script>alert('$mensaje_error');</script>";
    }
}

// Obtener pedidos
$pedidos = $conn->query("SELECT * FROM pedidos ORDER BY fecha DESC");
$kits = $conn->query("SELECT * FROM kits ORDER BY nombre");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🛒 Gestión de Pedidos</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
                <a href="agregar_kit.php" class="btn btn-success">📦 Crear Kit</a>
                <a href="pedidos_distribuidor.php" class="btn btn-danger">🚨 Pedidos Distribuidor</a>
            </nav>
        </header>

        <!-- Gestión de Kits Disponibles -->
        <section class="kits-section">
            <h2>📦 Kits Disponibles - Gestión</h2>
            <div class="kits-grid">
                <?php 
                $kits_gestion = $conn->query("SELECT * FROM kits ORDER BY nombre");
                while($kit = $kits_gestion->fetch_assoc()): 
                ?>
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

                    <!-- Acciones del Kit -->
                    <div class="kit-actions" style="margin-top: 15px; text-align: center;">
                        <a href="editar_kit.php?id=<?= $kit['id'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                        <a href="kits.php?eliminar_kit=<?= $kit['id'] ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('¿Estás seguro de eliminar este kit? Esta acción no se puede deshacer.')">
                           🗑️ Eliminar
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    </style>
</body>
</html>