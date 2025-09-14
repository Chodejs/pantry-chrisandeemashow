<?php
// --- FORCE ERROR DISPLAY FOR DEBUGGING ---
// This is the most important part. We start with a clean slate.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Test</h1>";
echo "<p>Attempting to connect to the database...</p><hr>";

// --- Hardcoded Live Server Credentials ---
$db_host = 'mysql.christow.blog';
$db_user = 'architect11';
$db_pass = '{ReowReow11}'; 
$db_name = 'pantry_ce_show';

// --- DATABASE CONNECTION (PDO) ---
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    echo "<p style='color:green; font-weight:bold;'>SUCCESS: Connection to the database was successful!</p>";

} catch (\PDOException $e) {
    echo "<p style='color:red; font-weight:bold;'>ERROR: Connection failed.</p>";
    echo "<p><strong>Error Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p>Please double-check your database host, name, username, and password.</p>";
}
?>
