<?php
session_start();
require_once "config.php";

// Redirect if no search query is provided
if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    header("location: index.php");
    exit;
}

$search_term = trim($_GET['q']);
$recipes = [];

// Prepare the search query
$sql = "SELECT id, title, description, image_url FROM recipes WHERE title LIKE ? OR description LIKE ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    $param_term = "%" . $search_term . "%";
    mysqli_stmt_bind_param($stmt, "ss", $param_term, $param_term);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $recipes[] = $row;
        }
    } else {
        echo "Oops! Something went wrong executing the search.";
    }
    mysqli_stmt_close($stmt);
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results for "<?php echo htmlspecialchars($search_term); ?>"</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-extrabold text-green-700">The Plant-Powered Pantry</a>
            <nav class="space-x-4 flex items-center">
                <!-- Search Bar -->
                <form action="search.php" method="get" class="relative hidden sm:block">
                    <input type="search" name="q" placeholder="Search recipes..." class="px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500" value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-green-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" /></svg>
                    </button>
                </form>

                 <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <span class="font-semibold hidden lg:inline">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</span>
                    <a href="favorites.php" class="text-gray-600 hover:text-green-700">My Favorites</a>
                     <?php if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true): ?>
                        <a href="admin_remixes.php" class="text-red-600 font-bold hover:text-red-700">Admin Panel</a>
                    <?php endif; ?>
                    <a href="logout.php" class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-green-700">Login</a>
                    <a href="register.php" class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-6">Search Results</h1>
        <p class="text-xl text-gray-600 mb-8">Found <?php echo count($recipes); ?> result(s) for "<strong><?php echo htmlspecialchars($search_term); ?></strong>"</p>

        <?php if (empty($recipes)): ?>
            <div class="text-center bg-white p-12 rounded-xl shadow-lg">
                <p class="text-2xl text-gray-700">Sorry, we couldn't find any recipes matching your search.</p>
                <p class="text-gray-500 mt-2">Try searching for a different keyword or check out our latest recipes below!</p>
                <a href="index.php" class="mt-6 inline-block bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700">Back to All Recipes</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($recipes as $recipe): ?>
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                        <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="block">
                            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <h3 class="text-2xl font-bold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                                <p class="text-gray-700 text-base line-clamp-3"><?php echo htmlspecialchars($recipe['description']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gray-800 text-white py-6 text-center mt-12">
        <div class="container mx-auto px-4">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> The Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
