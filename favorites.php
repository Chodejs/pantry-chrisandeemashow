<?php
// Initialize the session
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include our database configuration file.
require_once "config.php";

$user_id = $_SESSION["id"];
$favorite_recipes = [];

// Fetch all recipes that the user has favorited
$sql = "SELECT r.id, r.title, r.description, r.image_url 
        FROM recipes r
        JOIN favorites f ON r.id = f.recipe_id
        WHERE f.user_id = ?
        ORDER BY r.title ASC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $favorite_recipes[] = $row;
        }
    } else {
        echo "Oops! Something went wrong retrieving your favorites.";
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
    <title>My Favorite Recipes | The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <a href="index.php" class="text-3xl font-extrabold text-green-700">The Plant-Powered Pantry</a>
            <nav class="space-x-4">
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <span class="font-semibold">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</span>
                    <a href="favorites.php" class="text-green-700 font-bold">My Favorites</a>
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
        <div class="text-center mb-12">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4">My Favorite Recipes</h1>
            <p class="text-xl text-gray-600">Your personal collection of go-to recipes. Happy cooking!</p>
        </div>
        
        <?php if (empty($favorite_recipes)): ?>
            <div class="text-center bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-700">Your collection is empty!</h2>
                <p class="text-gray-600 mt-2">Start exploring our recipes and click the "Add to Favorites" button to save them here.</p>
                <a href="index.php" class="mt-6 inline-block bg-green-600 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:bg-green-700 transition">Explore Recipes</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($favorite_recipes as $recipe): ?>
                    <a href="recipe.php?id=<?php echo $recipe['id']; ?>" class="block bg-white rounded-xl shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 hover:shadow-2xl">
                        <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h3>
                            <p class="text-gray-700"><?php echo htmlspecialchars($recipe['description']); ?></p>
                        </div>
                    </a>
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

