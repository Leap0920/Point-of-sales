<?php
// Basic configuration for the POS system
// Adjust these values to match your XAMPP/MySQL setup.

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'pos_db',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'base_url' => 'http://localhost/Point-of-sales/public',
        'name' => 'Restaurant POS',
        'env' => 'development', // development | production
    ],
];


