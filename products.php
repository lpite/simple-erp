<?php 
require './catalogs/product.php';
require './accumulationRegisters/productStock.php';

require './views/header.php';

 ?>
<div class="container">
	<a class="btn btn-success" href="product.php">Створити</a>
	<table class="table table-striped">
		<thead>
			<tr>
				<th>code</th>
				<th>name</th>
				<th>stock</th>
			</tr>
		</thead>
		<tbody>
			<?php

foreach (getAllProducts() as $key => $value) {
	?>
			<tr>
				<td>
					<a href="/erp/product.php?id=<?php echo $value['id'] ?>">
						<?php echo $value['code']; ?>
					</a>
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
</div>