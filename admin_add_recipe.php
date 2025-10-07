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
    
    // Process ingredients and instructions from textareas
    $ingredients_raw = trim($_POST['ingredients']);
    $ingredients_json = json_encode(array_filter(array_map('trim', explode("\n", $ingredients_raw))));

    $instructions_raw = trim($_POST['instructions']);
    $instructions_json = json_encode(array_filter(array_map('trim', explode("\n", $instructions_raw))));
    
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
    $author_id = $_SESSION['user_id'];

    // --- Image Upload Handling ---
    $image_url = '';
    if (isset($_FILES['recipe_image']) && $_FILES['recipe_image']['error'] == 0) {
        $target_dir = "uploads/recipes/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $image_name = uniqid() . '-' . basename($_FILES["recipe_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["recipe_image"]["tmp_name"]);
        if ($check !== false) {
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif', 'webp'];
            if (in_array($imageFileType, $allowed_types)) {
                if (move_uploaded_file($_FILES["recipe_image"]["tmp_name"], $target_file)) {
                    $image_url = $target_file;
                } else {
                    $error_message = "Sorry, there was an error uploading your file.";
                }
            } else {
                $error_message = "Sorry, only JPG, JPEG, PNG, WEBP & GIF files are allowed.";
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
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
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

$page_title = "Enhanced Recipe Uploader";
require_once 'includes/admin_header.php';
?>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Enhanced Recipe Uploader</h1>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left Panel: Raw Text Input -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-xl font-bold text-gray-800 mb-4">1. Paste Raw Recipe Text</h2>
                <p class="text-sm text-gray-600 mb-4">Paste the entire recipe from your document here. My script will read it and attempt to fill out the form on the right.</p>
                <textarea id="raw-recipe-input" rows="25" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Silken Tofu Chocolate Pudding..."></textarea>
                <button type="button" id="parse-recipe-btn" class="mt-4 w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Let Emma Work Her Magic (Parse Recipe)
                </button>
            </div>

            <!-- Right Panel: The Recipe Form -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-lg space-y-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">2. Review & Save</h2>
                
                <?php if ($success_message): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert"><?php echo htmlspecialchars($error_message); ?></div>
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
                    <label for="notes" class="block text-sm font-medium text-gray-700">Chef's Notes</label>
                    <textarea name="notes" id="notes" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                </div>

                <!-- Dynamic Nutrition Info -->
                <div id="nutrition-container">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nutrition Facts (Key-Value Pairs)</label>
                    <div class="space-y-2">
                        <!-- JS will populate this -->
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
        </div>
    </main>

    <script>
        // --- Emma's Magic Parsing Engine (v2) ---
        document.getElementById('parse-recipe-btn').addEventListener('click', function() {
            const rawText = document.getElementById('raw-recipe-input').value;
            if (!rawText) {
                alert('Please paste the recipe text first, my dear!');
                return;
            }

            // --- Define Helper Functions ---
            const getSection = (text, startKeyword, endKeywords = []) => {
                const lowerText = text.toLowerCase();
                const lowerStartKeyword = startKeyword.toLowerCase();
                const startIndex = lowerText.indexOf(lowerStartKeyword);
                if (startIndex === -1) return '';

                let contentStartIndex = startIndex + startKeyword.length;
                
                if (text[contentStartIndex] === ':') {
                    contentStartIndex++;
                }

                let textAfterStart = text.substring(contentStartIndex);
                let endIndex = textAfterStart.length;

                if (endKeywords.length > 0) {
                    for (const endKeyword of endKeywords) {
                        const currentEndIndex = textAfterStart.toLowerCase().indexOf(endKeyword.toLowerCase());
                        if (currentEndIndex !== -1 && currentEndIndex < endIndex) {
                            endIndex = currentEndIndex;
                        }
                    }
                }
                
                return textAfterStart.substring(0, endIndex).trim();
            };
            
            // --- 1. Parse Title & Description ---
            const lines = rawText.split('\n');
            document.getElementById('title').value = (lines[0] || '').trim();
            
            const descriptionMatch = rawText.match(/^[^\n]+\n+([\s\S]+?)(?=^\s*(?:Yields|Prep time|Cook time|Ingredients|Instructions):)/im);
            document.getElementById('description').value = descriptionMatch ? descriptionMatch[1].trim() : '';


            // --- 2. Parse Meta Info (Yields, Prep, Cook) ---
            const prepTimeMatch = rawText.match(/Prep time:\s*(\d+)/i);
            document.getElementById('prep_time').value = prepTimeMatch ? prepTimeMatch[1] : '';
            
            const cookTimeMatch = rawText.match(/Cook time:\s*(\d+)/i);
            document.getElementById('cook_time').value = cookTimeMatch ? cookTimeMatch[1] : '0';

            const yieldsMatch = rawText.match(/Yields:\s*(.*?)(?=\s*(?:Prep time|Cook time|Ingredients)|$)/im);
            document.getElementById('yields').value = yieldsMatch ? yieldsMatch[1].trim() : '';

            // --- 3. Parse Ingredients & Instructions ---
            const ingredientsBlock = getSection(rawText, 'Ingredients', ['Instructions:', 'Nutritional Information:', 'Notes:', 'Note on']);
            document.getElementById('ingredients').value = ingredientsBlock.replace(/^[*-]\s*/gm, '').trim();

            const instructionsBlock = getSection(rawText, 'Instructions', ['Nutritional Information:', 'Notes:', 'Note on']);
            document.getElementById('instructions').value = instructionsBlock.replace(/^\d+\.\s*/gm, '').trim();

            // --- 4. Parse Notes (More Robust) ---
            let notesBlock = getSection(rawText, 'Notes', []); 
            if (notesBlock) {
                document.getElementById('notes').value = notesBlock;
            } else {
                notesBlock = getSection(rawText, 'Note on', []);
                if (notesBlock) {
                    document.getElementById('notes').value = 'Note on ' + notesBlock;
                } else {
                    document.getElementById('notes').value = '';
                }
            }
            
            // --- 5. Parse Nutrition Info ---
            const nutritionContainer = document.querySelector('#nutrition-container .space-y-2');
            nutritionContainer.innerHTML = '';

            const nutritionBlock = getSection(rawText, 'Nutritional Information', ['Macronutrient Breakdown:', 'Note on', 'Notes:']);
            if (nutritionBlock) {
                const nutritionLines = nutritionBlock.split('\n').filter(line => line.includes(':') && !line.toLowerCase().includes('per serving'));
                nutritionLines.forEach(line => {
                    const parts = line.split(':');
                    const key = parts[0].trim();
                    const value = parts.slice(1).join(':').trim();
                    if (key && value) addNutritionRow(key, value);
                });
            }
             
            const macroBlock = getSection(rawText, 'Macronutrient Breakdown', ['Note on', 'Notes:']);
            if (macroBlock) {
                const macroLines = macroBlock.split('\n').filter(line => line.includes(':'));
                 macroLines.forEach(line => {
                    const parts = line.split(':');
                    const key = `Macro: ${parts[0].trim()}`;
                    const value = parts.slice(1).join(':').trim();
                    if(key && value) addNutritionRow(key, value);
                 });
            }
        });

        // Function to add a new nutrition row to the form
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

        // Utility to prevent basic HTML injection
        const escapeHTML = str => str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        
        // Manual 'Add More' button
        document.getElementById('add-nutrition-btn').addEventListener('click', () => addNutritionRow());

        // Event delegation for removing nutrition rows
        document.getElementById('nutrition-container').addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-nutrition-btn')) {
                e.target.parentElement.remove();
            }
        });
    </script>
    
<?php require_once 'includes/footer.php'; ?>
