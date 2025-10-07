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
            rec.id AS original_recipe_id,
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

// Set the page title for the admin header
$page_title = "Remix Moderation";

// Include the new admin header
require_once 'includes/admin_header.php';
?>

    <main class="container mx-auto px-6 py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Remix Moderation Queue</h1>

        <div class="bg-white p-4 sm:p-8 rounded-lg shadow-lg">
            <?php if (count($remixes) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remix Details</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Submitted On</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($remixes as $remix): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-normal text-sm font-medium text-gray-900">
                                        <div class="font-bold"><?php echo htmlspecialchars($remix['remix_title']); ?></div>
                                        <div class="text-gray-500">by <?php echo htmlspecialchars($remix['submitter_username']); ?></div>
                                        <div class="text-xs text-blue-600 mt-1">
                                            Original: <a href="recipe.php?id=<?php echo $remix['original_recipe_id']; ?>" target="_blank" class="hover:underline"><?php echo htmlspecialchars($remix['original_recipe_title']); ?></a>
                                        </div>
                                         <div class="text-xs text-gray-500 sm:hidden mt-1"><?php echo date('M d, Y', strtotime($remix['submitted_at'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo date('M d, Y', strtotime($remix['submitted_at'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($remix['is_approved']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-4">
                                            <?php if (!$remix['is_approved']): ?>
                                                <form action="approve_remix.php" method="POST" onsubmit="return confirm('Are you sure you want to approve this remix?');">
                                                    <input type="hidden" name="remix_id" value="<?php echo $remix['id']; ?>">
                                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900">Approve</button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="delete_remix.php" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this remix?');">
                                                 <input type="hidden" name="remix_id" value="<?php echo $remix['id']; ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8">There are no pending remixes to moderate. Good job!</p>
            <?php endif; ?>
        </div>
    </main>

<?php
// We can use the same footer for admin pages
require_once 'includes/footer.php';
?>
