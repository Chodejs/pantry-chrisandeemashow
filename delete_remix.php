<?php
ob_start(); // Start output buffering as the very first thing.
session_start();
require_once "config.php"; // Use the config file with the $pdo connection

// Check if the user is logged in and is an admin, otherwise redirect
if (!isset($_SESSION["user_id"]) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("location: login.php");
    ob_end_flush(); // Send buffer before exiting
    exit;
}

// Ensure the form was submitted via POST and the remix_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remix_id'])) {
    $remix_id = $_POST['remix_id'];

    try {
        // Prepare and execute the delete statement using PDO
        $sql = "DELETE FROM remixes WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$remix_id]);

        // Redirect back to the moderation page upon success
        header("location: admin_remixes.php?status=deleted");
        ob_end_flush(); // Send buffer before exiting
        exit();

    } catch (PDOException $e) {
        // In case of a database error, stop and show the error for debugging.
        die("Database error during remix deletion: " . $e->getMessage());
    }
} else {
    // If the request is invalid, redirect back.
    header("location: admin_remixes.php");
    ob_end_flush(); // Send buffer before exiting
    exit;
}
?>

