<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database credentials
$host = 'db';                // Hostname of the MySQL container in Docker
$db = 'ecommerce';           // Name of the database
$user = 'user';              // MySQL username
$pass = 'userpass';          // MySQL password
$charset = 'utf8mb4';        // Character set for full Unicode support

// Data Source Name: defines how to connect to the DB
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for better error handling and security
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,          // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false,                  // Use real prepared statements
];

try {
    // Create a new PDO instance and assign it to $pdo
    $pdo = new PDO($dsn, $user, $pass, $options);

} catch (PDOException $e) {
    // If the connection fails, stop the script and print error
    die('Database connection failed: ' . $e->getMessage());
}
