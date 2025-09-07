<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";

// Check if recipe_id is provided
if (isset($_POST['recipe_id']) && !empty($_POST['recipe_id'])) {
    $recipe_id = $_POST['recipe_id'];
    $user_id = $_SESSION['id'];

    // Check if the recipe is already favorited by the user
    $sql_check = "SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?";
    
    if ($stmt_check = mysqli_prepare($link, $sql_check)) {
        mysqli_stmt_bind_param($stmt_check, "ii", $user_id, $recipe_id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            // It's already a favorite, so REMOVE it
            $sql_delete = "DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?";
            if ($stmt_delete = mysqli_prepare($link, $sql_delete)) {
                mysqli_stmt_bind_param($stmt_delete, "ii", $user_id, $recipe_id);
                mysqli_stmt_execute($stmt_delete);
                mysqli_stmt_close($stmt_delete);
            }
        } else {
            // It's not a favorite, so ADD it
            $sql_insert = "INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)";
            if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                mysqli_stmt_bind_param($stmt_insert, "ii", $user_id, $recipe_id);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
        }
        mysqli_stmt_close($stmt_check);
    }
    
    // Redirect back to the recipe page they were on
    header("location: recipe.php?id=" . $recipe_id);
    exit;

} else {
    // If no recipe ID was provided, redirect to homepage
    header("location: index.php");
    exit;
}
?>
