<?php
require_once "config.php";

$token = $_GET['token'] ?? '';
$password = $confirm_password = "";
$password_err = $confirm_password_err = $token_err = "";
$email = "";

if (empty($token)) {
    $token_err = "Invalid or missing password reset token.";
} else {
    $sql = "SELECT email FROM password_resets WHERE token = ? AND expires >= ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $token, time());
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $result_email);
                mysqli_stmt_fetch($stmt);
                $email = $result_email;
            } else {
                $token_err = "Token has expired or is invalid. Please request a new one.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($token_err)) {
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a new password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match.";
        }
    }

    if (empty($password_err) && empty($confirm_password_err)) {
        $sql_update = "UPDATE users SET password_hash = ? WHERE email = ?";
        if ($stmt_update = mysqli_prepare($link, $sql_update)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            mysqli_stmt_bind_param($stmt_update, "ss", $hashed_password, $email);
            if (mysqli_stmt_execute($stmt_update)) {
                // Delete the used token
                $sql_delete = "DELETE FROM password_resets WHERE email = ?";
                if($stmt_delete = mysqli_prepare($link, $sql_delete)){
                    mysqli_stmt_bind_param($stmt_delete, "s", $email);
                    mysqli_stmt_execute($stmt_delete);
                    mysqli_stmt_close($stmt_delete);
                }
                header("location: login.php?reset=success");
                exit();
            } else {
                echo "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt_update);
        }
    }
    mysqli_close($link);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto mt-12">
        <div class="max-w-md mx-auto bg-white p-8 rounded-xl shadow-lg">
            <h2 class="text-3xl font-bold text-center mb-6">Set a New Password</h2>
            <?php if (!empty($token_err)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo $token_err; ?>
                    <p class="mt-2"><a href="forgot_password.php" class="font-bold underline">Request a new link</a></p>
                </div>
            <?php else: ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo htmlspecialchars($token); ?>" method="post">
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">New Password</label>
                        <input type="password" name="password" id="password" class="w-full px-4 py-2 border <?php echo (!empty($password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg">
                        <span class="text-red-500 text-sm"><?php echo $password_err; ?></span>
                    </div>
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="w-full px-4 py-2 border <?php echo (!empty($confirm_password_err)) ? 'border-red-500' : 'border-gray-300'; ?> rounded-lg">
                        <span class="text-red-500 text-sm"><?php echo $confirm_password_err; ?></span>
                    </div>
                    <div class="mb-4">
                        <button type="submit" class="w-full bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700">Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
