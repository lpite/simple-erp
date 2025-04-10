<?php
require_once __DIR__."/db.php";
require_once __DIR__."/../catalogs/product.php";
require_once __DIR__."/../accumulationRegisters/productStock.php";


function unpostDocument($type, $id) {
    $pdo = db();

    $pdo->prepare("
        DELETE FROM reg_accum_stock
        WHERE source_document_type = ? AND source_document_id = ?
    ")->execute([$type, $id]);

    // 2. Unmark the document as posted
    if ($type === 'sales') {
        $pdo->prepare("UPDATE document_sales SET posted = FALSE WHERE id = ?")->execute([$id]);
    } elseif ($type === 'purchase') {
        $pdo->prepare("UPDATE document_purchase SET posted = FALSE WHERE id = ?")->execute([$id]);
    } else {
        throw new Exception("Unknown document type: $type");
    }
}


function postDocument($type, $id) {
    $pdo = db();

    unpostDocument($type, $id);

    if ($type === 'sales') {
        $items = $pdo->prepare("SELECT product_id, quantity FROM document_sales_item WHERE document_id = ?");
        $items->execute([$id]);
        $items = $items->fetchAll(PDO::FETCH_ASSOC);

        // Check stock availability
        foreach ($items as $item) {
            $stock = getProductStock($item['product_id']);
            if ($stock < $item['quantity']) {
                throw new Exception("Not enough stock for product ID {$item['product_id']}");
            }
        }

        // Post stock deduction
        $stmt = $pdo->prepare("
            INSERT INTO reg_accum_stock (product_id, quantity, source_document_type, source_document_id)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($items as $item) {
            $stmt->execute([$item['product_id'], -$item['quantity'], $type, $id]);
        }

        // Mark document as posted
        $pdo->prepare("UPDATE document_sales SET posted = TRUE WHERE id = ?")->execute([$id]);

    } elseif ($type === 'purchase') {
        $items = $pdo->prepare("SELECT product_id, quantity FROM document_purchase_item WHERE document_id = ?");
        $items->execute([$id]);
        $items = $items->fetchAll(PDO::FETCH_ASSOC);

        // Post stock addition
        $stmt = $pdo->prepare("
            INSERT INTO reg_accum_stock (product_id, quantity, source_document_type, source_document_id)
            VALUES (?, ?, ?, ?)
        ");
        foreach ($items as $item) {
            $stmt->execute([$item['product_id'], $item['quantity'], $type, $id]);
        }

        // Mark document as posted
        $pdo->prepare("UPDATE document_purchase SET posted = TRUE WHERE id = ?")->execute([$id]);
    } else {
        throw new Exception("Unknown document type: $type");
    }
}

function isDocumentPosted($type, $id) {
    $stmt = db()->prepare("
        SELECT posted FROM document_{$type} WHERE id = ?
    ");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

