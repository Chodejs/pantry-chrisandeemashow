<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Check for status messages from delete action
$status_message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'deleted') {
        $status_message = "Recipe successfully deleted.";
    } elseif ($_GET['status'] == 'error') {
        $status_message = "Error: Could not delete the recipe.";
    }
}


try {
    // Fetch all recipes to display in the table
    $stmt = $pdo->prepare("
        SELECT 
            r.id, 
            r.title, 
            u.username AS author_name
        FROM recipes r
        LEFT JOIN users u ON r.author_id = u.id
        ORDER BY r.id DESC
    ");
    $stmt->execute();
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error fetching recipes: " . $e->getMessage());
}

$page_title = "Manage Recipes";
require_once 'includes/admin_header.php';
?>

<main class="container mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-gray-900">Manage Recipes</h1>
        <a href="admin_add_recipe.php" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg shadow-md">
            Add New Recipe
        </a>
    </div>

    <?php if ($status_message): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6" role="alert">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-4 sm:p-8 rounded-lg shadow-lg">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Author</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($recipes) > 0): ?>
                        <?php foreach ($recipes as $recipe): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($recipe['title']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo htmlspecialchars($recipe['author_name'] ?? 'N/A'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-4">
                                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>" target="_blank" class="text-blue-600 hover:text-blue-900">View</a>
                                        <a href="admin_edit_recipe.php?id=<?php echo $recipe['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="admin_delete_recipe.php" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this recipe? This cannot be undone.');">
                                             <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No recipes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
require_once 'includes/footer.php';
?>
