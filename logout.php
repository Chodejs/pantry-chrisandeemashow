<?php
// Initialize the session
session_start();
 
// Unset all of the session variables
$_SESSION = array();
 
// Destroy the session.
session_destroy();
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out | The Plant-Powered Pantry</title>
    <!-- Redirect to the login page after 3 seconds -->
    <meta http-equiv="refresh" content="3;url=login.php">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 text-gray-800">

    <header class="bg-white shadow-md py-6">
        <div class="container mx-auto px-4 text-center">
            <a href="index.php" class="text-4xl font-extrabold text-green-700 hover:text-green-800 transition">The Plant-Powered Pantry</a>
        </div>
    </header>

    <main class="container mx-auto px-4 py-12">
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg text-center">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">You have been successfully logged out.</h2>
            <p class="text-gray-600 mb-6">Thank you for visiting. We hope to see you again soon!</p>
            <p class="text-sm text-gray-500">You will be redirected to the login page shortly.</p>
            <p class="text-sm text-gray-500 mt-4">If you are not redirected, <a href="login.php" class="text-green-600 hover:underline font-semibold">click here</a>.</p>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 text-center mt-12 fixed bottom-0 w-full">
        <div class="container mx-auto px-4">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> The Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>

