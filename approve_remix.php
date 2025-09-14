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
        // Prepare and execute the update statement using PDO
        $sql = "UPDATE remixes SET is_approved = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$remix_id]);

        // Redirect back to the moderation page upon success
        header("location: admin_remixes.php?status=approved");
        ob_end_flush(); // Send buffer before exiting
        exit();

    } catch (PDOException $e) {
        // In case of a database error, stop and show the error for debugging.
        // On a live site, you might log this and show a friendly error page.
        die("Database error during remix approval: " . $e->getMessage());
    }
} else {
    // If the request is invalid (e.g., accessed directly via URL), redirect back.
    header("location: admin_remixes.php");
    ob_end_flush(); // Send buffer before exiting
    exit;
}
?>

