<?php 
include 'product.php';
include 'salesDocument.php';
include 'purchaseDocument.php';


// addProduct("meow","meow");
createSalesDocument([["product_id"=>1,"quantity"=>100]]);

// createPurchaseDocument([["product_id"=>1,"quantity"=>10]]);
try {
	postPurchaseDocument(2);
	postSalesDocument(1);
	postSalesDocument(2);
	postSalesDocument(3);
	postSalesDocument(4);
	postSalesDocument(5);
	postSalesDocument(6);
	postSalesDocument(7);
	postSalesDocument(8);
	postSalesDocument(53);


	
} catch (Exception $e) {
	var_dump($e);
	
}
    $pdo = db();
    $stmt = $pdo->prepare("
            SELECT quantity
            FROM reg_accum_stock
            WHERE product_id = ?
        ");
     $meow=   $stmt->execute([1]);
     // var_dump($stmt->fetchAll());
echo "stock = ".getProductStock(1);