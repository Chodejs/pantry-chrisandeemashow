<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Retrieve and sanitize form data ---
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $prep_time = filter_input(INPUT_POST, 'prep_time', FILTER_VALIDATE_INT);
    $cook_time = filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
    $yields = trim($_POST['yields']);
    
    // Process ingredients and instructions from textareas into JSON arrays
    $ingredients_raw = trim($_POST['ingredients']);
    $ingredients_array = array_filter(array_map('trim', explode("\n", $ingredients_raw)));
    $ingredients_json = json_encode($ingredients_array);

    $instructions_raw = trim($_POST['instructions']);
    $instructions_array = array_filter(array_map('trim', explode("\n", $instructions_raw)));
    $instructions_json = json_encode($instructions_array);
    
    // Process nutrition info into a JSON object
    $nutrition_keys = $_POST['nutrition_key'] ?? [];
    $nutrition_values = $_POST['nutrition_value'] ?? [];
    $nutrition_array = [];
    for ($i = 0; $i < count($nutrition_keys); $i++) {
        if (!empty($nutrition_keys[$i]) && !empty($nutrition_values[$i])) {
            $nutrition_array[trim($nutrition_keys[$i])] = trim($nutrition_values[$i]);
        }
    }
    $nutrition_json = json_encode($nutrition_array);

    $notes = trim($_POST['notes']);
    $author_id = $_SESSION['user_id']; // The logged-in admin is the author

    // --- Image Upload Handling ---
    $image_url = ''; // Default empty
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $target_dir = "uploads/recipes/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = uniqid() . '-' . basename($_FILES["recipe_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Basic validation
        $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
        if ($check !== false) {
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $error_message = "File is not an image.";
        }
    }

    // --- Database Insertion ---
    if (empty($error_message)) {
        try {
            $sql = "INSERT INTO recipes (title, description, ingredients, instructions, prep_time, cook_time, yields, image_url, nutrition_info, notes, author_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$title, $description, $ingredients_json, $instructions_json, $prep_time, $cook_time, $yields, $image_url, $nutrition_json, $notes, $author_id])) {
                $success_message = "Recipe added successfully! You can add another one.";
                 // Clear POST data to reset the form by redirecting
                header("Location: admin_add_recipe.php?success=1");
                exit();
            } else {
                $error_message = "Failed to add the recipe to the database.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Recipe added successfully! You can now add another one.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Add Recipe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">

    <!-- Admin Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-800">Admin Dashboard</div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="admin_remixes.php" class="text-gray-600 hover:text-green-600">Remix Moderation</a>
                <a href="admin_add_recipe.php" class="text-green-600 font-semibold border-b-2 border-green-600">Add a Recipe</a>
                <a href="index.php" class="text-gray-600 hover:text-green-600">Back to Site</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Logout</a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="admin_remixes.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Remix Moderation</a>
                <a href="admin_add_recipe.php" class="block px-3 py-2 rounded-md text-base font-medium text-green-700 bg-green-50">Add a Recipe</a>
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Back to Site</a>
                <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600">Logout</a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Add New Recipe</h1>

        <form action="admin_add_recipe.php" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-lg space-y-6">
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Recipe Title</label>
                <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 <div>
                    <label for="prep_time" class="block text-sm font-medium text-gray-700">Prep Time (mins)</label>
                    <input type="number" name="prep_time" id="prep_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>
                 <div>
                    <label for="cook_time" class="block text-sm font-medium text-gray-700">Cook Time (mins)</label>
                    <input type="number" name="cook_time" id="cook_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>
                 <div>
                    <label for="yields" class="block text-sm font-medium text-gray-700">Yields</label>
                    <input type="text" name="yields" id="yields" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="e.g., 4 servings" required>
                </div>
            </div>

             <div>
                <label for="recipe_image" class="block text-sm font-medium text-gray-700">Recipe Image</label>
                <input type="file" name="recipe_image" id="recipe_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="ingredients" class="block text-sm font-medium text-gray-700">Ingredients (one per line)</label>
                    <textarea name="ingredients" id="ingredients" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
                </div>
                <div>
                    <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions (one per line)</label>
                    <textarea name="instructions" id="instructions" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
                </div>
            </div>

             <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Chef's Notes (one sentence per line)</label>
                <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
            </div>

            <!-- Dynamic Nutrition Info -->
            <div id="nutrition-container">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nutrition Facts (Key-Value Pairs)</label>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text" name="nutrition_key[]" placeholder="e.g., Calories" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                        <input type="text" name="nutrition_value[]" placeholder="e.g., 250" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                </div>
                <button type="button" id="add-nutrition-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-semibold">Add More</button>
            </div>

            <div class="pt-5">
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Save Recipe
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script>
        document.getElementById('add-nutrition-btn').addEventListener('click', function() {
            const container = document.getElementById('nutrition-container').querySelector('.space-y-2');
            const newRow = document.createElement('div');
            newRow.className = 'flex items-center gap-2';
            newRow.innerHTML = `
                <input type="text" name="nutrition_key[]" placeholder="e.g., Protein" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                <input type="text" name="nutrition_value[]" placeholder="e.g., 15g" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
            `;
            container.appendChild(newRow);
        });

        // JavaScript for mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
