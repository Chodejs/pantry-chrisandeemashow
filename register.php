<?php
session_start();
require_once 'config.php';

// If user is already logged in, redirect them to the homepage
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // --- Validation ---
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error_message = "Please fill out all fields.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        try {
            // Check if email or username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error_message = "Username or email already taken.";
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
                if ($insertStmt->execute([$username, $email, $password_hash])) {
                    // Redirect to login page with a success message
                    header("Location: login.php?registration=success");
                    exit();
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $error_message = "A database error occurred. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Chris and Emma's Pantry</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
        <div class="text-center mb-8">
            <a href="index.php" class="text-3xl font-bold text-green-600">Chris and Emma's Pantry</a>
            <h2 class="text-2xl font-bold text-gray-800 mt-4">Create Your Account</h2>
            <p class="text-gray-600">Join our community of food lovers!</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" name="username" id="username" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                <input type="email" name="email" id="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="mb-6">
                <label for="password_confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" name="password_confirm" id="password_confirm" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:ring-2 focus:ring-green-500" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full">
                    Create Account
                </button>
            </div>
        </form>
         <p class="text-center text-gray-600 text-sm mt-6">
            Already have an account? <a href="login.php" class="font-bold text-green-600 hover:text-green-800">Log in here</a>.
        </p>
    </div>

</body>
</html>
