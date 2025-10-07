<?php
session_start();
require_once 'config.php';

// --- Pagination Logic by Emma ---
// 1. Define how many recipes to show per page
$recipes_per_page = 9;

// 2. Determine the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}

// 3. Get the total number of recipes to calculate total pages
try {
    $total_recipes_stmt = $pdo->query("SELECT COUNT(*) FROM recipes");
    $total_recipes = $total_recipes_stmt->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// 4. Calculate total pages
$total_pages = ceil($total_recipes / $recipes_per_page);

// 5. Calculate the offset for the SQL query
$offset = ($page - 1) * $recipes_per_page;

// 6. Fetch a specific 'page' of recipes from the database
try {
    // **EMMA'S FIX:** Changed 'ORDER BY created_at' to 'ORDER BY id' to match the database schema.
    $stmt = $pdo->prepare("SELECT id, title, description, image_url, average_rating, rating_count FROM recipes ORDER BY id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $recipes_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $recipes = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Could not fetch recipes: " . $e->getMessage());
}

// Set the page title for the header
$page_title = "Home";

// Include the new header
require_once 'includes/header.php';
?>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
        <!-- Hero Section -->
        <section class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4">Welcome to Our Plant-Powered Pantry</h1>
            <p class="text-xl text-gray-600">Discover a growing collection of delicious, wholesome, and plant-powered recipes from our kitchen to yours.</p>
             <!-- Search Bar -->
            <div class="mt-8 max-w-lg mx-auto">
                <form action="search.php" method="GET" class="flex items-center bg-white rounded-full shadow-lg">
                    <input type="text" name="q" placeholder="Search recipes..." class="w-full py-3 px-6 rounded-full focus:outline-none text-gray-700">
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
                            <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="block">
                                <div class="aspect-video bg-gray-200">
                                    <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-full object-cover object-center">
                                </div>
                            </a>
                            <div class="p-6">
                                <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <div class="flex items-center mb-4">
                                    <div class="flex items-center text-amber-500">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?php echo (isset($recipe['average_rating']) && $i <= round($recipe['average_rating'])) ? 'text-amber-500' : 'text-gray-300'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-500">(<?php echo $recipe['rating_count'] ?? 0; ?>)</span>
                                </div>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($recipe['description']); ?></p>
                                <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="inline-block bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-md font-semibold">View Recipe</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-gray-500 col-span-3">No recipes found on this page.</p>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Pagination Links -->
        <?php if ($total_pages > 1): ?>
        <section class="mt-12">
            <div class="flex justify-center items-center space-x-1">
                <!-- Previous Button -->
                <?php if ($page > 1): ?>
                    <a href="index.php?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">&laquo; Previous</a>
                <?php endif; ?>

                <!-- Page Numbers -->
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo ($i == $page) ? 'bg-green-500 text-white border-green-500' : 'bg-white border border-gray-300 text-gray-700'; ?> rounded-md hover:bg-gray-50 hover:text-gray-900"><?php echo $i; ?></a>
                <?php endfor; ?>

                <!-- Next Button -->
                <?php if ($page < $total_pages): ?>
                    <a href="index.php?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">Next &raquo;</a>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

    </main>

<?php
// Include the new footer
require_once 'includes/footer.php';
?>
