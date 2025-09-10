<?php
session_start();
require_once 'config.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $user_id = $_SESSION['user_id'];

    if ($recipe_id > 0) {
        try {
            // Check if the recipe is already a favorite
            $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$user_id, $recipe_id]);
            $existing_favorite = $stmt->fetch();

            if ($existing_favorite) {
                // If it exists, remove it
                $deleteStmt = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
                $deleteStmt->execute([$existing_favorite['id']]);
            } else {
                // If it does not exist, add it
                $insertStmt = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
                $insertStmt->execute([$user_id, $recipe_id]);
            }

        } catch (PDOException $e) {
            // In a real application, you'd log this error.
            // For now, we'll just stop execution to see the error.
            die("Database error: " . $e->getMessage());
        }
    }
}

// *** THE FIX: Redirect to the favorites page to see the result! ***
header("Location: favorites.php");
exit();
?>

