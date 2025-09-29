<?php
// EMMA'S FIX: Start output buffering to prevent "headers already sent" errors.
ob_start();

// Initialize the session
session_start();

require_once "config.php"; // Provides the $pdo object

// Check if the user is logged in, otherwise deny
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Or handle with an error message
    header("location: login.php");
    ob_end_flush();
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_id'])) {
    // EMMA'S FIX: The form field is named 'comment', not 'comment_text'.
    $comment_text = trim($_POST['comment']);
    $recipe_id = $_POST['recipe_id'];
    // EMMA'S FIX: Use 'user_id' for consistency with the rest of the site.
    $user_id = $_SESSION['user_id'];
    $image_url = null; // Default to null

    // Validate comment text
    if (empty($comment_text)) {
        // Handle error - maybe set a session flash message
        header("location: recipe.php?id=" . $recipe_id . "&error=empty_comment");
        ob_end_flush();
        exit;
    }

    // --- Image Upload Handling ---
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
        $target_dir = "uploads/comments/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        
        // Create a unique filename to prevent overwriting
        $image_filename = uniqid() . '-' . basename($_FILES["comment_image"]["name"]);
        $target_file = $target_dir . $image_filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic validation
        $check = getimagesize($_FILES["comment_image"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if (in_array($imageFileType, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES["comment_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file; // Set image_url to the path
                }
            }
        }
    }

    // --- Insert into Database (EMMA'S PDO FIX) ---
    try {
        $sql = "INSERT INTO comments (recipe_id, user_id, comment_text, image_url) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$recipe_id, $user_id, $comment_text, $image_url]);
    } catch (PDOException $e) {
        // In a real application, log this error.
        error_log("Comment submission failed: " . $e->getMessage());
        // Redirect with a generic error message for the user.
        header("location: recipe.php?id=" . $recipe_id . "&error=database");
        ob_end_flush();
        exit;
    }

    // Redirect back to the recipe page
    header("location: recipe.php?id=" . $recipe_id . "&comment=success");
    ob_end_flush();
    exit;
}
?>
