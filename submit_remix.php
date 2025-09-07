<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$recipe_id = $_GET['recipe_id'] ?? 0;
$remix_title = $notes = "";
$remix_title_err = $notes_err = $image_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipe_id = $_POST['recipe_id'];

    if (empty(trim($_POST["remix_title"]))) {
        $remix_title_err = "Please enter a title for your remix.";
    } else {
        $remix_title = trim($_POST["remix_title"]);
    }

    if (empty(trim($_POST["notes"]))) {
        $notes_err = "Please describe your remix.";
    } else {
        $notes = trim($_POST["notes"]);
    }

    $image_url = NULL;
    // Handle image upload
    if (isset($_FILES["remix_image"]) && $_FILES["remix_image"]["error"] == 0) {
        $target_dir = "uploads/remixes/";
        $image_name = uniqid() . basename($_FILES["remix_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["remix_image"]["tmp_name"]);
        if ($check === false) {
            $image_err = "File is not an image.";
        }

        // Check file size (e.g., 5MB limit)
        if ($_FILES["remix_image"]["size"] > 5000000) {
            $image_err = "Sorry, your file is too large.";
        }

        // Allow certain file formats
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            $image_err = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        if (empty($image_err)) {
            if (move_uploaded_file($_FILES["remix_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $image_err = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (empty($remix_title_err) && empty($notes_err) && empty($image_err)) {
        $sql = "INSERT INTO remixes (original_recipe_id, user_id, remix_title, notes, image_url) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "iisss", $recipe_id, $_SESSION['id'], $remix_title, $notes, $image_url);
            if (mysqli_stmt_execute($stmt)) {
                header("location: recipe.php?id=" . $recipe_id . "&remix_submitted=true");
                exit();
            } else {
                echo "Oops! Something went wrong.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit a Remix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
     <div class="container mx-auto mt-12">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-center mb-6">Share Your Remix!</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="recipe_id" value="<?php echo $recipe_id; ?>">
                <div class="mb-4">
                    <label for="remix_title" class="block text-gray-700 font-semibold mb-2">Remix Title</label>
                    <input type="text" name="remix_title" id="remix_title" class="w-full px-4 py-2 border rounded-lg <?php echo (!empty($remix_title_err)) ? 'border-red-500' : 'border-gray-300'; ?>" value="<?php echo $remix_title; ?>" placeholder="e.g., Chris's Spicy Pancake Remix">
                    <span class="text-red-500 text-sm"><?php echo $remix_title_err; ?></span>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-gray-700 font-semibold mb-2">Your Notes</label>
                    <textarea name="notes" id="notes" rows="6" class="w-full px-4 py-2 border rounded-lg <?php echo (!empty($notes_err)) ? 'border-red-500' : 'border-gray-300'; ?>" placeholder="Describe the changes you made and how it turned out!"><?php echo $notes; ?></textarea>
                    <span class="text-red-500 text-sm"><?php echo $notes_err; ?></span>
                </div>
                 <div class="mb-6">
                    <label for="remix_image" class="block text-gray-700 font-semibold mb-2">Share a Photo (Optional)</label>
                    <input type="file" name="remix_image" id="remix_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <span class="text-red-500 text-sm"><?php echo $image_err; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <a href="recipe.php?id=<?php echo $recipe_id; ?>" class="text-gray-600 hover:underline">Cancel</a>
                    <button type="submit" class="bg-indigo-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-indigo-700">Submit Remix</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

