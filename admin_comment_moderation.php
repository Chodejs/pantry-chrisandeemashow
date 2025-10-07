<?php
session_start();
require_once 'config.php';

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

// Check for status messages from actions
$status_message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'approved') {
        $status_message = "Comment approved successfully.";
    } elseif ($_GET['status'] == 'deleted') {
        $status_message = "Comment deleted successfully.";
    } elseif ($_GET['status'] == 'error') {
        $status_message = "An error occurred.";
    }
}

try {
    // Fetch all comments with user and recipe information
    $stmt = $pdo->prepare("
        SELECT 
            c.id, 
            c.comment_text, 
            c.created_at, 
            c.is_approved,
            u.username AS author_username,
            r.title AS recipe_title,
            r.id AS recipe_id
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN recipes r ON c.recipe_id = r.id
        ORDER BY c.created_at DESC
    ");
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error fetching comments: " . $e->getMessage());
}

$page_title = "Comment Moderation";
require_once 'includes/admin_header.php';
?>

<main class="container mx-auto px-6 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Comment Moderation Queue</h1>

    <?php if ($status_message): ?>
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6" role="alert">
            <?php echo htmlspecialchars($status_message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white p-4 sm:p-8 rounded-lg shadow-lg">
        <?php if (count($comments) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment Details</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Submitted On</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-900">
                                    <p class="mb-2">"<?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>"</p>
                                    <div class="font-semibold text-gray-600">by <?php echo htmlspecialchars($comment['author_username']); ?></div>
                                    <div class="text-xs text-blue-600 mt-1">
                                        On Recipe: <a href="recipe.php?id=<?php echo $comment['recipe_id']; ?>" target="_blank" class="hover:underline"><?php echo htmlspecialchars($comment['recipe_title']); ?></a>
                                    </div>
                                     <div class="text-xs text-gray-500 sm:hidden mt-1"><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden sm:table-cell"><?php echo date('M d, Y', strtotime($comment['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($comment['is_approved']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-4">
                                        <?php if (!$comment['is_approved']): ?>
                                            <form action="admin_approve_comment.php" method="POST" onsubmit="return confirm('Are you sure you want to approve this comment?');">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900">Approve</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="admin_delete_comment.php" method="POST" onsubmit="return confirm('Are you sure you want to PERMANENTLY DELETE this comment?');">
                                             <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
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
            <p class="text-center text-gray-500 py-8">There are no comments to moderate. All clear!</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
