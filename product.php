<?php 
require 'db.php';

function addProduct($code, $name) {
    $pdo = db();

    // Check if the code already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM catalog_product WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetchColumn() > 0) {
        return [
            'success' => false,
            'error' => "Product with code '$code' already exists."
        ];
    }

    // Insert new product
    $stmt = $pdo->prepare("INSERT INTO catalog_product (code, name) VALUES (?, ?)");
    $success = $stmt->execute([$code, $name]);

    return [
        'success' => $success,
        'id' => $pdo->lastInsertId()
    ];
}


function getAllProducts() {
    return db()->query("SELECT * FROM catalog_product")->fetchAll(PDO::FETCH_ASSOC);
}

function getProductStock($productId) {
    $stmt = db()->prepare("SELECT SUM(quantity) FROM reg_accum_stock WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetchColumn();
}
