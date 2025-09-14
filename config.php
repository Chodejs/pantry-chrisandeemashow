<?php
// --- SESSION MANAGEMENT ---
// By starting the session in the config file, we ensure it's active on every page.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- LIVE SERVER CREDENTIALS ---
// This block is for the live server.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'mysql'; // Braces are part of the password
$db_name = 'pantry_ce_show_live';
$environment = 'Live Server';

/*
// --- LOCAL DEVELOPMENT CREDENTIALS ---
// Comment out the live block and uncomment this one for local work.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'mysql'; 
$db_name = 'pantry_ce_show';
$environment = 'Local Development';
*/

// --- DATABASE CONNECTION (PDO) ---
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // On a live site, you would typically log this error and show a generic message.
    // For debugging, it is better to see the error.
    die("Database Connection Failed: " . $e->getMessage());
}
?>

