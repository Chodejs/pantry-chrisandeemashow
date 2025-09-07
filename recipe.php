<?php
// Initialize the session
session_start();
require_once "config.php";

// Check if recipe ID is set
if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: index.php");
    exit();
}

$recipe_id = trim($_GET["id"]);
$recipe = null;
$is_favorited = false;
$remixes = [];
$comments = []; // Array to hold comments

// Fetch the recipe details
$sql = "SELECT * FROM recipes WHERE id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $recipe_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $recipe = mysqli_fetch_assoc($result);
        } else {
            header("location: index.php");
            exit();
        }
    } else {
        echo "Oops! Something went wrong.";
    }
    mysqli_stmt_close($stmt);
}

// If user is logged in, check if this recipe is a favorite
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];
    $sql_fav = "SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?";
    if ($stmt_fav = mysqli_prepare($link, $sql_fav)) {
        mysqli_stmt_bind_param($stmt_fav, "ii", $user_id, $recipe_id);
        if (mysqli_stmt_execute($stmt_fav)) {
            mysqli_stmt_store_result($stmt_fav);
            if (mysqli_stmt_num_rows($stmt_fav) > 0) {
                $is_favorited = true;
            }
        }
        mysqli_stmt_close($stmt_fav);
    }
}

// Fetch approved remixes for this recipe
$sql_remix = "SELECT r.remix_title, r.notes, r.image_url, r.submitted_at, u.username 
              FROM remixes r
              JOIN users u ON r.user_id = u.id
              WHERE r.original_recipe_id = ? AND r.is_approved = 1
              ORDER BY r.submitted_at DESC";
if ($stmt_remix = mysqli_prepare($link, $sql_remix)) {
    mysqli_stmt_bind_param($stmt_remix, "i", $recipe_id);
    if (mysqli_stmt_execute($stmt_remix)) {
        $result_remix = mysqli_stmt_get_result($stmt_remix);
        while ($row = mysqli_fetch_assoc($result_remix)) {
            $remixes[] = $row;
        }
    }
    mysqli_stmt_close($stmt_remix);
}

// Fetch comments for this recipe
$sql_comments = "SELECT c.comment_text, c.image_url, c.created_at, u.username
                 FROM comments c
                 JOIN users u ON c.user_id = u.id
                 WHERE c.recipe_id = ?
                 ORDER BY c.created_at DESC";
if($stmt_comments = mysqli_prepare($link, $sql_comments)){
    mysqli_stmt_bind_param($stmt_comments, "i", $recipe_id);
    if(mysqli_stmt_execute($stmt_comments)){
        $result_comments = mysqli_stmt_get_result($stmt_comments);
        while($row = mysqli_fetch_assoc($result_comments)){
            $comments[] = $row;
        }
    }
    mysqli_stmt_close($stmt_comments);
}


mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> | The Plant-Powered Pantry</title>
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
                    <input type="search" name="q" placeholder="Search recipes..." class="px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
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
        <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">
            
            <?php if(isset($_GET['remix_submitted']) && $_GET['remix_submitted'] == 'true'): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded-r-lg" role="alert">
                    <p class="font-bold">Thank You!</p>
                    <p>Your recipe remix has been submitted for approval. We'll review it shortly!</p>
                </div>
            <?php endif; ?>

            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full h-96 object-cover rounded-lg mb-8">
            <h1 class="text-5xl font-extrabold text-gray-900 mb-4"><?php echo htmlspecialchars($recipe['title']); ?></h1>
            <p class="text-xl text-gray-600 mb-8 break-words"><?php echo htmlspecialchars($recipe['description']); ?></p>

            <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <div class="flex space-x-4 mb-8">
                    <form action="toggle_favorite.php" method="post" class="inline">
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                        <button type="submit" class="bg-amber-500 text-white font-semibold py-2 px-6 rounded-lg hover:bg-amber-600 transition">
                            <?php echo $is_favorited ? '★ Unfavorite' : '☆ Add to Favorites'; ?>
                        </button>
                    </form>
                    <a href="submit_remix.php?recipe_id=<?php echo $recipe['id']; ?>" class="bg-indigo-500 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-600 transition">
                        Share a Remix!
                    </a>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div class="text-center bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500 font-semibold">Prep Time</span>
                    <p class="text-2xl font-bold"><?php echo htmlspecialchars($recipe['prep_time']); ?> mins</p>
                </div>
                <div class="text-center bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500 font-semibold">Cook Time</span>
                    <p class="text-2xl font-bold"><?php echo htmlspecialchars($recipe['cook_time']); ?> mins</p>
                </div>
                <div class="text-center bg-gray-50 p-4 rounded-lg">
                    <span class="text-gray-500 font-semibold">Yields</span>
                    <p class="text-2xl font-bold"><?php echo htmlspecialchars($recipe['yields']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <div>
                    <h2 class="text-3xl font-bold mb-4">Ingredients</h2>
                    <ul class="list-disc list-inside space-y-2 text-lg">
                        <?php foreach (json_decode($recipe['ingredients']) as $ingredient): ?>
                            <li><?php echo htmlspecialchars($ingredient); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h2 class="text-3xl font-bold mb-4">Instructions</h2>
                    <ol class="list-decimal list-inside space-y-4 text-lg">
                         <?php foreach (json_decode($recipe['instructions']) as $instruction): ?>
                            <li><?php echo htmlspecialchars($instruction); ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
            
            <?php if (!empty($recipe['nutrition_info']) || !empty($recipe['notes'])): ?>
            <div class="mt-12 border-t pt-8">
                 <h2 class="text-3xl font-bold mb-4">Additional Information</h2>
                <?php if (!empty($recipe['nutrition_info'])): ?>
                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Nutrition Facts (Summary)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <?php
                        // Decode the JSON nutrition info into an associative array
                        $nutrition_data = json_decode($recipe['nutrition_info'], true);
                        if (is_array($nutrition_data)):
                            foreach ($nutrition_data as $key => $value):
                        ?>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <p class="text-sm font-semibold text-green-800"><?php echo htmlspecialchars($key); ?></p>
                                <p class="text-2xl font-bold text-green-900 break-words"><?php echo htmlspecialchars($value); ?></p>
                            </div>
                        <?php 
                            endforeach; 
                        endif;
                        ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($recipe['notes'])): ?>
                    <h3 class="text-xl font-semibold text-gray-700 mt-6 mb-4">Chef's Notes</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700 text-lg break-words">
                        <?php 
                        // Intelligently split the notes into sentences for a cleaner list
                        $notes_list = preg_split('/(?<=[.?!])\s+/', trim($recipe['notes']), -1, PREG_SPLIT_NO_EMPTY);
                        foreach ($notes_list as $note): 
                        ?>
                            <li><?php echo htmlspecialchars(trim($note)); ?></li>
                        <?php 
                        endforeach; 
                        ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="mt-12 border-t pt-8">
                <h2 class="text-4xl font-bold mb-6">Community Remixes</h2>
                <?php if (empty($remixes)): ?>
                    <p class="text-gray-600 text-center bg-gray-50 p-6 rounded-lg">Be the first to share a remix for this recipe!</p>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach($remixes as $remix): ?>
                        <div class="bg-gray-50 p-6 rounded-xl shadow">
                            <h3 class="text-2xl font-bold text-amber-700"><?php echo htmlspecialchars($remix['remix_title']); ?></h3>
                            <p class="text-sm text-gray-500 mb-3">Submitted by <?php echo htmlspecialchars($remix['username']); ?> on <?php echo date("F j, Y", strtotime($remix['submitted_at'])); ?></p>
                            <?php if (!empty($remix['image_url'])): ?>
                                <img src="<?php echo htmlspecialchars($remix['image_url']); ?>" alt="Remix photo by <?php echo htmlspecialchars($remix['username']); ?>" class="my-4 rounded-lg max-w-sm shadow-sm">
                            <?php endif; ?>
                            <p class="text-gray-700 whitespace-pre-line break-words"><?php echo htmlspecialchars($remix['notes']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-12 border-t pt-8">
                <h2 class="text-4xl font-bold mb-6">Community Comments</h2>
                
                <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                <div class="bg-gray-50 p-6 rounded-xl shadow mb-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Share Your Thoughts!</h3>
                    <form action="add_comment.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                        <div class="mb-4">
                            <label for="comment_text" class="sr-only">Your Comment</label>
                            <textarea name="comment_text" id="comment_text" rows="4" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Made this recipe? Share your experience..." required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="comment_image" class="block text-gray-700 font-semibold mb-2">Share a Photo (Optional)</label>
                            <input type="file" name="comment_image" id="comment_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        </div>
                        <div class="text-right">
                            <button type="submit" class="bg-green-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-green-700">Post Comment</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="space-y-6">
                    <?php if (empty($comments)): ?>
                        <p class="text-gray-600 text-center">No comments yet. Be the first to share your feedback!</p>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-200 rounded-full flex items-center justify-center">
                                <span class="text-xl font-bold text-green-700"><?php echo strtoupper(substr($comment['username'], 0, 1)); ?></span>
                            </div>
                            <div class="flex-1 bg-white p-4 rounded-xl border">
                                <p class="font-bold text-gray-900"><?php echo htmlspecialchars($comment['username']); ?></p>
                                <p class="text-sm text-gray-500 mb-2"><?php echo date("F j, Y, g:i a", strtotime($comment['created_at'])); ?></p>
                                <p class="text-gray-800 mb-3 break-words"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>
                                <?php if (!empty($comment['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($comment['image_url']); ?>" alt="User comment photo" class="mt-3 rounded-lg max-w-sm shadow-sm">
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
    <footer class="bg-gray-800 text-white py-6 text-center mt-12">
        <div class="container mx-auto px-4">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> The Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

