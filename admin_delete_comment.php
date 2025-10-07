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
        // First, get the image URL from the database before deleting the record
        $stmt_select = $pdo->prepare("SELECT image_url FROM comments WHERE id = ?");
        $stmt_select->execute([$comment_id]);
        $comment = $stmt_select->fetch();

        // Now, delete the comment record from the database
        $stmt_delete = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt_delete->execute([$comment_id]);

        // If the comment was deleted and had an image, delete the image file
        if ($stmt_delete->rowCount() > 0 && $comment && !empty($comment['image_url'])) {
            if (file_exists($comment['image_url'])) {
                unlink($comment['image_url']);
            }
        }
        
        header("location: admin_comment_moderation.php?status=deleted");
        ob_end_flush();
        exit();

    } catch (PDOException $e) {
        error_log("Comment deletion failed: " . $e->getMessage());
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
