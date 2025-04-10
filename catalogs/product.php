<?php 
require_once __DIR__.'/../utils/db.php';
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

function updateProduct($id, $code, $name) {
    $stmt = db()->prepare("
        UPDATE catalog_product
        SET code = ?, name = ?
        WHERE id = ?
    ");
    return $stmt->execute([$code, $name, $id]);
}


function getAllProducts($filters = []) {
    $pdo = db();

    $where = [];
    $params = [];

    if (!empty($filters['id'])) {
        $where[] = "id = ?";
        $params[] = $filters['id'];
    }

    if (!empty($filters['code'])) {
        $where[] = "code = ?";
        $params[] = $filters['code'];
    }

    if (!empty($filters['name'])) {
        $where[] = "name ILIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    $sql = "SELECT * FROM catalog_product";
    if ($where) {
        $sql .= " WHERE " . implode(" OR ", $where);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($id) {
    $stmt = db()->prepare("SELECT * FROM catalog_product WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

