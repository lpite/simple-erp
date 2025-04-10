<?php 
require_once __DIR__.'/../../utils/document.php';

if (isset($_POST['docId']) && isset($_POST['docType'])) {
	unpostDocument($_POST['docType'],$_POST['docId']);
	header("Location: ".$_SERVER['HTTP_REFERER']);
}