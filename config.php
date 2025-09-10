<?php
/**
 * Chris & Emma's Plant-Powered Pantry - Database Configuration
 * * This file contains the configuration for connecting to the database.
 * It supports switching between local development and a live server environment.
 * This version uses PDO (PHP Data Objects) for a more modern and secure connection.
 */

// --- ENVIRONMENT SETUP ---
// Define the current environment. Change to 'live' when deploying.
define('ENVIRONMENT', 'local'); 

// --- DATABASE CREDENTIALS ---
$db_host = '';
$db_user = '';
$db_pass = '';
$db_name = '';
$environment_name = '';

if (ENVIRONMENT === 'local') {
    // --- LOCAL DEVELOPMENT CREDENTIALS ---
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = 'mysql'; // Or your local MySQL root password
    $db_name = 'pantry_ce_show';
    $environment_name = 'Local Development';
} else {
    // --- LIVE SERVER CREDENTIALS ---
    $db_host = 'mysql.christow.blog';
    $db_user = 'architect11';
    $db_pass = '{ReowReow11}'; // Braces are part of the password
    $db_name = 'pantry_ce_show'; // You might want a specific pantry DB here
    $environment_name = 'Live Server';
}

// --- PDO CONNECTION ---
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
    // Create the PDO instance (our connection object)
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // If connection fails, stop everything and show a generic error message.
    // In a real production app, you would log this error and show a user-friendly page.
    throw new PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
}

