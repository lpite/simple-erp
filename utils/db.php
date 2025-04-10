<?php
function db() {
    return new PDO('pgsql:host=localhost;dbname=erp', 'postgres', '');
}

