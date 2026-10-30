<?php
require_once 'config.php';
require_admin();

// La actualización manual de existencias queda reservada al administrador.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['producto_id'] ?? 0);
    $cantidad = max(0, (int)($_POST['cantidad'] ?? 0));
    db()->prepare('UPDATE inventario SET cantidad=?, fec_ingreso_producto=CURDATE() WHERE producto_id=?')->execute([$cantidad, $id]);
    flash('Inventario actualizado.');
    header('Location: inventario.php'); exit;
}

$rows = db()->query("SELECT p.id, p.nomb_producto, p.cod_producto, p.precio, i.cantidad, i.fec_ingreso_producto
    FROM productos p JOIN inventario i ON i.producto_id=p.id
    WHERE p.activo=1 ORDER BY p.nomb_producto")->fetchAll();
$page_title = 'Inventario';
include 'header.php';
?>
<div class="no-print">
    <h1>Control de inventario</h1>
    <p class="muted">Las ventas descuentan automáticamente las unidades. Aquí puedes corregir las existencias y sacar una copia impresa.</p>
    <button type="button" class="btn secondary" onclick="window.print()">Imprimir inventario</button>
</div>
<div class="print-head"><h1>Inventario de productos</h1><p>Fecha de impresión: <?= h(date('d/m/Y H:i')) ?></p></div>
<div class="stats"><div><small>Productos en inventario</small><strong><?= count($rows) ?></strong></div><div><small>Unidades disponibles</small><strong><?= array_sum(array_map(fn($r) => (int)$r['cantidad'], $rows)) ?></strong></div></div>
<div class="table-wrap"><table>
<tr><th>Código</th><th>Producto</th><th>Precio</th><th>Stock actual</th><th>Último ingreso</th><th class="no-print">Nuevo stock / acción</th></tr>
<?php foreach ($rows as $p): ?><tr>
<td><?= h($p['cod_producto']) ?></td><td><?= h($p['nomb_producto']) ?></td><td><?= money($p['precio']) ?></td><td><?= (int)$p['cantidad'] ?></td><td><?= h($p['fec_ingreso_producto'] ?? '—') ?></td>
<td class="no-print"><form class="inline-form" method="post"><input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>"><input class="qty" type="number" min="0" name="cantidad" value="<?= (int)$p['cantidad'] ?>" required><button class="btn primary">Guardar</button></form></td>
</tr><?php endforeach; ?>
<?php if (!$rows): ?><tr><td colspan="6">No hay productos activos en el inventario.</td></tr><?php endif; ?>
</table></div>
<?php include 'footer.php'; ?>
