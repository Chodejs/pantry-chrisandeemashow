<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an admin, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("location: login.php");
    exit;
}

// Include our database configuration file.
require_once "config.php";

// Fetch all remixes that are pending approval (is_approved = 0)
$pending_remixes = [];
$sql = "SELECT r.id, r.remix_title, r.notes, r.submitted_at, u.username, rec.title as original_title
        FROM remixes r
        JOIN users u ON r.user_id = u.id
        JOIN recipes rec ON r.original_recipe_id = rec.id
        WHERE r.is_approved = 0
        ORDER BY r.submitted_at ASC";

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pending_remixes[] = $row;
    }
    mysqli_free_result($result);
}
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin: Moderate Remixes | The Plant-Powered Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-200 text-gray-800">
    <header class="bg-white shadow-md py-4">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div>
                <a href="index.php" class="text-2xl font-extrabold text-green-700">The Plant-Powered Pantry</a>
                <span class="ml-4 text-sm font-bold text-red-600">ADMIN PANEL</span>
            </div>
            <nav>
                <a href="logout.php" class="bg-red-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-red-600">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">Pending Remix Submissions</h1>
        
        <?php if (empty($pending_remixes)): ?>
            <div class="bg-white p-8 rounded-xl shadow-lg text-center">
                <h2 class="text-2xl font-semibold text-gray-700">All Clear!</h2>
                <p class="text-gray-600 mt-2">There are currently no pending remixes to review.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($pending_remixes as $remix): ?>
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h3 class="text-2xl font-bold text-amber-700"><?php echo htmlspecialchars($remix['remix_title']); ?></h3>
                        <p class="text-sm text-gray-500 mb-4">
                            For Recipe: <span class="font-semibold"><?php echo htmlspecialchars($remix['original_title']); ?></span><br>
                            Submitted by <span class="font-semibold"><?php echo htmlspecialchars($remix['username']); ?></span> on <?php echo date("F j, Y", strtotime($remix['submitted_at'])); ?>
                        </p>
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($remix['notes']); ?></p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <!-- Approve Form -->
                            <form action="approve_remix.php" method="post">
                                <input type="hidden" name="remix_id" value="<?php echo $remix['id']; ?>">
                                <button type="submit" class="bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition">Approve</button>
                            </form>
                            <!-- Delete Form -->
                            <form action="delete_remix.php" method="post" onsubmit="return confirm('Are you sure you want to permanently delete this submission?');">
                                <input type="hidden" name="remix_id" value="<?php echo $remix['id']; ?>">
                                <button type="submit" class="bg-gray-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-gray-600 transition">Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
