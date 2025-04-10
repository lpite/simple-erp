<?php 
require_once __DIR__.'/../../utils/document.php';
if (isset($_POST['docId']) && isset($_POST['docType'])) {
	try {
		postDocument($_POST['docType'],$_POST['docId']);
		header("Location: ".$_SERVER['HTTP_REFERER']);
		
	} catch (Exception $e) {
		?>
			<a href="" onclick="history.back()">return back</a>
		<?php
		echo $e->getMessage();
		// var_dump($e);
	}
}