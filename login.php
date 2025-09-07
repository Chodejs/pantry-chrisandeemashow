<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect them to the homepage
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "config.php";

$email = $password = "";
$email_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Check credentials
    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT id, username, email, password_hash FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $db_email, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            header("location: index.php");
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid email or password.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | The Plant-Powered Pantry</title>
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
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-6">Welcome Back!</h2>
            <p class="text-center text-gray-600 mb-8">Sign in to access your pantry.</p>

            <?php 
            if(!empty($login_err)){
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $login_err . '</div>';
            }        
            ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-4 py-2 border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" value="<?php echo $email; ?>">
                    <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 font-semibold mb-2">Password</label>
                    <input type="password" name="password" id="password" class="w-full px-4 py-2 border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                    <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
                </div>
                <div class="mb-4">
                    <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 px-4 rounded-lg shadow-lg hover:bg-green-700 transition duration-300">Login</button>
                </div>
                <p class="text-center text-gray-600">Don't have an account? <a href="register.php" class="text-green-600 hover:underline font-semibold">Sign up now</a>.</p>
            </form>
        </div>
    </main>
    <footer class="bg-gray-800 text-white py-6 text-center mt-12">
        <div class="container mx-auto px-4">
            <p class="text-sm">&copy; <?php echo date("Y"); ?> The Chris and Emma Show. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
