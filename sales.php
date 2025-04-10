<?php 
	require './documents/salesDocument.php';

	require './views/header.php';

?>

<table>
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
		<?php foreach (getAllSalesDocuments() as $key => $value) {
			?>
		<tr>
			<td>
				<a href="document.php?id=<?php echo $value['id']; ?>&type=sales">
					<?php echo $value['id']; ?>
				</a>
			</td>
			<td>
				<?php echo $value['date']; ?>
			</td>
			<td>
				<?php echo $value['posted']; ?>
			</td>
			<td>
				<form method="post" action="api/document/post.php">
					<input type="hidden" name="docType" value="sales">
					<input type="hidden" name="docId" value="<?php echo $value['id']; ?>">
					<button>post</button>
				</form>
			</td>
			<td>
				<form method="post" action="api/document/unpost.php">
					<input type="hidden" name="docType" value="sales">
					<input type="hidden" name="docId" value="<?php echo $value['id']; ?>">
					<button>unpost</button>
				</form>
			</td>
		</tr>
		<?php
		} ?>
	</tbody>
</table>