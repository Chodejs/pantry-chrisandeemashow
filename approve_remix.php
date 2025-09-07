<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin, otherwise redirect
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['remix_id'])) {
    $remix_id = $_POST['remix_id'];

    // Prepare an update statement
    $sql = "UPDATE remixes SET is_approved = 1 WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $remix_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect back to the moderation page with a success message (optional)
            header("location: admin_remixes.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($link);
} else {
    // If the request is not a POST or remix_id isn't set, redirect
    header("location: admin_remixes.php");
    exit;
}
?>
