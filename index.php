<?php
session_start();
require_once 'config.php';

// Fetch all recipes from the database
try {
    $stmt = $pdo->query("SELECT id, title, description, image_url FROM recipes ORDER BY id DESC");
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    // We'll just die for now, but in a real app, show a friendly error page.
    die("Could not connect to the database and fetch recipes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navigation Bar -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-green-600">The Plant-Powered Pantry</a>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
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
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
        <!-- Hero Section -->
        <section class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4">Welcome to the Pantry</h1>
            <p class="text-xl text-gray-600">Discover a growing collection of delicious, wholesome, and plant-powered recipes from our kitchen to yours.</p>
             <!-- Search Bar -->
            <div class="mt-8 max-w-lg mx-auto">
                <form action="search.php" method="GET" class="flex items-center bg-white rounded-full shadow-lg">
                    <input type="text" name="query" placeholder="Search recipes..." class="w-full py-3 px-6 rounded-full focus:outline-none text-gray-700">
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white p-3 rounded-full -ml-12">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </section>

        <!-- Recipe Grid -->
        <section>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php if (count($recipes) > 0): ?>
                    <?php foreach ($recipes as $recipe): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden transform hover:scale-105 transition-transform duration-300">
                            <a href="recipe.php?id=<?php echo $recipe['id']; ?>">
                                <!-- **MODIFICATION HERE**: Changed height from h-48 to h-64 for better image visibility -->
                                <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-64 object-cover">
                            </a>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars(substr($recipe['description'], 0, 100)) . '...'; ?></p>
                                <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="inline-block bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-md font-semibold">View Recipe</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 col-span-3">No recipes found. Why not be the first to add one?</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

