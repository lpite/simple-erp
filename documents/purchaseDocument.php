<?php 
require_once __DIR__.'/../utils/db.php';
require_once __DIR__.'/../utils/document.php';

function createPurchaseDocument($items) {
    $pdo = db();
    $pdo->beginTransaction();

    // 1. Create purchase document
    $pdo->exec("INSERT INTO document_purchase DEFAULT VALUES");
    $docId = $pdo->lastInsertId();

    // 2. Insert line items
    $stmt = $pdo->prepare("
        INSERT INTO document_purchase_item (document_id, product_id, quantity,price)
        VALUES (?, ?, ?,?)
    ");

    foreach ($items as $item) {
        $stmt->execute([$docId, $item['product_id'], $item['quantity']],$item['price']);
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


function getAllPurchaseDocuments() {
    $stmt = db()->query("
        SELECT id, date, posted
        FROM document_purchase
        ORDER BY date DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllPurchaseDocumentsWithItems() {
    $pdo = db();

    $docsStmt = $pdo->query("
        SELECT id, date, posted
        FROM document_purchase
        ORDER BY date DESC
    ");
    $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($documents as &$doc) {
        $itemStmt = $pdo->prepare("
            SELECT dpi.product_id, p.name AS product_name, dpi.quantity
            FROM document_purchase_item dpi
            JOIN catalog_product p ON p.id = dpi.product_id
            WHERE dpi.document_id = ?
        ");
        $itemStmt->execute([$doc['id']]);
        $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $documents;
}

function getPurchaseDocumentWithItems($id) {
    $pdo = db();

    // Get the main document
    $docStmt = $pdo->prepare("
        SELECT id, date, posted
        FROM document_purchase
        WHERE id = ?
    ");
    $docStmt->execute([$id]);
    $doc = $docStmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        return null; // Document not found
    }

    // Get items
    $itemStmt = $pdo->prepare("
        SELECT dpi.product_id, p.name AS product_name, dpi.quantity, dpi.price
        FROM document_purchase_item dpi
        JOIN catalog_product p ON p.id = dpi.product_id
        WHERE dpi.document_id = ?
    ");
    $itemStmt->execute([$id]);
    $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $doc;
}

function addPurchaseItem($documentId, $productId, $quantity, $price) {
    $stmt = db()->prepare("
        INSERT INTO document_purchase_item (document_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([$documentId, $productId, $quantity, $price]);
}


