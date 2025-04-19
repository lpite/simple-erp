<?php 
require_once './documents/salesDocument.php';
require_once './documents/purchaseDocument.php';
require_once './utils/document.php';


require './views/header.php';
$document = [];
switch ($_GET['type']) {
	case 'sales':
		$document = getSalesDocumentWithItems($_GET['id']);
		break;
	case 'purchase':
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
$newItems = [];
if(str_contains($_SERVER['HTTP_REFERER'],'addProductToDocument')){
	$newItems = $_SESSION['newItems'] ?? [];
}

if(isset($_POST['save'])){
	$newItems = $_SESSION['newItems'] ?? [];
	if (count($newItems)) {
		foreach ($newItems as $key => $item) {
			addItemToDocument($_GET['type'],$_GET['id'],$item);
			// addPurchaseItem($_GET['id'],$item['product_id'],1,1);
		}
	}
	postDocument($_GET['type'],$_GET['id']);
	$_SESSION['newItems'] = [];
	header("Location: /erp/document.php?id={$_GET['id']}&type={$_GET['type']}");
	var_dump($_GET);
}


?>
<main class="mx-2">
	<form method="post">
		<input type="hidden" name="save">
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
		foreach (array_merge($document["items"],$newItems) as $key => $value) {
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