<?php
ob_start();
session_start();
require_once "config.php";

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("location: login.php");
    ob_end_flush();
    exit;
}

// Ensure the request is POST and the comment_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_id'])) {
    $comment_id = $_POST['comment_id'];

    try {
        $sql = "UPDATE comments SET is_approved = 1 WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$comment_id]);

        header("location: admin_comment_moderation.php?status=approved");
        ob_end_flush();
        exit();

    } catch (PDOException $e) {
        error_log("Comment approval failed: " . $e->getMessage());
        header("location: admin_comment_moderation.php?status=error");
        ob_end_flush();
        exit();
    }
} else {
    // If the request is invalid, redirect back
    header("location: admin_comment_moderation.php");
    ob_end_flush();
    exit;
}
?>
