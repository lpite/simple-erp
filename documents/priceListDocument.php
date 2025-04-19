<?php
require_once __DIR__.'/../utils/db.php';

function postPriceListDocument($docId) {
    $pdo = db();

    // Check if already posted
    $isPosted = $pdo->prepare("SELECT posted FROM document_price_list WHERE id = ?");
    $isPosted->execute([$docId]);
    if (!$isPosted->fetchColumn()) {
        // Unpost (just in case it was previously posted and re-edited)
        unpostDocument('price_list', $docId);

        // Get items
        $items = $pdo->prepare("
            SELECT product_id, price
            FROM document_price_list_item
            WHERE document_id = ?
        ");
        $items->execute([$docId]);
        $rows = $items->fetchAll(PDO::FETCH_ASSOC);

        // Insert into reg_info_price
        $insert = $pdo->prepare("
            INSERT INTO reg_info_price (product_id, price, source_document_type, source_document_id)
            VALUES (?, ?, 'price_list', ?)
        ");

        foreach ($rows as $item) {
            $insert->execute([$item['product_id'], $item['price'], $docId]);
        }

        $pdo->prepare("UPDATE document_price_list SET posted = TRUE WHERE id = ?")->execute([$docId]);
    }
}

function getAllPriceListDocuments() {
    $pdo = db();

    $docsStmt = $pdo->query("
        SELECT id, date, posted
        FROM document_price_list
        ORDER BY date DESC
    ");
    $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($documents as &$doc) {
        $itemStmt = $pdo->prepare("
            SELECT dpli.product_id, p.name AS product_name, dpli.price
            FROM document_price_list_item dpli
            JOIN catalog_product p ON p.id = dpli.product_id
            WHERE dpli.document_id = ?
        ");
        $itemStmt->execute([$doc['id']]);
        $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $documents;
}

function getPriceListDocumentById($id) {
    $pdo = db();

    $docStmt = $pdo->prepare("
        SELECT id, date, posted
        FROM document_price_list
        WHERE id = ?
    ");
    $docStmt->execute([$id]);
    $doc = $docStmt->fetch(PDO::FETCH_ASSOC);

    if (!$doc) {
        return null;
    }

    $itemStmt = $pdo->prepare("
        SELECT dpli.product_id, p.name AS product_name, dpli.price
        FROM document_price_list_item dpli
        JOIN catalog_product p ON p.id = dpli.product_id
        WHERE dpli.document_id = ?
    ");
    $itemStmt->execute([$id]);
    $doc['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    return $doc;
}


