<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id === 0) {
    header("Location: admin_remixes.php"); // Or an admin dashboard
    exit();
}

$success_message = '';
$error_message = '';

// --- Handle Form Submission (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $prep_time = filter_input(INPUT_POST, 'prep_time', FILTER_VALIDATE_INT);
    $cook_time = filter_input(INPUT_POST, 'cook_time', FILTER_VALIDATE_INT);
    $yields = trim($_POST['yields']);
    
    $ingredients_raw = trim($_POST['ingredients']);
    $ingredients_json = json_encode(array_filter(array_map('trim', explode("\n", $ingredients_raw))));

    $instructions_raw = trim($_POST['instructions']);
    $instructions_json = json_encode(array_filter(array_map('trim', explode("\n", $instructions_raw))));
    
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
    $current_image_url = $_POST['current_image_url'];

    // --- Image Upload Handling ---
    $image_url = $current_image_url; // Default to the current image
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $target_dir = "uploads/recipes/";
        $image_name = uniqid() . '-' . basename($_FILES["recipe_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
        if ($check !== false) {
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file; // Set the new image URL
                    // Optional: Delete the old image file if it's not a default one
                    if (!empty($current_image_url) && file_exists($current_image_url)) {
                       // unlink($current_image_url);
                    }
                } else {
                    $error_message = "Sorry, there was an error uploading your new file.";
                }
            } else {
                $error_message = "Sorry, only JPG, JPEG, PNG, WEBP & GIF files are allowed.";
            }
        } else {
            $error_message = "New file is not an image.";
        }
    }

    // --- Database Update ---
    if (empty($error_message)) {
        try {
            $sql = "UPDATE recipes SET 
                        title = ?, 
                        description = ?, 
                        ingredients = ?, 
                        instructions = ?, 
                        prep_time = ?, 
                        cook_time = ?, 
                        yields = ?, 
                        image_url = ?, 
                        nutrition_info = ?, 
                        notes = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$title, $description, $ingredients_json, $instructions_json, $prep_time, $cook_time, $yields, $image_url, $nutrition_json, $notes, $recipe_id])) {
                header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $recipe_id . "&success=1");
                exit();
            } else {
                $error_message = "Failed to update the recipe in the database.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Check for success message from redirect
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success_message = "Recipe updated successfully!";
}

// --- Fetch Existing Recipe Data to Pre-populate Form ---
try {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        $error_message = "Recipe not found.";
    } else {
        // Decode JSON data for textareas
        $ingredients_array = json_decode($recipe['ingredients'], true);
        $recipe['ingredients_text'] = is_array($ingredients_array) ? implode("\n", $ingredients_array) : $recipe['ingredients'];

        $instructions_array = json_decode($recipe['instructions'], true);
        $recipe['instructions_text'] = is_array($instructions_array) ? implode("\n", $instructions_array) : $recipe['instructions'];
        
        $recipe['nutrition_array'] = json_decode($recipe['nutrition_info'], true);
    }
} catch (PDOException $e) {
    $error_message = "Database error fetching recipe: " . $e->getMessage();
    $recipe = null; // Ensure recipe is null on error
}

$page_title = "Edit Recipe";
require_once 'includes/admin_header.php';
?>

<main class="container mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Edit Recipe</h1>
        <?php if ($recipe): ?>
        <a href="recipe.php?id=<?php echo $recipe['id']; ?>" target="_blank" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg shadow-md">
            View Live Recipe
        </a>
        <?php endif; ?>
    </div>


    <?php if ($recipe): ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-lg space-y-6">
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <input type="hidden" name="current_image_url" value="<?php echo htmlspecialchars($recipe['image_url']); ?>">

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Recipe Title</label>
                <input type="text" name="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($recipe['title']); ?>" required>
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required><?php echo htmlspecialchars($recipe['description']); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                 <div>
                    <label for="prep_time" class="block text-sm font-medium text-gray-700">Prep Time (mins)</label>
                    <input type="number" name="prep_time" id="prep_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($recipe['prep_time']); ?>" required>
                </div>
                 <div>
                    <label for="cook_time" class="block text-sm font-medium text-gray-700">Cook Time (mins)</label>
                    <input type="number" name="cook_time" id="cook_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($recipe['cook_time']); ?>" required>
                </div>
                 <div>
                    <label for="yields" class="block text-sm font-medium text-gray-700">Yields</label>
                    <input type="text" name="yields" id="yields" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($recipe['yields']); ?>" placeholder="e.g., 4 servings" required>
                </div>
            </div>

             <div>
                <label class="block text-sm font-medium text-gray-700">Current Image</label>
                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="Current recipe image" class="mt-2 rounded-lg max-w-xs">
            </div>
             <div>
                <label for="recipe_image" class="block text-sm font-medium text-gray-700">Upload New Image (Optional)</label>
                <p class="text-xs text-gray-500 mb-2">If you upload a new image, it will replace the current one.</p>
                <input type="file" name="recipe_image" id="recipe_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="ingredients" class="block text-sm font-medium text-gray-700">Ingredients (one per line)</label>
                    <textarea name="ingredients" id="ingredients" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required><?php echo htmlspecialchars($recipe['ingredients_text']); ?></textarea>
                </div>
                <div>
                    <label for="instructions" class="block text-sm font-medium text-gray-700">Instructions (one per line)</label>
                    <textarea name="instructions" id="instructions" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required><?php echo htmlspecialchars($recipe['instructions_text']); ?></textarea>
                </div>
            </div>

             <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Chef's Notes</label>
                <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"><?php echo htmlspecialchars($recipe['notes']); ?></textarea>
            </div>

            <div id="nutrition-container">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nutrition Facts (Key-Value Pairs)</label>
                <div class="space-y-2">
                    <?php if (is_array($recipe['nutrition_array']) && count($recipe['nutrition_array']) > 0): ?>
                        <?php foreach ($recipe['nutrition_array'] as $key => $value): ?>
                            <div class="flex items-center gap-2">
                                <input type="text" name="nutrition_key[]" placeholder="e.g., Protein" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($key); ?>">
                                <input type="text" name="nutrition_value[]" placeholder="e.g., 15g" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="<?php echo htmlspecialchars($value); ?>">
                                <button type="button" class="remove-nutrition-btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-full text-xs">&times;</button>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button type="button" id="add-nutrition-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-800 font-semibold">Add More</button>
            </div>

            <div class="pt-5">
                <div class="flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>
</main>

<script>
    const addNutritionRow = (key = '', value = '') => {
        const container = document.querySelector('#nutrition-container .space-y-2');
        const newRow = document.createElement('div');
        newRow.className = 'flex items-center gap-2';
        newRow.innerHTML = `
            <input type="text" name="nutrition_key[]" placeholder="e.g., Protein" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="${escapeHTML(key)}">
            <input type="text" name="nutrition_value[]" placeholder="e.g., 15g" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" value="${escapeHTML(value)}">
            <button type="button" class="remove-nutrition-btn bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded-full text-xs">&times;</button>
        `;
        container.appendChild(newRow);
    };

    const escapeHTML = str => str.toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    
    document.getElementById('add-nutrition-btn').addEventListener('click', () => addNutritionRow());

    document.getElementById('nutrition-container').addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-nutrition-btn')) {
            e.target.parentElement.remove();
        }
    });
</script>

<?php
require_once 'includes/footer.php';
?>
