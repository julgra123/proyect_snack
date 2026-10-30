<?php
require_once 'config.php';require_admin();
$fecha=$_GET['fecha']??date('Y-m-d');
$st=db()->prepare("SELECT v.*,u.nombre AS vendedor FROM ventas v JOIN usuarios u ON u.id=v.usuario_id WHERE DATE(v.fec_venta)=? ORDER BY v.fec_venta DESC");$st->execute([$fecha]);$sales=$st->fetchAll();
$sum=db()->prepare("SELECT COALESCE(SUM(total),0) FROM ventas WHERE DATE(fec_venta)=?");$sum->execute([$fecha]);$total=(float)$sum->fetchColumn();
$page_title='Informe de ventas';include 'header.php';
?>
<div class="no-print"><h1>Informe diario de ventas</h1><form method="get" class="filter"><label>Fecha <input type="date" name="fecha" value="<?=h($fecha)?>"></label><button class="btn primary">Consultar</button><button type="button" class="btn secondary" onclick="window.print()">Imprimir informe</button></form></div>
<div class="print-head"><h1>Informe diario de ventas</h1><p>Fecha: <?=h($fecha)?></p></div>
<div class="stats"><div><small>Ventas registradas</small><strong><?=count($sales)?></strong></div><div><small>Total vendido</small><strong><?=money($total)?></strong></div></div>
<div class="table-wrap"><table><tr><th>N° venta</th><th>Hora</th><th>Vendedor</th><th>Total</th><th class="no-print">Detalle</th></tr>
<?php foreach($sales as $s): ?><tr><td><?=h($s['cod_ventas'])?></td><td><?=date('H:i',strtotime($s['fec_venta']))?></td><td><?=h($s['vendedor'])?></td><td><?=money($s['total'])?></td><td class="no-print"><a href="ticket.php?id=<?=$s['id']?>">Ver / imprimir</a></td></tr><?php endforeach; ?>
<?php if(!$sales): ?><tr><td colspan="5">No hay ventas registradas en esta fecha.</td></tr><?php endif; ?></table></div>
<?php include 'footer.php'; ?>
