<?php
function unpostDocument($type, $id) {
    db()->prepare("
        DELETE FROM reg_accum_stock
        WHERE source_document_type = ? AND source_document_id = ?
    ")->execute([$type, $id]);
}