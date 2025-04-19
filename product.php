<?php 
require './views/header.php';
require './catalogs/product.php';

if(isset($_GET['id']) && $_GET['id']!== ''){
	$product = getProductById($_GET['id']);
}else{
	$product = [];
}

if (isset($_POST['code']) && isset($_POST['code']) && $_POST['type'] === 'create') {
	$result = addProduct($_POST['code'],$_POST['name']);
	if(isset($result['id'])){
		header("Location: /erp/product.php"."?id=".$result['id']);
	}else{
		var_dump($result);
		// header("Location: /erp/product.php");
	}
}

if (
    isset($_POST['id']) &&
    isset($_POST['code']) &&
    isset($_POST['name']) &&
    $_POST['type'] === 'update'
) {
    updateProduct($_POST['id'], $_POST['code'], $_POST['name']);
	header("Location: /erp/product.php"."?id=".$_POST['id']);
}
?>


<form method="post" action="" class="w-25 m-3">
	<input type="hidden" name="id" value="<?php echo $_GET['id']; ?>">
	<div class="mb-3">
		<label class="form-label">Код</label>
		<input class="form-control" type="text" placeholder="code" name="code" required
			value="<?php echo $product['code'] ?>">
	</div>
	<div class="mb-3">
		<label class="form-label">Назва</label>
		<input type="text" class="form-control" placeholder="name" name="name" required
			value="<?php echo $product['name'] ?>">
	</div>
	<input class="btn btn-success" type="submit" name="type" value="<?php echo isset($_GET['id']) ? "update" : "create"
		?>">
</form>