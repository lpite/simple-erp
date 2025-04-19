<?php
require_once __DIR__."/db.php";

require_once __DIR__."/../catalogs/product.php";

require_once __DIR__."/../documents/salesDocument.php";
require_once __DIR__."/../documents/purchaseDocument.php";

require_once __DIR__."/../accumulationRegisters/productStock.php";


function unpostDocument($type, $id) {
    $pdo = db();

    $config = [
        'sales' => [
            'register_table' => 'reg_accum_stock',
            'document_table' => 'document_sales',
        ],
        'purchase' => [
            'register_table' => 'reg_accum_stock',
            'document_table' => 'document_purchase',
        ],
        'price_list' => [
            'register_table' => 'reg_info_price',
            'document_table' => 'document_price_list',
        ],
    ];

    if (!isset($config[$type])) {
        throw new Exception("Unknown document type: $type");
    }

    $register = $config[$type]['register_table'];
    $docTable = $config[$type]['document_table'];

    $pdo->prepare("
        DELETE FROM {$register}
        WHERE source_document_type = ? AND source_document_id = ?
    ")->execute([$type, $id]);

    $pdo->prepare("UPDATE {$docTable} SET posted = FALSE WHERE id = ?")->execute([$id]);
}


function postDocument($type, $id) {
    $pdo = db();

    unpostDocument($type, $id);
    switch ($type) {
        case 'sales':
            postSalesDocument($id);
            break;
        case 'purchase':
           postPurchaseDocument($id);
            break;
        default:
            throw new Exception("Unknown document type: $type");
            break;
    }
}

function isDocumentPosted($type, $id) {
    $stmt = db()->prepare("
        SELECT posted FROM document_{$type} WHERE id = ?
    ");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

function addItemToDocument($type,$id,$item){
    switch ($type) {
        case 'sales':
            addSalesItem($id,$item["product_id"],1,1);
            break;
        case 'purchase':
            addPurchaseItem($id,$item["product_id"],1,1);
            break;
        default:
            throw new Exception("Unknown document type: $type");
            break;
    }
}