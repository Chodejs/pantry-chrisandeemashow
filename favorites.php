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

// Set the page title for the header
$page_title = "My Favorites";

// Include the new header
require_once 'includes/header.php';
?>
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
                                        <i class="fas fa-star <?php echo (isset($recipe['average_rating']) && $i <= round($recipe['average_rating'])) ? 'text-amber-500' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="ml-2 text-sm text-gray-500">(<?php echo $recipe['rating_count'] ?? 0; ?>)</span>
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

<?php
// Include the new footer
require_once 'includes/footer.php';
?>
