<?php
session_start();
require_once 'config.php';

// Get recipe ID from URL
$recipe_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recipe_id === 0) {
    header("Location: index.php");
    exit();
}

// Construct the full URL for sharing
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$current_page_url = $protocol . "://" . $host . $_SERVER['REQUEST_URI'];

try {
    // Fetch the main recipe
    $stmt = $pdo->prepare("SELECT r.*, u.username AS author_name FROM recipes r JOIN users u ON r.author_id = u.id WHERE r.id = ?");
    $stmt->execute([$recipe_id]);
    $recipe = $stmt->fetch();

    if (!$recipe) {
        header("Location: index.php");
        exit();
    }
    
    // Fetch comments, remixes, etc.
    $commentStmt = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.recipe_id = ? ORDER BY c.created_at DESC");
    $commentStmt->execute([$recipe_id]);
    $comments = $commentStmt->fetchAll();

    $remixStmt = $pdo->prepare("SELECT r.*, u.username FROM remixes r JOIN users u ON r.user_id = u.id WHERE r.original_recipe_id = ? AND r.is_approved = 1 ORDER BY r.submitted_at DESC");
    $remixStmt->execute([$recipe_id]);
    $remixes = $remixStmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$is_favorited = false;
$user_rating = 0; // Default to 0, meaning not rated
if (isset($_SESSION['user_id'])) {
    // Check for favorite
    $favStmt = $pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $favStmt->execute([$_SESSION['user_id'], $recipe_id]);
    if ($favStmt->fetchColumn() > 0) {
        $is_favorited = true;
    }

    // Check for user's own rating
    $ratingStmt = $pdo->prepare("SELECT rating FROM ratings WHERE user_id = ? AND recipe_id = ?");
    $ratingStmt->execute([$_SESSION['user_id'], $recipe_id]);
    $result = $ratingStmt->fetch();
    if ($result) {
        $user_rating = $result['rating'];
    }
}


// Prepare variables for sharing
$share_url = urlencode($current_page_url);
$share_title = urlencode($recipe['title']);
$share_description = urlencode($recipe['description']);
$share_image = urlencode($protocol . "://" . $host . "/" . ltrim($recipe['image_url'], '/'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recipe['title']); ?> - The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .word-wrap-break { word-wrap: break-word; overflow-wrap: break-word; }
        .rating-stars input[type="radio"] { display: none; }
        .rating-stars label {
            font-size: 2.5rem;
            color: #d1d5db; /* gray-300 */
            cursor: pointer;
            transition: color 0.2s;
        }
        .rating-stars input[type="radio"]:checked ~ label,
        .rating-stars:hover label,
        .rating-stars label:hover ~ label {
            color: #f59e0b; /* amber-500 */
        }
        .rating-stars input[type="radio"]:hover ~ label {
             color: #f59e0b;
        }
        .rating-stars label:hover {
            color: #f59e0b !important;
        }

        /* Print-specific styles */
        @media print {
            body {
                background-color: white;
            }
            header, footer, .no-print {
                display: none !important;
            }
            main {
                padding: 0;
            }
            article {
                box-shadow: none;
                border: 1px solid #ccc;
                max-width: 100%;
            }
            img {
                max-width: 50% !important; /* Make image smaller for print */
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <!-- Navigation Bar -->
    <header class="bg-white shadow-md no-print sticky top-0 z-50">
        <nav class="container mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-green-600">The Plant-Powered Pantry</a>
            
            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-4">
                <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full">
                    <input type="text" name="query" placeholder="Search..." class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
                    <button type="submit" class="text-gray-500 p-2 rounded-full hover:text-green-500">
                        <i class="fas fa-search"></i>
                    </button>
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
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
            </div>
        </nav>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                 <form action="search.php" method="GET" class="flex items-center bg-gray-200 rounded-full mb-4 px-2">
                    <input type="text" name="query" placeholder="Search..." class="w-full py-2 px-4 rounded-full focus:outline-none bg-transparent text-gray-700">
                    <button type="submit" class="text-gray-500 p-2 rounded-full hover:text-green-500">
                        <i class="fas fa-search"></i>
                    </button>
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

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-12">
        
        <?php if (isset($_GET['remix_submitted']) && $_GET['remix_submitted'] == 'true'): ?>
        <div class="max-w-4xl mx-auto bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-md mb-6" role="alert">
            <p class="font-bold">Thank You!</p>
            <p>Your remix has been submitted successfully and is now awaiting approval from our team.</p>
        </div>
        <?php endif; ?>

        <div class="max-w-4xl mx-auto mb-6 no-print">
             <a href="javascript:history.back()" class="inline-flex items-center text-gray-600 hover:text-green-700 font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Back to previous page
            </a>
        </div>

        <article class="bg-white p-8 rounded-lg shadow-lg max-w-4xl mx-auto">
            <img src="<?php echo htmlspecialchars($recipe['image_url']); ?>" alt="<?php echo htmlspecialchars($recipe['title']); ?>" class="w-full rounded-lg mb-6">
            
            <h1 class="text-4xl font-extrabold text-gray-900 mb-2"><?php echo htmlspecialchars($recipe['title']); ?></h1>
            
            <div class="flex items-center mb-4">
                <div class="flex items-center">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo ($i <= round($recipe['average_rating'])) ? 'text-amber-500' : 'text-gray-300'; ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="ml-2 text-gray-600 text-sm font-semibold">
                    <?php echo number_format($recipe['average_rating'], 1); ?> out of 5 
                    <span class="ml-1">(<?php echo $recipe['rating_count']; ?> votes)</span>
                </p>
            </div>

            <p class="text-lg text-gray-600 mb-6"><?php echo htmlspecialchars($recipe['description']); ?></p>

             <div class="flex flex-wrap items-center justify-center gap-4 mb-8 no-print">
                <form action="toggle_favorite.php" method="POST" class="flex-shrink-0">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                    <button type="submit" class="px-6 py-2 rounded-md font-semibold flex items-center space-x-2 <?php echo $is_favorited ? 'bg-pink-500 text-white' : 'bg-pink-100 text-pink-800'; ?> hover:bg-pink-200 transition-colors">
                        <i class="fas fa-heart"></i>
                        <span><?php echo $is_favorited ? 'Favorited!' : 'Add to Favorites'; ?></span>
                    </button>
                </form>
                <a href="submit_remix.php?recipe_id=<?php echo $recipe['id']; ?>" class="flex-shrink-0 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md font-semibold flex items-center space-x-2">
                    <i class="fas fa-lightbulb"></i>
                    <span>Share a Remix!</span>
                </a>
                <button onclick="window.print()" class="flex-shrink-0 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-semibold flex items-center space-x-2">
                    <i class="fas fa-print"></i>
                    <span>Print Recipe</span>
                </button>
                <a href="#" class="flex-shrink-0 bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md font-semibold flex items-center space-x-2">
                    <i class="fas fa-hand-holding-dollar"></i>
                    <span>Donate</span>
                </a>
            </div>

             <div class="bg-gray-50 p-4 rounded-lg mb-8 no-print">
                <h3 class="font-semibold text-center text-gray-700 mb-3">Share this Recipe</h3>
                <div class="flex justify-center items-center gap-3 flex-wrap">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors" aria-label="Share on Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-black text-white hover:bg-gray-800 transition-colors" aria-label="Share on X">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://pinterest.com/pin/create/button/?url=<?php echo $share_url; ?>&media=<?php echo $share_image; ?>&description=<?php echo $share_description; ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-red-600 text-white hover:bg-red-700 transition-colors" aria-label="Share on Pinterest">
                        <i class="fab fa-pinterest-p"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors" aria-label="Share on WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <button id="copy-link-btn" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-500 text-white hover:bg-gray-600 transition-colors" aria-label="Copy Link">
                        <i class="fas fa-link"></i>
                    </button>
                    <span id="copy-feedback" class="text-sm text-green-600 font-semibold opacity-0 transition-opacity">Copied!</span>
                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
            <div class="bg-amber-50 p-4 rounded-lg mb-8 no-print">
                 <h3 class="font-semibold text-center text-amber-800 mb-2"><?php echo $user_rating > 0 ? 'You rated this recipe:' : 'Rate this recipe!'; ?></h3>
                <form action="submit_rating.php" method="POST" class="flex justify-center">
                    <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                    <div class="rating-stars flex flex-row-reverse">
                        <input type="radio" id="star5" name="rating" value="5" <?php if ($user_rating == 5) echo 'checked'; ?> onchange="this.form.submit()"><label for="star5" title="5 stars">★</label>
                        <input type="radio" id="star4" name="rating" value="4" <?php if ($user_rating == 4) echo 'checked'; ?> onchange="this.form.submit()"><label for="star4" title="4 stars">★</label>
                        <input type="radio" id="star3" name="rating" value="3" <?php if ($user_rating == 3) echo 'checked'; ?> onchange="this.form.submit()"><label for="star3" title="3 stars">★</label>
                        <input type="radio" id="star2" name="rating" value="2" <?php if ($user_rating == 2) echo 'checked'; ?> onchange="this.form.submit()"><label for="star2" title="2 stars">★</label>
                        <input type="radio" id="star1" name="rating" value="1" <?php if ($user_rating == 1) echo 'checked'; ?> onchange="this.form.submit()"><label for="star1" title="1 star">★</label>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="flex justify-around bg-gray-50 p-4 rounded-lg mb-8 text-center">
                <div>
                    <h4 class="font-bold text-gray-500 text-sm">Prep Time</h4>
                    <p class="text-xl font-semibold"><?php echo htmlspecialchars($recipe['prep_time']); ?> mins</p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-500 text-sm">Cook Time</h4>
                    <p class="text-xl font-semibold"><?php echo htmlspecialchars($recipe['cook_time']); ?> mins</p>
                </div>
                <div>
                    <h4 class="font-bold text-gray-500 text-sm">Yields</h4>
                    <p class="text-xl font-semibold"><?php echo htmlspecialchars($recipe['yields']); ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <div>
                    <h2 class="text-2xl font-bold mb-4">Ingredients</h2>
                    <div class="prose max-w-none">
                        <?php
                            // **EMMA'S ROBUST FIX:** Check if data is JSON or plain text.
                            $ingredients_raw = $recipe['ingredients'];
                            $ingredients_array = json_decode($ingredients_raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($ingredients_array)) {
                                echo '<ul class="list-disc list-inside space-y-2 text-gray-700">';
                                foreach ($ingredients_array as $item) {
                                    echo '<li>' . htmlspecialchars($item) . '</li>';
                                }
                                echo '</ul>';
                            } else {
                                echo nl2br(htmlspecialchars($ingredients_raw));
                            }
                        ?>
                    </div>
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-4">Instructions</h2>
                    <div class="prose max-w-none">
                         <?php
                            // **EMMA'S ROBUST FIX:** Check if data is JSON or plain text.
                            $instructions_raw = $recipe['instructions'];
                            $instructions_array = json_decode($instructions_raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($instructions_array)) {
                                echo '<ol class="list-decimal list-inside space-y-4 text-gray-700">';
                                foreach ($instructions_array as $item) {
                                    echo '<li>' . htmlspecialchars($item) . '</li>';
                                }
                                echo '</ol>';
                            } else {
                                echo nl2br(htmlspecialchars($instructions_raw));
                            }
                        ?>
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 p-6 rounded-lg mb-8">
                <h2 class="text-2xl font-bold mb-4">Additional Information</h2>
                <div class="mb-6">
                    <h3 class="text-xl font-semibold mb-3">Nutrition Facts (Summary)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-center">
                    <?php
                        // **EMMA'S ROBUST FIX:** Check if data is JSON or plain text.
                        $nutrition_info_raw = $recipe['nutrition_info'];
                        $nutrition_info_array = json_decode($nutrition_info_raw, true);

                        if (json_last_error() === JSON_ERROR_NONE && is_array($nutrition_info_array)) {
                            foreach($nutrition_info_array as $key => $value) {
                                echo '<div class="bg-white p-3 rounded-lg shadow-sm">';
                                echo '<h4 class="font-semibold text-gray-500 text-sm">' . htmlspecialchars($key) . '</h4>';
                                echo '<p class="text-lg font-bold text-green-600">' . htmlspecialchars($value) . '</p>';
                                echo '</div>';
                            }
                        } elseif (!empty($nutrition_info_raw)) {
                             echo '<div class="bg-white p-3 rounded-lg shadow-sm col-span-full text-left">';
                             echo '<p class="text-gray-700">' . nl2br(htmlspecialchars($nutrition_info_raw)) . '</p>';
                             echo '</div>';
                        } else {
                            echo '<p class="text-gray-500 col-span-full">No nutrition information available.</p>';
                        }
                    ?>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-semibold mb-3">Chef's Notes</h3>
                    <div class="prose max-w-none">
                        <?php 
                            if (!empty($recipe['notes'])) {
                                echo nl2br(htmlspecialchars($recipe['notes']));
                            } else {
                                echo '<p>No special notes for this recipe.</p>';
                            }
                        ?>
                    </div>
                </div>
            </div>

            <div class="pt-8 border-t no-print">
                <h2 class="text-3xl font-bold mb-6 text-center">Community Remixes</h2>
                 <?php if (count($remixes) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach($remixes as $remix): ?>
                            <div class="bg-blue-50 p-4 rounded-lg shadow flex items-start space-x-4">
                                <?php if (!empty($remix['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($remix['image_url']); ?>" class="w-24 h-24 object-cover rounded-md" alt="Remix Image">
                                <?php endif; ?>
                                <div class="flex-1">
                                    <h4 class="font-bold text-lg text-blue-800"><?php echo htmlspecialchars($remix['remix_title']); ?></h4>
                                    <p class="text-sm text-gray-500 mb-2">by <?php echo htmlspecialchars($remix['username']); ?></p>
                                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($remix['notes']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500">Be the first to share a remix for this recipe!</p>
                <?php endif; ?>
            </div>

            <div class="pt-8 mt-8 border-t no-print">
                <h2 class="text-3xl font-bold mb-6 text-center">Community Comments</h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="add_comment.php" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-lg shadow mb-8">
                        <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
                        <h3 class="text-xl font-semibold mb-4">Share Your Thoughts</h3>
                        <textarea name="comment" rows="4" class="w-full p-3 border rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Made this recipe? Share your experience!" required></textarea>
                        <div class="mt-4">
                            <label for="comment_image" class="block text-sm font-medium text-gray-700">Share a photo (optional):</label>
                            <input type="file" name="comment_image" id="comment_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        </div>
                        <div class="text-right mt-4">
                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-6 rounded-lg">Post Comment</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-center text-gray-600 bg-gray-100 p-4 rounded-lg">You must be <a href="login.php" class="text-green-600 font-semibold hover:underline">logged in</a> to post a comment.</p>
                <?php endif; ?>

                <div class="space-y-6">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach($comments as $comment): ?>
                            <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex items-start space-x-4">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($comment['username']); ?></p>
                                    <p class="text-xs text-gray-500 mb-2"><?php echo date('F j, Y, g:i a', strtotime($comment['created_at'])); ?></p>
                                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($comment['comment_text']); ?></p>
                                    <?php if (!empty($comment['image_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($comment['image_url']); ?>" class="mt-3 rounded-lg max-w-xs" alt="User submitted photo">
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <p class="text-center text-gray-500">No comments yet. Be the first to share your thoughts!</p>
                    <?php endif; ?>
                </div>
            </div>
        </article>
    </main>

    <footer class="bg-gray-800 text-white py-8 mt-12 no-print">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // JavaScript for mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // JavaScript for the copy link button
        const copyBtn = document.getElementById('copy-link-btn');
        const feedbackSpan = document.getElementById('copy-feedback');
        
        copyBtn.addEventListener('click', () => {
            const urlToCopy = '<?php echo $current_page_url; ?>';

            navigator.clipboard.writeText(urlToCopy).then(() => {
                feedbackSpan.style.opacity = '1';
                setTimeout(() => {
                    feedbackSpan.style.opacity = '0';
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        });
    </script>

</body>
</html>

