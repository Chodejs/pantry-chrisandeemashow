<?php
require_once "config.php";
$email = "";
$email_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($email_err)) {
        // Check if email exists
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Email exists, generate a token
                    $token = bin2hex(random_bytes(50));
                    $expires = time() + 3600; // 1 hour from now

                    // Store token in the database
                    $sql_insert = "INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)";
                    if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
                        mysqli_stmt_bind_param($stmt_insert, "ssi", $email, $token, $expires);
                        if (mysqli_stmt_execute($stmt_insert)) {
                            // In a real application, you would email this link.
                            // For this environment, we will display it.
                            $reset_link = "reset_password.php?token=" . $token;
                            $success_msg = "If an account with that email exists, a password reset link has been generated. <br><br><strong>For Testing:</strong> Please use the link below to reset your password.<br><br><a href='{$reset_link}' class='text-blue-500 underline break-all'>{$reset_link}</a>";
                        } else {
                            $email_err = "Oops! Something went wrong. Please try again.";
                        }
                        mysqli_stmt_close($stmt_insert);
                    }
                } else {
                     $success_msg = "If an account with that email exists, password reset instructions have been generated.";
                }
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
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-12">
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-center mb-6">Reset Your Password</h2>
            <p class="text-center text-gray-600 mb-8">Enter your email address and we will generate a link to reset your password.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-4 py-2 border <?php echo (!empty($email_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg" value="<?php echo $email; ?>">
                    <span class="text-red-500 text-sm"><?php echo $email_err; ?></span>
                </div>
                <?php if(!empty($success_msg)): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo $success_msg; ?></span>
                    </div>
                <?php endif; ?>
                <div class="mb-4">
                    <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700">Send Reset Link</button>
                </div>
                <p class="text-center"><a href="login.php" class="text-green-600 hover:underline">Back to Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>
