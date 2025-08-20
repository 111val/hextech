<?php
include 'conexion.php';

$pedido_id = intval($_GET['id']);

// Obtener pedido
$pedido = $conn->query("SELECT * FROM pedidos WHERE id = $pedido_id")->fetch_assoc();
if (!$pedido) {
    header("Location: pedidos.php");
    exit;
}

// Obtener kits del pedido
$kits_pedido = $conn->query("SELECT pk.*, k.nombre
                            FROM pedido_kits pk
                            JOIN kits k ON pk.kit_id = k.id
                            WHERE pk.pedido_id = $pedido_id");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Pedido #<?= $pedido_id ?> - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>👁️ Pedido #<?= $pedido_id ?></h1>
            <nav>
                <a href="pedidos.php" class="btn btn-secondary">⬅️ Volver</a>
                <a href="editar_pedido.php?id=<?= $pedido_id ?>" class="btn btn-warning">✏️ Editar</a>
            </nav>
        </header>

        <section class="productos-section">
            <h2>📋 Detalles del Pedido</h2>

            <div class="pedido-info">
                <p><strong>ID:</strong> #<?= $pedido['id'] ?></p>
                <p><strong>Fecha:</strong> <?= date('d/m/Y H:i:s', strtotime($pedido['fecha'])) ?></p>
                <p><strong>Total:</strong> $<?= number_format($pedido['total'], 2) ?></p>
            </div>

            <h3>Kits del Pedido</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kit</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Productos Incluidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($kit = $kits_pedido->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($kit['nombre']) ?></td>
                            <td><?= $kit['cantidad'] ?></td>
                            <td>$<?= number_format($kit['precio_unitario'], 2) ?></td>
                            <td>$<?= number_format($kit['precio_unitario'] * $kit['cantidad'], 2) ?></td>
                            <td>
                                <?php
                                $productos_kit = $conn->query("SELECT p.nombre, kp.cantidad
                                                              FROM kit_productos kp
                                                              JOIN productos p ON kp.producto_id = p.id
                                                              WHERE kp.kit_id = " . $kit['kit_id']);
                                $productos_list = [];
                                while($prod = $productos_kit->fetch_assoc()) {
                                    $productos_list[] = $prod['nombre'] . " x" . ($prod['cantidad'] * $kit['cantidad']);
                                }
                                echo implode(", ", $productos_list);
                                ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <style>
        .pedido-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .pedido-info p {
            margin-bottom: 10px;
            font-size: 16px;
        }
    </style>
</body>
</html>