<?php 
require './documents/salesDocument.php';
require './documents/purchaseDocument.php';

require './views/header.php';
$document = [];
switch ($_GET['type']) {
	case 'sales':
		$document = getSalesDocumentWithItems($_GET['id']);
		break;
	case 'purchases':
		$document =  getPurchaseDocumentWithItems($_GET['id']);
		break;
	default:
		echo "meow";
		break;
}
if(!isset($document)){
	$document["id"] = "-";
	$document["type"] = $_GET['type'];
	$document["date"] = date("Y-m-d h:m:s");
	$document["items"] = [];
	$_SESSION['document'] = $document;
}else{
	unset($_SESSION['document']);
}
?>
<main class="mx-2">
	<form method="post">
		<button class="btn btn-success">Зберегти</button>
	</form>
	<table>
		<tr>
			<td>Номер</td>
			<td>
				<?php  echo $document["id"]; ?>
			</td>
		</tr>
		<tr>
			<td>дата</td>
			<td>
				<?php  echo $document["date"]; ?>
			</td>
		</tr>
	</table>
	<a href="/erp/addProductToDocument.php?docId=<?php echo $_GET['id']; ?>&type=<?php echo $_GET['type']; ?>"
		class="btn btn-success">+</a>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>id</th>
				<th>name</th>
				<th>quanity</th>
				<th>price</th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ($_SESSION['newItems_'.$_GET['id'].'_'.$_GET['type']] as $key => $value) {
		 ?>
			<tr>
				<td>
					<?php echo $value['product_id']; ?>
				</td>
				<td>
					<?php echo $value['product_name']; ?>
				</td>
				<td>
					<?php echo $value['quantity']; ?>
				</td>
				<td>
					<?php echo $value['price']; ?>
				</td>
			</tr>
			<?php

			 } 
		 ?>
			<?php 
		foreach ($document["items"] as $key => $value) {
			?>
			<tr>
				<td>
					<?php echo $value['product_id']; ?>
				</td>
				<td>
					<?php echo $value['product_name']; ?>
				</td>
				<td>
					<?php echo $value['quantity']; ?>
				</td>
				<td>
					<?php echo $value['price']; ?>
				</td>
			</tr>
			<?php 
		 } ?>
		</tbody>
	</table>
</main>