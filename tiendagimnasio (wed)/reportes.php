<?php
include 'conexion.php';

// Productos con stock bajo
$productos_bajo = $conn->query("SELECT * FROM productos WHERE stock <= stock_minimo ORDER BY stock ASC");

// Kits que no se pueden armar
$kits_sin_stock = [];
$kits = $conn->query("SELECT * FROM kits");

while ($kit = $kits->fetch_assoc()) {
    $kit_id = $kit['id'];
    $productos_kit = $conn->query("SELECT kp.cantidad, p.stock, p.nombre
                                  FROM kit_productos kp
                                  JOIN productos p ON kp.producto_id = p.id
                                  WHERE kp.kit_id = $kit_id");

    $puede_armarse = true;
    $faltantes = [];

    while ($prod = $productos_kit->fetch_assoc()) {
        if ($prod['stock'] < $prod['cantidad']) {
            $puede_armarse = false;
            $faltantes[] = $prod['nombre'] . " (faltan " . ($prod['cantidad'] - $prod['stock']) . ")";
        }
    }

    if (!$puede_armarse) {
        $kits_sin_stock[] = [
            'kit' => $kit,
            'faltantes' => $faltantes
        ];
    }
}

// Estadísticas
$total_productos = $conn->query("SELECT COUNT(*) as total FROM productos")->fetch_assoc()['total'];
$productos_sin_stock = $conn->query("SELECT COUNT(*) as total FROM productos WHERE stock = 0")->fetch_assoc()['total'];
$total_kits = $conn->query("SELECT COUNT(*) as total FROM kits")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Tienda Gimnasio</title>
    <link rel="stylesheet" href="estilo.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📊 Reportes y Alertas</h1>
            <nav>
                <a href="index.php" class="btn btn-secondary">⬅️ Volver</a>
            </nav>
        </header>

        <!-- Estadísticas generales -->
        <section class="productos-section">
            <h2>📈 Estadísticas Generales</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?= $total_productos ?></h3>
                    <p>Total Productos</p>
                </div>
                <div class="stat-card warning">
                    <h3><?= $productos_bajo->num_rows ?></h3>
                    <p>Stock Bajo</p>
                </div>
                <div class="stat-card danger">
                    <h3><?= $productos_sin_stock ?></h3>
                    <p>Sin Stock</p>
                </div>
                <div class="stat-card">
                    <h3><?= $total_kits ?></h3>
                    <p>Total Kits</p>
                </div>
                <div class="stat-card danger">
                    <h3><?= count($kits_sin_stock) ?></h3>
                    <p>Kits No Disponibles</p>
                </div>
            </div>
        </section>

        <!-- Productos con stock bajo -->
        <section class="productos-section">
            <h2>⚠️ Productos con Stock Bajo</h2>
            <?php if ($productos_bajo->num_rows > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Diferencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod = $productos_bajo->fetch_assoc()): ?>
                        <tr class="<?= $prod['stock'] == 0 ? 'sin-stock' : 'stock-bajo' ?>">
                            <td><?= htmlspecialchars($prod['nombre']) ?></td>
                            <td>
                                <span class="stock-badge <?= $prod['stock'] == 0 ? 'sin-stock' : 'bajo' ?>">
                                    <?= $prod['stock'] ?>
                                </span>
                            </td>
                            <td><?= $prod['stock_minimo'] ?></td>
                            <td><?= $prod['stock_minimo'] - $prod['stock'] ?></td>
                            <td>
                                <?php if($prod['stock'] == 0): ?>
                                    <span class="status-badge danger">🚫 Sin Stock</span>
                                <?php else: ?>
                                    <span class="status-badge warning">⚠️ Stock Bajo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                ✅ Todos los productos tienen stock suficiente.
            </div>
            <?php endif; ?>
        </section>

        <!-- Kits no disponibles -->
        <section class="kits-section">
            <h2>📦 Kits No Disponibles</h2>
            <?php if (count($kits_sin_stock) > 0): ?>
            <div class="kits-grid">
                <?php foreach($kits_sin_stock as $kit_info): ?>
                <div class="kit-card">
                    <h3><?= htmlspecialchars($kit_info['kit']['nombre']) ?></h3>
                    <p class="precio">$<?= number_format($kit_info['kit']['precio'], 2) ?></p>

                    <h4>Productos faltantes:</h4>
                    <ul class="faltantes-list">
                        <?php foreach($kit_info['faltantes'] as $faltante): ?>
                        <li class="sin-stock"><?= htmlspecialchars($faltante) ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="kit-status">
                        <span class="status-badge danger">❌ No Disponible</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-success">
                ✅ Todos los kits pueden armarse con el stock actual.
            </div>
            <?php endif; ?>
        </section>
    </div>

    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #007bff;
        }

        .stat-card.warning {
            border-left-color: #ffc107;
        }

        .stat-card.danger {
            border-left-color: #dc3545;
        }

        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #333;
        }

        .stat-card p {
            color: #6c757d;
            font-weight: 500;
        }

        .faltantes-list {
            list-style: none;
            margin: 10px 0;
        }

        .faltantes-list li {
            padding: 5px 0;
            color: #dc3545;
            font-weight: 500;
        }

        .sin-stock {
            background-color: #fff5f5 !important;
        }

        .stock-badge.sin-stock {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</body>
</html>