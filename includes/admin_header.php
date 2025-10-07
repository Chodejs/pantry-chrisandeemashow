<?php
// This is the header file for all backend admin pages.

// We need to ensure the session is started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The $page_title variable should be set before including this file -->
    <title>Admin - <?php echo isset($page_title) ? htmlspecialchars($page_title) : "Dashboard"; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-gray-800">Admin Dashboard</div>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="admin_remixes.php" class="text-gray-600 hover:text-green-600">Remix Moderation</a>
                <a href="admin_comment_moderation.php" class="text-gray-600 hover:text-green-600">Comment Moderation</a>
                <a href="admin_manage_recipes.php" class="text-gray-600 hover:text-green-600">Manage Recipes</a>
                <a href="admin_add_recipe.php" class="text-gray-600 hover:text-green-600">Add Recipe</a>
                <a href="index.php" class="text-gray-600 hover:text-green-600" target="_blank">View Site</a>
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
                <a href="admin_comment_moderation.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Comment Moderation</a>
                <a href="admin_manage_recipes.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Manage Recipes</a>
                <a href="admin_add_recipe.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Add Recipe</a>
                <a href="index.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50" target="_blank">View Site</a>
                <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600">Logout</a>
            </div>
        </div>
    </header>

