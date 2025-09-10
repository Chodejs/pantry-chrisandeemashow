<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

try {
    // Fetch all remixes, joining with recipe and user tables to get names
    $stmt = $pdo->prepare("
        SELECT 
            r.id, 
            r.remix_title, 
            r.notes, 
            r.submitted_at, 
            r.is_approved,
            rec.title AS original_recipe_title,
            u.username AS submitter_username
        FROM remixes r
        JOIN recipes rec ON r.original_recipe_id = rec.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.submitted_at DESC
    ");
    $stmt->execute();
    $remixes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error fetching remixes: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Remix Moderation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">

    <!-- Admin Navigation -->
    <header class="bg-white shadow-md">
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="text-2xl font-bold text-gray-800">Admin Dashboard</div>
                <div class="flex items-center space-x-4">
                    <a href="admin_remixes.php" class="text-green-600 font-semibold border-b-2 border-green-600">Remix Moderation</a>
                    <a href="admin_add_recipe.php" class="text-gray-600 hover:text-green-600">Add a Recipe</a>
                    <a href="index.php" class="text-gray-600 hover:text-green-600">Back to Site</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Remix Moderation Queue</h1>

        <div class="bg-white p-8 rounded-lg shadow-lg">
            <?php if (count($remixes) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted By</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remix Title</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original Recipe</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted On</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($remixes as $remix): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($remix['submitter_username']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($remix['remix_title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($remix['original_recipe_title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y', strtotime($remix['submitted_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($remix['is_approved']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if (!$remix['is_approved']): ?>
                                            <a href="approve_remix.php?id=<?php echo $remix['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Approve</a>
                                        <?php endif; ?>
                                        <a href="delete_remix.php?id=<?php echo $remix['id']; ?>" class="text-red-600 hover:text-red-900 ml-4">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500">There are no pending remixes to moderate. Good job!</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>

