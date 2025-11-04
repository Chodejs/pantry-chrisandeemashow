<?php
// This file contains the opening HTML, head section, and the main site navigation.
// It assumes that a session has been started and config.php has been included on the parent page.
// It also uses a $page_title variable, which should be set on the parent page before including this file.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>Chris and Emma's Pantry</title>

    <?php
    // --- EMMA'S OPEN GRAPH (OGP) & TWITTER CARD IMPLEMENTATION ---
    
    // 1. Set up the base URL (needed for all absolute URLs)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . "://" . $host;

    // 2. Check if we are on a specific recipe page.
    //    We know we are if the '$recipe' variable is set (which recipe.php does *before* including this header)
    if (isset($recipe) && is_array($recipe)) {
        // --- This is a Recipe Page ---
        $og_title = $recipe['title'];
        // Clean the description: remove HTML and trim it
        $og_description = htmlspecialchars(strip_tags($recipe['description']));
        // Create an absolute URL for the image
        $og_image = $base_url . "/" . ltrim($recipe['image_url'], '/');
        // $current_page_url is already defined in recipe.php before this file is included
        $og_url = $current_page_url; 
        $og_type = "article"; // A recipe is a type of article

    } else {
        // --- This is a General Page (Homepage, Search, Favorites, etc.) ---
        // Use the $page_title if available, otherwise a default
        $og_title = (isset($page_title) ? htmlspecialchars($page_title) . ' | ' : '') . "Chris and Emma's Pantry";
        $og_description = "Discover a growing collection of delicious, wholesome, and plant-powered recipes from our kitchen to yours.";
        // Use a nice default image for the site
        $og_image = $base_url . "/images/classic-pancakes.jpg"; 
        // Get the current URL
        $og_url = $base_url . $_SERVER['REQUEST_URI'];
        $og_type = "website";
    }
    ?>

    <!-- Open Graph Tags (for Facebook, Pinterest, etc.) -->
    <meta property="og:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($og_url); ?>">
    <meta property="og:type" content="<?php echo htmlspecialchars($og_type); ?>">
    <meta property="og:site_name" content="Chris and Emma's Pantry">
    
    <!-- Twitter Card Tags (for X / Twitter) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($og_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($og_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image); ?>">

    <!-- --- END OF OGP IMPLEMENTATION --- -->

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navigation Bar -->
    <header class="bg-white shadow-md no-print sticky top-0 z-50">
        <nav class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-green-600">Chris and Emma's Pantry</a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-4">
                 <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full">
                    <input type="text" name="q" placeholder="Search..." class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
                    <button type="submit" class="text-gray-500 p-2 rounded-full hover:text-green-500"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars(strtok($_SESSION['username'], ' ')); ?>!</span>
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
                <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full mb-4 px-2">
                    <input type="text" name="q" placeholder="Search..." class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
                    <button type="submit" class="text-gray-500 p-2 rounded-full hover:text-green-500"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <p class="block px-3 py-2 text-base font-medium text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                    <a href="favorites.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">My Favorites</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin_remixes.php" class="block px-3 py-2 rounded-md text-base font-medium text-blue-600 hover:text-blue-800 hover:bg-gray-50">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-red-500 hover:bg-red-600">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-green-600 hover:bg-gray-50">Login</a>
                    <a href="register.php" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-green-500 hover:bg-green-600">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
