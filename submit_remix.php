<?php
// --- START: Robust Page Initialization ---
ob_start(); // Use output buffering as a failsafe for header redirects
session_start();
require_once 'config.php'; // Provides the $pdo database connection

// Get recipe_id consistently from either a GET or POST request
$recipe_id = isset($_REQUEST['recipe_id']) ? (int)$_REQUEST['recipe_id'] : 0;

// --- Step 1: Authentication Check ---
// A user MUST be logged in to access this page.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to share a remix.");
    ob_end_flush(); // Send buffer and headers
    exit;
}

// --- Step 2: Validate Recipe and Get Title ---
$recipe_title = '';
$page_error = ''; // This is for fatal errors, like a bad recipe ID

if ($recipe_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT title FROM recipes WHERE id = ?");
        $stmt->execute([$recipe_id]);
        $recipe = $stmt->fetch();
        if ($recipe) {
            $recipe_title = $recipe['title'];
        } else {
            // The recipe ID from the URL is not valid.
            $page_error = "The recipe you are trying to remix could not be found.";
        }
    } catch (PDOException $e) {
        // A database error occurred while trying to load the page.
        $page_error = "We're sorry, a database error prevented this page from loading.";
        error_log("Remix Page Load Error: " . $e->getMessage()); // Log for admin
    }
} else {
    // If we get here, it means no recipe_id was provided in the URL at all.
    header("Location: index.php");
    ob_end_flush();
    exit;
}

// --- Step 3: Initialize Form Variables ---
$remix_title_value = $notes_value = "";
$form_error = ""; // This is for user input errors (e.g., empty title)

// --- Step 4: Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Repopulate form fields with submitted data in case of an error
    $remix_title_value = trim($_POST["remix_title"]);
    $notes_value = trim($_POST["notes"]);

    // Basic validation
    if (empty($remix_title_value)) {
        $form_error = "Please enter a title for your remix.";
    } elseif (empty($notes_value)) {
        $form_error = "Please describe your remix in the notes section.";
    }

    $image_url = NULL;
    // --- Handle Image Upload ---
    if (empty($form_error) && isset($_FILES["remix_image"]) && $_FILES["remix_image"]["error"] == 0) {
        $target_dir = "uploads/remixes/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        
        $image_name = uniqid() . '-' . basename($_FILES["remix_image"]["name"]);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["remix_image"]["tmp_name"]);
        if ($check === false) { $form_error = "The uploaded file is not a valid image."; }
        if ($_FILES["remix_image"]["size"] > 5000000) { $form_error = "Your image file is too large (Max 5MB)."; }
        $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) { $form_error = "Sorry, only JPG, PNG, & GIF files are allowed."; }
        
        if (empty($form_error)) {
            if (move_uploaded_file($_FILES["remix_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $form_error = "Sorry, there was a server error uploading your file.";
            }
        }
    }

    // --- Insert into Database ---
    if (empty($form_error)) {
        try {
            $sql = "INSERT INTO remixes (original_recipe_id, user_id, remix_title, notes, image_url) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$recipe_id, $_SESSION['user_id'], $remix_title_value, $notes_value, $image_url]);
            
            // Success! Redirect back to the recipe page with a confirmation message.
            header("Location: recipe.php?id=" . $recipe_id . "&remix_submitted=true");
            ob_end_flush();
            exit();
        } catch (PDOException $e) {
            $form_error = "A database error occurred while saving your remix. Please try again.";
            error_log("Remix Insert Error: " . $e->getMessage()); // Log for admin
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Remix - The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-green-600">The Plant-Powered Pantry</a>
            
            <div class="hidden md:flex items-center space-x-4">
                <a href="index.php" class="text-gray-600 hover:text-green-600">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="favorites.php" class="text-gray-600 hover:text-green-600">My Favorites</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin_remixes.php" class="text-blue-600 hover:text-blue-800 font-semibold">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-green-600">Login</a>
                    <a href="register.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Register</a>
                <?php endif; ?>
            </div>

            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-800 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>
        </nav>

        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Home</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="favorites.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">My Favorites</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin_remixes.php" class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 hover:text-blue-800 hover:bg-gray-50">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Login</a>
                    <a href="register.php" class="block px-3 py-2 rounded-md text-base font-medium text-green-700 bg-green-50">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

     <div class="container mx-auto mt-12 px-4">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-lg">
            
            <?php if (!empty($page_error)): ?>
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-red-600 mb-4">An Error Occurred</h2>
                    <p class="text-gray-600 bg-red-50 p-4 rounded-md"><?php echo htmlspecialchars($page_error); ?></p>
                    <a href="index.php" class="mt-6 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Return to Homepage</a>
                </div>
            <?php else: ?>
                <h2 class="text-3xl font-bold text-center mb-2">Share Your Remix!</h2>
                <p class="text-center text-gray-500 mb-6">for "<?php echo htmlspecialchars($recipe_title); ?>"</p>

                <?php if (!empty($form_error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                        <?php echo htmlspecialchars($form_error); ?>
                    </div>
                <?php endif; ?>

                <form action="submit_remix.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="recipe_id" value="<?php echo htmlspecialchars($recipe_id); ?>">
                    
                    <div class="mb-4">
                        <label for="remix_title" class="block text-gray-700 font-semibold mb-2">Remix Title</label>
                        <input type="text" name="remix_title" id="remix_title" class="w-full px-4 py-2 border rounded-lg border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500" value="<?php echo htmlspecialchars($remix_title_value); ?>" placeholder="e.g., Chris's Spicy Pancake Remix" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notes" class="block text-gray-700 font-semibold mb-2">Your Notes</label>
                        <textarea name="notes" id="notes" rows="6" class="w-full px-4 py-2 border rounded-lg border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Describe the changes you made and how it turned out!" required><?php echo htmlspecialchars($notes_value); ?></textarea>
                    </div>
                    
                     <div class="mb-6">
                        <label for="remix_image" class="block text-gray-700 font-semibold mb-2">Share a Photo (Optional)</label>
                        <input type="file" name="remix_image" id="remix_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                    
                    <div class="flex justify-between items-center mt-8">
                        <a href="recipe.php?id=<?php echo htmlspecialchars($recipe_id); ?>" class="text-gray-600 hover:underline">Cancel</a>
                        <button type="submit" class="bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Submit for Review</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
<?php ob_end_flush(); // Send final output to the browser ?>

