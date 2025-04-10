<?php
require_once __DIR__.'/../catalogs/product.php';
addProduct($_POST['code'],$_POST['name']);
header("Location: /erp/products.php");