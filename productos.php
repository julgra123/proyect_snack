<?php
require_once 'config.php'; require_admin();
if(isset($_GET['del'])) { $id=(int)$_GET['del']; db()->prepare("UPDATE productos SET activo=0 WHERE id=?")->execute([$id]); flash('Producto desactivado.');header('Location: productos.php');exit; }
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)($_POST['id']??0);$nombre=trim($_POST['nombre']??'');$codigo=trim($_POST['codigo']??'');$precio=(float)($_POST['precio']??0);$stock=max(0,(int)($_POST['stock']??0));$img=null;
    if($nombre===''||$precio<0){flash('Completa los datos correctamente.','error');header('Location: productos.php');exit;}
    if(!empty($_FILES['imagen']['name'])) {
        $ext=strtolower(pathinfo($_FILES['imagen']['name'],PATHINFO_EXTENSION));
        if(!in_array($ext,['jpg','jpeg','png','webp'])){flash('Imagen no válida. Usa JPG, PNG o WEBP.','error');header('Location: productos.php');exit;}
        $img=bin2hex(random_bytes(8)).'.'.$ext;move_uploaded_file($_FILES['imagen']['tmp_name'],__DIR__.'/uploads/'.$img);
    }
    $pdo=db();
    if($id){$sql="UPDATE productos SET cod_producto=?,nomb_producto=?,precio=?".($img?",imagen=?":"")." WHERE id=?";$args=[$codigo?:null,$nombre,$precio];if($img)$args[]=$img;$args[]=$id;$pdo->prepare($sql)->execute($args);$pdo->prepare("UPDATE inventario SET cantidad=? WHERE producto_id=?")->execute([$stock,$id]);}
    else {$pdo->prepare("INSERT INTO productos(cod_producto,nomb_producto,precio,imagen) VALUES(?,?,?,?)")->execute([$codigo?:null,$nombre,$precio,$img]);$pid=(int)$pdo->lastInsertId();$pdo->prepare("INSERT INTO inventario(producto_id,cantidad,fec_ingreso_producto) VALUES(?,?,CURDATE())")->execute([$pid,$stock]);}
    flash('Producto guardado correctamente.');header('Location: productos.php');exit;
}
$page_title='Productos';include 'header.php';
$edit=null;if(isset($_GET['edit'])){$s=db()->prepare("SELECT p.*,i.cantidad FROM productos p LEFT JOIN inventario i ON i.producto_id=p.id WHERE p.id=?");$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$rows=db()->query("SELECT p.*,i.cantidad FROM productos p LEFT JOIN inventario i ON i.producto_id=p.id WHERE p.activo=1 ORDER BY p.id DESC")->fetchAll();
?>
<h1>Administrar productos</h1><div class="form-card"><h2><?=$edit?'Editar':'Registrar'?> producto</h2><form method="post" enctype="multipart/form-data" class="form-grid">
<input type="hidden" name="id" value="<?=h($edit['id']??'')?>"><label>Código<input name="codigo" value="<?=h($edit['cod_producto']??'')?>"></label><label>Nombre del producto<input name="nombre" required value="<?=h($edit['nomb_producto']??'')?>"></label><label>Precio (Bs)<input type="number" step="0.01" min="0" name="precio" required value="<?=h($edit['precio']??'')?>"></label><label>Stock inicial / actual<input type="number" min="0" name="stock" required value="<?=h($edit['cantidad']??0)?>"></label><label>Imagen (JPG, PNG o WEBP)<input type="file" name="imagen" accept=".jpg,.jpeg,.png,.webp"></label><button class="btn primary"><?=$edit?'Guardar cambios':'Registrar producto'?></button><?php if($edit): ?><a class="btn secondary" href="productos.php">Cancelar</a><?php endif; ?></form></div>
<h2>Productos registrados</h2><div class="table-wrap"><table><tr><th>Imagen</th><th>Código</th><th>Producto</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
<?php foreach($rows as $p): ?><tr><td><?php if($p['imagen']): ?><img class="thumb" src="uploads/<?=h($p['imagen'])?>"><?php else: ?>📦<?php endif; ?></td><td><?=h($p['cod_producto'])?></td><td><?=h($p['nomb_producto'])?></td><td><?=money($p['precio'])?></td><td><?= (int)$p['cantidad']?></td><td><a href="?edit=<?=$p['id']?>">Editar</a> · <a class="danger-link" href="?del=<?=$p['id']?>" onclick="return confirm('¿Desactivar producto?')">Desactivar</a></td></tr><?php endforeach; ?>
</table></div><?php include 'footer.php'; ?>
