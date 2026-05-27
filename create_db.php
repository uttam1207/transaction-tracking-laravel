<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    $pdo->exec('CREATE DATABASE IF NOT EXISTS transaction_monitor CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "Database 'transaction_monitor' created successfully!\n";
} catch(Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Please create the database manually: CREATE DATABASE transaction_monitor;\n";
}
