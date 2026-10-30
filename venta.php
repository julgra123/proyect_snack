<?php
require_once 'config.php'; require_login();
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['finalizar'])) {
    $cart=$_SESSION['cart']??[];
    if (!$cart) { flash('Selecciona al menos un producto.','error'); header('Location: venta.php'); exit; }
    $pdo=db();
    try {
        $pdo->beginTransaction(); $total=0; $items=[];
        foreach($cart as $id=>$qty) {
            $st=$pdo->prepare("SELECT p.id,p.nomb_producto,p.precio,i.cantidad FROM productos p JOIN inventario i ON i.producto_id=p.id WHERE p.id=? AND p.activo=1 FOR UPDATE");
            $st->execute([$id]); $p=$st->fetch();
            if(!$p || $qty<1 || $p['cantidad']<$qty) throw new Exception('Stock insuficiente para '.($p['nomb_producto']??'un producto').'.');
            $sub=(float)$p['precio']*(int)$qty; $total+=$sub; $items[]=['p'=>$p,'qty'=>(int)$qty,'sub'=>$sub];
        }
        $cod='V'.date('ymdHis').random_int(10,99);
        $st=$pdo->prepare("INSERT INTO ventas(cod_ventas,usuario_id,total) VALUES(?,?,?)");
        $st->execute([$cod,$_SESSION['user']['id'],$total]); $saleId=(int)$pdo->lastInsertId();
        $det=$pdo->prepare("INSERT INTO detalle_venta(venta_id,producto_id,cantidad,precio_unitario,subtotal) VALUES(?,?,?,?,?)");
        $upd=$pdo->prepare("UPDATE inventario SET cantidad=cantidad-? WHERE producto_id=?");
        foreach($items as $it) { $det->execute([$saleId,$it['p']['id'],$it['qty'],$it['p']['precio'],$it['sub']]); $upd->execute([$it['qty'],$it['p']['id']]); }
        $pdo->commit(); $_SESSION['cart']=[]; $_SESSION['last_sale']=$saleId;
        header('Location: ticket.php?id='.$saleId); exit;
    } catch(Throwable $e) { if($pdo->inTransaction())$pdo->rollBack(); flash($e->getMessage(),'error'); header('Location: venta.php'); exit; }
}
if(isset($_GET['add'])) { $id=(int)$_GET['add']; $st=db()->prepare("SELECT id FROM productos WHERE id=? AND activo=1");$st->execute([$id]); if($st->fetch()) {$_SESSION['cart'][$id]=($_SESSION['cart'][$id]??0)+1;} header('Location: venta.php');exit; }
if(isset($_GET['remove'])) {unset($_SESSION['cart'][(int)$_GET['remove']]);header('Location: venta.php');exit;}
if(isset($_POST['actualizar'])) {foreach(($_POST['cant']??[]) as $id=>$qty){$qty=max(0,(int)$qty);if($qty===0)unset($_SESSION['cart'][(int)$id]);else $_SESSION['cart'][(int)$id]=$qty;}header('Location: venta.php');exit;}
$page_title='Realizar venta'; include 'header.php';
$products=db()->query("SELECT p.*,i.cantidad FROM productos p LEFT JOIN inventario i ON i.producto_id=p.id WHERE p.activo=1 ORDER BY p.nomb_producto")->fetchAll();
$cart=$_SESSION['cart']??[]; $total=0;
?>
<h1>Realizar venta</h1><p class="muted">Selecciona uno o varios productos. El total se calcula automáticamente.</p>
<div class="sales-layout"><section><h2>Productos disponibles</h2><div class="product-grid">
<?php foreach($products as $p): ?><article class="product-card">
<?php if($p['imagen'] && file_exists(__DIR__.'/uploads/'.$p['imagen'])): ?><img src="uploads/<?=h($p['imagen'])?>" alt="<?=h($p['nomb_producto'])?>"><?php else: ?><div class="no-image">📦</div><?php endif; ?>
<div class="product-info"><b><?=h($p['nomb_producto'])?></b><strong><?=money($p['precio'])?></strong><small>Stock: <?= (int)$p['cantidad']?></small>
<?php if((int)$p['cantidad']>0): ?><a class="btn primary full" href="venta.php?add=<?=$p['id']?>">Agregar</a><?php else: ?><button class="btn disabled full" disabled>Agotado</button><?php endif; ?></div></article><?php endforeach; ?>
<?php if(!$products): ?><p>No hay productos registrados.</p><?php endif; ?></div></section>
<aside class="cart"><h2>Lista de venta</h2><form method="post"><table><thead><tr><th>Producto</th><th>Cant.</th><th>Subtotal</th><th></th></tr></thead><tbody>
<?php foreach($cart as $id=>$qty): $st=db()->prepare("SELECT nomb_producto,precio FROM productos WHERE id=?");$st->execute([$id]);$p=$st->fetch();if(!$p)continue;$sub=$p['precio']*$qty;$total+=$sub; ?>
<tr><td><?=h($p['nomb_producto'])?><br><small><?=money($p['precio'])?> c/u</small></td><td><input class="qty" type="number" min="0" name="cant[<?=$id?>]" value="<?=$qty?>"></td><td><?=money($sub)?></td><td><a class="remove" href="venta.php?remove=<?=$id?>">×</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php if(!$cart): ?><p class="muted">Todavía no agregaste productos.</p><?php endif; ?>
<button class="btn secondary full" name="actualizar">Actualizar cantidades</button><div class="total">TOTAL <span><?=money($total)?></span></div>
<button class="btn success full" name="finalizar" value="1" <?=!$cart?'disabled':''?> onclick="return confirm('¿Registrar esta venta?')">Finalizar venta</button></form></aside></div>
<?php include 'footer.php'; ?>
