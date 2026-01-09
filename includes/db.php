<?php
// includes/db.php
// Database configuration and connection using PDO

// Configuration Variables
// In a production environment, these should be in an env file outside the web root
$host = 'localhost';
$db   = 'merobill_db';
$user = 'root';
$pass = ''; // Default XAMPP password
$charset = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO Options for better error handling and security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,      // Returning arrays indexed by column name
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Use real prepared statements
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In a real app, log this error instead of showing it to the user
    // For development, we show the message to debug easily
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
