<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin, otherwise redirect
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$title = $description = $ingredients_json = $instructions_json = $prep_time = $cook_time = $yields = $image_url = $nutrition_info = $notes = "";
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (empty(trim($_POST["title"])) || empty(trim($_POST["description"])) || empty(trim($_POST["ingredients"])) || empty(trim($_POST["instructions"]))) {
        $error = "Please fill out all required fields.";
    } else {
        $title = trim($_POST["title"]);
        $description = trim($_POST["description"]);
        $prep_time = (int)$_POST["prep_time"];
        $cook_time = (int)$_POST["cook_time"];
        $yields = trim($_POST["yields"]);
        $image_url = trim($_POST["image_url"]);
        $nutrition_info = trim($_POST["nutrition_info"]);
        $notes = trim($_POST["notes"]);
        
        // Convert ingredients and instructions from textarea (one per line) to JSON
        $ingredients_array = array_filter(array_map('trim', explode("\n", $_POST["ingredients"])));
        $instructions_array = array_filter(array_map('trim', explode("\n", $_POST["instructions"])));
        
        $ingredients_json = json_encode($ingredients_array);
        $instructions_json = json_encode($instructions_array);
        
        $author_id = $_SESSION["id"];

        $sql = "INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, yields, image_url, nutrition_info, notes, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssiissssi", $title, $description, $ingredients_json, $instructions_json, $prep_time, $cook_time, $yields, $image_url, $nutrition_info, $notes, $author_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Recipe added successfully! <a href='recipe.php?id=".mysqli_insert_id($link)."' class='underline'>View it here</a>.";
                // Clear form fields
                 $title = $description = $ingredients_json = $instructions_json = $prep_time = $cook_time = $yields = $image_url = $nutrition_info = $notes = "";
            } else {
                $error = "Something went wrong. Please try again later.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Recipe | Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Admin Panel</h1>
        <p class="text-lg text-gray-600 mb-6">The Plant-Powered Pantry</p>

        <!-- Admin Navigation -->
        <nav class="bg-white rounded-lg shadow-md p-4 mb-8 flex space-x-4 items-center">
            <a href="admin_remixes.php" class="text-gray-600 hover:text-green-700 font-semibold">Remix Moderation</a>
            <a href="admin_add_recipe.php" class="text-green-700 font-bold border-b-2 border-green-700">Add New Recipe</a>
            <a href="index.php" class="text-blue-600 hover:text-blue-800 ml-auto">&larr; Back to Main Site</a>
        </nav>

        <!-- Add Recipe Form -->
        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-gray-900 mb-6">Add a New Recipe</h2>

            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert"><?php echo $success; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div>
                        <div class="mb-4">
                            <label for="title" class="block text-gray-700 font-semibold mb-2">Recipe Title</label>
                            <input type="text" name="title" id="title" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" value="<?php echo htmlspecialchars($title); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-semibold mb-2">Short Description</label>
                            <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                             <div>
                                <label for="prep_time" class="block text-gray-700 font-semibold mb-2">Prep Time (mins)</label>
                                <input type="number" name="prep_time" id="prep_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?php echo htmlspecialchars($prep_time); ?>">
                            </div>
                            <div>
                                <label for="cook_time" class="block text-gray-700 font-semibold mb-2">Cook Time (mins)</label>
                                <input type="number" name="cook_time" id="cook_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="<?php echo htmlspecialchars($cook_time); ?>">
                            </div>
                        </div>
                         <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="yields" class="block text-gray-700 font-semibold mb-2">Yields</label>
                                <input type="text" name="yields" id="yields" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., 4 servings" value="<?php echo htmlspecialchars($yields); ?>">
                            </div>
                            <div>
                                <label for="image_url" class="block text-gray-700 font-semibold mb-2">Image URL</label>
                                <input type="text" name="image_url" id="image_url" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="e.g., images/recipe.jpg" value="<?php echo htmlspecialchars($image_url); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div>
                        <div class="mb-4">
                            <label for="ingredients" class="block text-gray-700 font-semibold mb-2">Ingredients (one per line)</label>
                            <textarea name="ingredients" id="ingredients" rows="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="instructions" class="block text-gray-700 font-semibold mb-2">Instructions (one per line)</label>
                            <textarea name="instructions" id="instructions" rows="8" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Full Width Bottom -->
                <div class="mt-6">
                    <div class="mb-4">
                        <label for="nutrition_info" class="block text-gray-700 font-semibold mb-2">Nutrition Info (Summary)</label>
                        <textarea name="nutrition_info" id="nutrition_info" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700 font-semibold mb-2">Chef's Notes</label>
                        <textarea name="notes" id="notes" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>

                <div class="mt-8 text-right">
                    <button type="submit" class="bg-green-600 text-white font-semibold py-3 px-8 rounded-lg shadow-lg hover:bg-green-700 transition duration-300">Add Recipe</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
