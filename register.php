<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';
            $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $_SESSION['success'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $error = "An error occurred while registering. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="index.scss">
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/realogo.png" alt="Logo Picture">
        </div>
    </header>
    <div class="wrapper">
        <div class="login-wrapper">
            <div class="login-container">
                <form action="register.php" method="POST">
                    <div class="login-header">
                        <h2>Create your Account</h2>
                        <p style="color: red; font-weight:bold;"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></p>
                    </div>
                    <div class="login-input">
                        <label>Name:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/></svg>
                        </i>
                        <input type="text" name="name" placeholder="Enter your name" maxlength="30" required>
                    </div>
                    <div class="login-input">
                        <label>Email:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </i>
                        <input type="email" name="email" placeholder="Enter your email" maxlength="30" onkeydown="return event.key !== ' ';" required>
                    </div>
                    <div class="login-input">
                        <label>Password:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </i>
                        <input type="password" name="password" placeholder="Enter your password" min="8" maxlength="30" onkeydown="return event.key !== ' ';" required>
                    </div>
                    <div class="login-input">
                        <label>Confirm Password:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </i>
                        <input type="password" name="confirm_password" placeholder="Confirm your password" min="8" maxlength="30" onkeydown="return event.key !== ' ';" required>
                    </div>
                    <div class="login-btn">
                        <button type="submit">Create account</button>
                    </div>
                    <div class="divider">
                        <span class="circle"></span>
                    </div>
                    <div class="create-btn">
                        <a href="login.php">
                            <button type="button">Login</button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
