<?php 
require_once __DIR__.'/../utils/db.php';
require_once __DIR__.'/../utils/document.php';

function createSalesDocument($items) {
    $pdo = db();
    $pdo->beginTransaction();
    
    $pdo->exec("INSERT INTO document_sales DEFAULT VALUES");
    $docId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO document_sales_item (document_id, product_id, quantity,price) VALUES (?, ?, ?,?)");

    foreach ($items as $item) {
        $stmt->execute([$docId, $item['product_id'], $item['quantity'],$item['price']]);
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

function getAllSalesDocuments() {
    $stmt = db()->query("
        SELECT id, date, posted
        FROM document_sales
        ORDER BY date DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllSalesDocumentsWithItems() {
    $pdo = db();

    $docsStmt = $pdo->query("
        SELECT id, date, posted
        FROM document_sales
        ORDER BY date DESC
    ");
    $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($documents as &$doc) {
        $itemStmt = $pdo->prepare("
            SELECT dsi.product_id, p.name AS product_name, dsi.quantity
            FROM document_sales_item dsi
            JOIN catalog_product p ON p.id = dsi.product_id
            WHERE dsi.document_id = ?
        ");
        $itemStmt->execute([$doc['id']]);
        $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $documents;
}

function getSalesDocumentWithItems($id) {
    $pdo = db();

    // Get the main document
    $docStmt = $pdo->prepare("
        SELECT id, date, posted
        FROM document_sales
        WHERE id = ?
    ");
    $docStmt->execute([$id]);
    $doc = $docStmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        return null; // Document not found
    }

    // Get items
    $itemStmt = $pdo->prepare("
        SELECT dsi.product_id, p.name AS product_name, dsi.quantity, dsi.price
        FROM document_sales_item dsi
        JOIN catalog_product p ON p.id = dsi.product_id
        WHERE dsi.document_id = ?
    ");
    $itemStmt->execute([$id]);
    $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $doc;
}

function addSalesItem($documentId, $productId, $quantity, $price) {
    $stmt = db()->prepare("
        INSERT INTO document_sales_item (document_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$documentId, $productId, $quantity, $price]);
}

