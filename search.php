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

// Set the page title for the header
$page_title = 'Search Results for "' . htmlspecialchars($search_term) . '"';

// Include the new header
require_once 'includes/header.php';
?>

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

<?php
// Include the new footer
require_once 'includes/footer.php';
?>
