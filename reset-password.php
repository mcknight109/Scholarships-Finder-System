<?php
session_start();
require 'config.php';

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['email'])) {
    die("Unauthorized access.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE users SET password = ?, otp_code = NULL, otp_expiry = NULL WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Destroy session and redirect
        session_destroy();
        header("Location: login.php?message=Password reset successful");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.scss">
    <title>Reset Password</title>
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/realogo.png" alt="Logo Picture">
        </div>
    </header>
    <div class="wrapper">
        
        <div class="login-wrapper" id="loginModal"> 
            <div class="login-container">
                <form action="reset-password.php" method="POST">
                    <div class="login-header">
                        <h2>Reset Password</h2>
                        <p><?= htmlspecialchars($message ?? '') ?></p>
                    </div>
                    <div class="login-input">
                        <label for="">New Password:</label><br>
                        <i>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </i>
                        <input type="password" name="new_password" placeholder="Enter your new password">
                    </div>
                    <div class="login-input">
                        <label for="">Confirm Password:</label><br>
                        <i>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </i>
                        <input type="password" name="confirm_password" placeholder="Confirm your new password">
                    </div>
                    <div class="login-btn">
                        <button type="submit">Confirm</button>
                    </div>
                    <div class="divider">
                        <span class="circle"></span>
                    </div>
                    <div class="create-btn">
                        <button type="submit">Go back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
