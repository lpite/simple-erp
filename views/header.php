<?php 
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ERROR | E_PARSE | E_NOTICE);

 ?>
<head>
	    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
</head>
<header class="mb-4 container">
	<a href="/erp/index.php">main</a>
	<a href="/erp/products.php">products</a>
	<a href="/erp/purchases.php">purchases</a>
	<a href="/erp/sales.php">sales</a>
	<a href="/erp/prices.php">prices</a>
</header>