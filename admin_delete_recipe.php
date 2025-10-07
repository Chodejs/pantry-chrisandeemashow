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

// Ensure the request is POST and the recipe_id is set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_id'])) {
    $recipe_id = $_POST['recipe_id'];

    try {
        // First, get the image URL from the database before deleting the record
        $stmt_select = $pdo->prepare("SELECT image_url FROM recipes WHERE id = ?");
        $stmt_select->execute([$recipe_id]);
        $recipe = $stmt_select->fetch();

        // Now, delete the recipe record from the database
        $stmt_delete = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
        $stmt_delete->execute([$recipe_id]);

        // If the recipe was deleted and had an image, delete the image file
        if ($stmt_delete->rowCount() > 0 && $recipe && !empty($recipe['image_url'])) {
            // Basic security check to prevent deleting files outside the uploads folder
            if (strpos($recipe['image_url'], 'uploads/recipes/') === 0 && file_exists($recipe['image_url'])) {
                unlink($recipe['image_url']);
            }
        }
        
        header("location: admin_manage_recipes.php?status=deleted");
        ob_end_flush();
        exit();

    } catch (PDOException $e) {
        // In case of a database error, redirect with an error status
        error_log("Recipe deletion failed: " . $e->getMessage());
        header("location: admin_manage_recipes.php?status=error");
        ob_end_flush();
        exit();
    }
} else {
    // If the request is invalid, just redirect back
    header("location: admin_manage_recipes.php");
    ob_end_flush();
    exit;
}
?>
