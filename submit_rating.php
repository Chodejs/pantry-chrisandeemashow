<?php
session_start();
require_once 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipe_id = isset($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0;
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $user_id = $_SESSION['user_id'];

    if ($recipe_id > 0 && $rating >= 1 && $rating <= 5) {
        try {
            // Start a transaction to ensure data integrity
            $pdo->beginTransaction();

            // Check if the user has already rated this recipe
            $stmt = $pdo->prepare("SELECT id FROM ratings WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$user_id, $recipe_id]);
            $existing_rating = $stmt->fetch();

            if ($existing_rating) {
                // If they have, update their existing rating
                $updateStmt = $pdo->prepare("UPDATE ratings SET rating = ?, created_at = NOW() WHERE id = ?");
                $updateStmt->execute([$rating, $existing_rating['id']]);
            } else {
                // If they haven't, insert a new rating
                $insertStmt = $pdo->prepare("INSERT INTO ratings (user_id, recipe_id, rating) VALUES (?, ?, ?)");
                $insertStmt->execute([$user_id, $recipe_id, $rating]);
            }

            // Recalculate the average rating and total rating count for the recipe
            $recalcStmt = $pdo->prepare(
                "UPDATE recipes SET 
                    average_rating = (SELECT AVG(rating) FROM ratings WHERE recipe_id = ?),
                    rating_count = (SELECT COUNT(*) FROM ratings WHERE recipe_id = ?)
                 WHERE id = ?"
            );
            // *** FIX: Provide the recipe_id for all three placeholders ***
            $recalcStmt->execute([$recipe_id, $recipe_id, $recipe_id]);

            // If all queries were successful, commit the changes
            $pdo->commit();

        } catch (PDOException $e) {
            // If any part of the transaction fails, roll everything back
            $pdo->rollBack();
            // For debugging, it's helpful to see the error. In production, you might log this.
            die("Database error: " . $e->getMessage());
        }
    }
}

// Redirect the user back to the recipe page they were on
header("Location: recipe.php?id=" . $recipe_id);
exit();
?>

