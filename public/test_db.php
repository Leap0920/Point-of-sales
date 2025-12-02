<?php

require __DIR__ . '/../app/Database.php';

try {
    $pdo = Database::getConnection();
    echo 'Database connection OK. Database name: ' . $pdo->query('SELECT DATABASE()')->fetchColumn();
} catch (Throwable $e) {
    echo 'Database connection failed: ' . $e->getMessage();
}


