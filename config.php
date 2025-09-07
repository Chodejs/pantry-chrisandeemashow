<?php
/*
 * Database Configuration for The Plant-Powered Pantry
 * Establishes a connection to the MySQL database.
 * Includes settings for both local and live environments.
 */

// --- ENVIRONMENT SETUP ---
// Uncomment the appropriate block for your environment.

// --- LOCAL DEVELOPMENT CREDENTIALS ---
// This block is active for local machine development.
$db_host = 'localhost';
$db_user = 'root';
$db_pass = 'mysql'; // Your local MySQL root password
$db_name = 'pantry_ce_show'; // Database for this specific project
$environment = 'Local Development';


/*
// --- LIVE SERVER CREDENTIALS ---
// Uncomment this block when deploying to the live server.
// Ensure the local development block above is commented out.
$db_host = 'mysql.christow.blog';
$db_user = 'architect11';
$db_pass = '{ReowReow11}'; // Braces are part of the password
$db_name = 'pantry_ce_show'; // IMPORTANT: Ensure this matches the live database name
$environment = 'Live Server';
*/


// --- Attempt to Connect ---
$link = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// --- Check Connection ---
if($link === false){
    // If connection fails, stop everything and display an error.
    // In a live production environment, we'd handle this more gracefully.
    die("ERROR: Could not connect to the '$environment' database. " . mysqli_connect_error());
}

// Set the character set to utf8mb4 for full Unicode support
mysqli_set_charset($link, "utf8mb4");

?>

