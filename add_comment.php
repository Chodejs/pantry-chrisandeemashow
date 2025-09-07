<?php
// Initialize the session
session_start();

require_once "config.php";

// Check if the user is logged in, otherwise deny
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Or handle with an error message
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recipe_id'])) {
    $comment_text = trim($_POST['comment_text']);
    $recipe_id = $_POST['recipe_id'];
    $user_id = $_SESSION['id'];
    $image_url = null; // Default to null

    // Validate comment text
    if (empty($comment_text)) {
        // Handle error - maybe set a session flash message
        header("location: recipe.php?id=" . $recipe_id);
        exit;
    }

    // --- Image Upload Handling ---
    if (isset($_FILES['comment_image']) && $_FILES['comment_image']['error'] == 0) {
        $target_dir = "uploads/comments/";
        // Create a unique filename to prevent overwriting
        $image_filename = uniqid() . '-' . basename($_FILES["comment_image"]["name"]);
        $target_file = $target_dir . $image_filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic validation
        $check = getimagesize($_FILES["comment_image"]["tmp_name"]);
        if ($check !== false) {
            // Allow certain file formats
            if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                if (move_uploaded_file($_FILES["comment_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file; // Set image_url to the path
                }
            }
        }
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO comments (recipe_id, user_id, comment_text, image_url) VALUES (?, ?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "iiss", $recipe_id, $user_id, $comment_text, $image_url);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);

    // Redirect back to the recipe page
    header("location: recipe.php?id=" . $recipe_id);
    exit;
}
?>
