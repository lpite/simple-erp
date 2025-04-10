<?php 
require_once __DIR__.'/../../documents/purchaseDocument.php';

if(isset($_POST['docId'])){
	postPurchaseDocument($_POST['docId']);
}

header("Location:/erp/purchases.php");