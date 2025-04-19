<?php 
require './views/header.php';

require './catalogs/product.php';
require './accumulationRegisters/productStock.php';

$products = getAllProducts(['name'=>$_GET['q'] ?? '','code'=>$_GET['q'] ?? '']);

if ($_GET['docId']) {
	echo "found";
}else{
	echo "not found";
}


if(isset($_GET['q'])){
	echo $_GET['q'];
}
if (isset($_POST["newProduct"])) {
	$itemsList = $_SESSION['newItems'] ?? [];
	$product = getProductById($_POST["newProduct"]);
	array_push($itemsList,['product_id'=>$_POST["newProduct"],'product_code'=>$product['code'],'product_name'=>$product['name'],'quantity'=>1]);
	$_SESSION['newItems'] = $itemsList;
	exit;
}

 ?>
<header>
	<a href="/erp/document.php?id=<?php echo $_GET['docId'] ?>&type=<?php echo $_GET['type'] ?>">
		Назад
	</a>
	<form class="d-flex w-25 mx-3">
		<input class="form-control" type="text" name="q">
		<button class="btn btn-primary mx-1">
			Шукати
		</button>
	</form>
</header>
<main>
	<table class="table">
		<thead>
			<tr>
				<th>code</th>
				<th>name</th>
				<th>stock</th>
			</tr>
		</thead>
		<tbody>
			<?php
foreach ($products as $key => $value) {
	?>
			<tr class="product_row" data-id="<?php echo $value['id']; ?>">
				<td>
					<?php echo $value['code']; ?>
				</td>
				<td>
					<?php echo $value['name']; ?>
				</td>
				<td>
					<?php echo getProductStock($value['id']); ?>
				</td>
			</tr>
			<?php
}
?>
		</tbody>
	</table>
	<script>
		const rows = document.querySelectorAll(".product_row");
		rows.forEach((row) => {
			row.addEventListener("click", (e) => {
				const activeRow = document.querySelector(".table-active");
				if (activeRow) {
					activeRow.classList.remove("table-active");
				}
				e.target.parentElement.classList.add("table-active")
			})
			row.addEventListener("dblclick", (e) => {
				const formData = new FormData();
				formData.append("newProduct", e.target.parentElement.dataset.id);
				fetch("", {
					method: "POST",
					body: formData
				})
				console.log("meow");
			})
		})
	</script>
	<style>

	</style>
</main>