<?php
// THE ULTIMATE FIX: Start output buffering.
// This captures any output and prevents the "headers already sent" error,
// which is the most likely cause of the white screen on a live server.
ob_start();

// Use an absolute path to be more resilient on different servers.
require_once __DIR__ . '/config.php';

$error_message = '';

// Check for a return URL in the query string
$return_to = $_GET['return_to'] ?? 'index.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash, is_admin FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct, start the session
                // *** FIX: Added 'loggedin' and 'id' session variables for site-wide consistency. ***
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $user['id']; // For pages that may incorrectly use 'id'
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = (bool)$user['is_admin']; // Store admin status as a boolean
                
                // *** FIX: Redirect to the intended page after login, or to index.php by default. ***
                $redirect_url = $_POST['return_to'] ?? 'index.php';
                header("Location: " . $redirect_url);
                ob_end_flush(); // Send the output buffer (which is just the redirect header)
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Login failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Chris and Emma's Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md">
        <!-- **EMMA'S RESPONSIVE FIX** -->
        <!-- I've changed the flexbox classes here. -->
        <!-- 'flex-col sm:flex-row' makes the items stack vertically on small screens and go side-by-side on larger ones. -->
        <!-- 'gap-4' adds spacing when they are stacked. -->
        <nav class="container mx-auto px-6 py-4 flex flex-col gap-4 sm:flex-row sm:justify-between sm:items-center">
             <a href="index.php" class="text-2xl font-bold text-green-600">Chris and Emma's Pantry</a>
             <div>
                <a href="login.php" class="text-green-600 font-semibold border-b-2 border-green-600">Login</a>
                <a href="register.php" class="ml-4 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Register</a>
             </div>
        </nav>
    </header>

    <main class="container mx-auto px-6 py-12">
        <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6 text-center">Welcome Back!</h1>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
             <?php if (isset($_GET['message'])): ?>
                <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-6">
                <!-- *** FIX: Hidden input to preserve the return URL across form submissions. *** -->
                <input type="hidden" name="return_to" value="<?php echo htmlspecialchars($return_to); ?>">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required>
                </div>
                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Sign In
                    </button>
                </div>
            </form>
            <div class="text-center mt-4">
                <a href="forgot_password.php" class="text-sm text-green-600 hover:underline">Forgot Password?</a>
            </div>
        </div>
    </main>

</body>
</html>
<?php ob_end_flush(); // Send the final output to the browser ?>
