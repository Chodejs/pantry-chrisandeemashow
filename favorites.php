<?php
session_start();
require_once 'config.php';

// If the user is not logged in, they can't see their favorites.
// Redirect them to the login page with a little message.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?message=Please log in to view your favorites.");
    exit();
}

$user_id = $_SESSION['user_id'];
$favorites = [];

try {
    // Prepare a statement to select all recipes that the current user has favorited.
    // We join the recipes and favorites tables to link them together.
    $stmt = $pdo->prepare(
        "SELECT r.* FROM recipes r 
         JOIN favorites f ON r.id = f.recipe_id 
         WHERE f.user_id = ?
         ORDER BY r.title ASC"
    );
    $stmt->execute([$user_id]);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // A graceful exit in case of a database error.
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navigation Bar -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-green-600">The Plant-Powered Pantry</a>
            <div class="flex items-center space-x-4">
                <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full">
                    <input type="text" name="query" placeholder="Search..." class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
                    <button type="submit" class="text-gray-500 p-2 rounded-full hover:text-green-500"><i class="fas fa-search"></i></button>
                </form>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="favorites.php" class="text-green-600 font-semibold border-b-2 border-green-600">My Favorites</a>
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
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8">My Favorite Recipes</h1>

        <?php if (count($favorites) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($favorites as $recipe): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>">
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-64 object-cover">
                        </a>
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h2>
                            <p class="text-gray-600 mb-4 line-clamp-3"><?php echo htmlspecialchars($recipe['description']); ?></p>
                            <div class="flex items-center">
                                <div class="flex items-center text-amber-500">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo ($i <= round($recipe['average_rating'])) ? 'text-amber-500' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 text-sm text-gray-500">(<?php echo $recipe['rating_count']; ?>)</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center bg-white p-10 rounded-lg shadow">
                <p class="text-xl text-gray-500">You haven't saved any favorites yet.</p>
                <a href="index.php" class="mt-4 inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg">Explore Recipes</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

