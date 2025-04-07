<?php 
include_once 'document.php';

function createPurchaseDocument($items) {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Create purchase document
    $pdo->exec("INSERT INTO document_purchase DEFAULT VALUES");
    $docId = $pdo->lastInsertId();

    // 2. Insert line items
    $stmt = $pdo->prepare("
        INSERT INTO document_purchase_item (document_id, product_id, quantity)
        VALUES (?, ?, ?)
    ");

    foreach ($items as $item) {
        $stmt->execute([$docId, $item['product_id'], $item['quantity']]);
    }

    $pdo->commit();
    return $docId;
}


function postPurchaseDocument($docId) {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Check if the document is already posted
    $stmt = $pdo->prepare("SELECT posted FROM document_purchase WHERE id = ?");
    $stmt->execute([$docId]);
    $posted = $stmt->fetchColumn();

    // 2. If posted — unpost it (delete old reg entries)
    if ($posted) {
        // $stmt = $pdo->prepare("DELETE FROM reg_accum_stock WHERE source_document_id = ?");
        // $stmt->execute([$docId]);
        unpostDocument('purchase',$docId);

    }

    // 3. Get items again (in case they changed)
    $stmt = $pdo->prepare("SELECT * FROM document_purchase_item WHERE document_id = ?");
    $stmt->execute([$docId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Insert new register entries (positive quantity = stock IN)
    $stmt = $pdo->prepare("
        INSERT INTO reg_accum_stock (product_id, quantity, source_document_id,source_document_type)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $stmt->execute([$item['product_id'], $item['quantity'], $docId,'purchase']);
    }

    // 5. Mark document as posted
    $stmt = $pdo->prepare("UPDATE document_purchase SET posted = TRUE WHERE id = ?");
    $stmt->execute([$docId]);

    $pdo->commit();
}