<?php 
require './views/header.php';
require './documents/purchaseDocument.php';

 ?>

<a href="/erp/document.php" class="btn btn-success">Новий</a>
<table class="table table-striped">
	<thead>
		<tr>
			<th>id</th>
			<th>date</th>
			<th>posted</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach (getAllPurchaseDocuments() as $key => $value) {
			?>
		<tr>
			<td>
				<a href="document.php?id=<?php echo $value['id']; ?>&type=purchases">
					<?php echo $value['id']; ?>
				</a>
			</td>
			<td>
				<?php echo $value['date']; ?>
			</td>
			<td>
				<?php echo $value['posted'] ? "yes" :"no"; ?>
			</td>
			<td>
				<form method="post" action="api/document/post.php" class="m-0">
					<input type="hidden" name="docType" value="purchase">
					<input type="hidden" name="docId" value="<?php echo $value['id']; ?>">
					<button>post</button>
				</form>
			</td>
			<td>
				<form method="post" action="api/document/unpost.php" class="m-0">
					<input type="hidden" name="docType" value="purchase">
					<input type="hidden" name="docId" value="<?php echo $value['id']; ?>">
					<button>unpost</button>
				</form>
			</td>

		</tr>
		<?php
		} ?>
	</tbody>
</table>