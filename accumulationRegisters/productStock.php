<?php 
function getProductStock($productId) {
    $stmt = db()->prepare("SELECT SUM(quantity) FROM reg_accum_stock WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetchColumn();
}