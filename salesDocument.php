<?php 
include_once 'document.php';
function createSalesDocument($items) {
    $pdo = db();
    $pdo->beginTransaction();
    
    $pdo->exec("INSERT INTO document_sales DEFAULT VALUES");
    $docId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO document_sales_item (document_id, product_id, quantity) VALUES (?, ?, ?)");

    foreach ($items as $item) {
        $stmt->execute([$docId, $item['product_id'], $item['quantity']]);
    }

    $pdo->commit();
    return $docId;
}

function postSalesDocument($docId) {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Check if already posted
    $stmt = $pdo->prepare("SELECT posted FROM document_sales WHERE id = ?");
    $stmt->execute([$docId]);
    $posted = $stmt->fetchColumn();

    // 2. If posted, unpost first (remove previous register records)
    if ($posted) {
        // $stmt = $pdo->prepare("DELETE FROM reg_accum_stock WHERE source_document_id = ?");
        // $stmt->execute([$docId]);
        unpostDocument('sales',$docId);
    }

    // 3. Get sales items
    $stmt = $pdo->prepare("SELECT * FROM document_sales_item WHERE document_id = ?");
    $stmt->execute([$docId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Check stock levels for each item
    foreach ($items as $item) {
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(quantity), 0) AS balance
            FROM reg_accum_stock
            WHERE product_id = ?
        ");
        $stmt->execute([$item['product_id']]);
        $balance = $stmt->fetchColumn();

        if ($balance < $item['quantity']) {
            $pdo->rollBack();
            throw new Exception("Not enough stock for product ID {$item['product_id']} (available: $balance, needed: {$item['quantity']})");
        }
    }

    // 5. Post: record outflow
    $stmt = $pdo->prepare("INSERT INTO reg_accum_stock (product_id, quantity, source_document_id,source_document_type) VALUES (?, ?, ?,?)");
    foreach ($items as $item) {
        $stmt->execute([
            $item['product_id'],
            -$item['quantity'],
            $docId,
            'sales'
        ]);
    }

    // 6. Mark as posted
    $stmt = $pdo->prepare("UPDATE document_sales SET posted = TRUE WHERE id = ?");
    $stmt->execute([$docId]);

    $pdo->commit();
}
