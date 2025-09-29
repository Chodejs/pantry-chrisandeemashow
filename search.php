<?php
session_start();
require_once 'config.php'; // This provides the $pdo object

$search_term = isset($_GET['q']) ? trim($_GET['q']) : '';
$recipes = [];
$error_message = '';

if (empty($search_term)) {
    $error_message = "The search field was empty. Please enter something to search for.";
} else {
    try {
        // Prepare and execute the search query using PDO
        $sql = "SELECT id, title, description, image_url, average_rating, rating_count FROM recipes WHERE title LIKE ? OR description LIKE ?";
        $stmt = $pdo->prepare($sql);
        
        $param_term = "%" . $search_term . "%";
        $stmt->execute([$param_term, $param_term]);
        
        $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        // For debugging, it's helpful to see the error. In production, you would log this.
        $error_message = "Search operation failed. Please try again later.";
        // Log the detailed error for your own review
        error_log("Search operation failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>" class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full mb-4 px-2">
                    <input type="text" name="q" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>" class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
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

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-6">Search Results</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="text-center bg-white p-12 rounded-xl shadow-lg border border-red-200">
                <p class="text-2xl text-red-700 font-semibold"><?php echo htmlspecialchars($error_message); ?></p>
                <a href="index.php" class="mt-6 inline-block bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700">Back to All Recipes</a>
            </div>
        <?php elseif (empty($recipes) && !empty($search_term)): ?>
            <div class="text-center bg-white p-12 rounded-xl shadow-lg">
                 <p class="text-xl text-gray-600 mb-2">Found <?php echo count($recipes); ?> result(s) for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>
                <p class="text-2xl text-gray-700">Sorry, we couldn't find any recipes matching your search.</p>
                <p class="text-gray-500 mt-2">Try searching for a different keyword or check out our latest recipes!</p>
                <a href="index.php" class="mt-6 inline-block bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700">Back to All Recipes</a>
            </div>
        <?php else: ?>
             <p class="text-xl text-gray-600 mb-8">Found <?php echo count($recipes); ?> result(s) for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="block">
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-64 object-cover">
                            <div class="p-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center text-amber-500">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo (isset($recipe['average_rating']) && $i <= round($recipe['average_rating'])) ? 'text-amber-500' : 'text-gray-300'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">(<?php echo $recipe['rating_count'] ?? 0; ?>)</span>
                                </div>
                                <p class="text-gray-700 text-base line-clamp-3"><?php echo htmlspecialchars($recipe['description']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date("Y"); ?> <a href="https://www.chrisandemmashow.com" target="_blank" class="hover:underline">The Chris and Emma Show</a>. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // JavaScript for mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
