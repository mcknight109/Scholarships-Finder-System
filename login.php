<?php
session_start();
include 'config.php';
date_default_timezone_set('Asia/Manila');

// Only run when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if user exists
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if password is correct
        if (password_verify($password, $user['password'])) {
            // Save user data to session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            $user_id = $user['id'];

            // Update login time
            $now = date('Y-m-d H:i:s');
            $updateLogin = $conn->prepare("UPDATE users SET last_login = ? , status = 'online' WHERE email = ?");
            $updateLogin->bind_param("ss", $now, $email);
            $updateLogin->execute();

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: adminSide/admin-dashboard.php"); // Redirect to admin dashboard
                exit();
            } else {
                header("Location: userSide/home-page.php"); // Redirect to student dashboard
                exit();
            }
        } else {
            $error = "Incorrect email or password!";
        }
    } else {
        $error = "No user found with that email!";
    }
}

// After verifying user credentials
$now = date('Y-m-d H:i:s');
$updateLogin = $conn->prepare("UPDATE users SET last_login = ? WHERE email = ?");
$updateLogin->bind_param("ss", $now, $email);
$updateLogin->execute();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
    <link rel="stylesheet" href="index.scss">
    <title>Landing Page</title>
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
                <form action="login.php" method="POST">
                    <div class="login-header">
                        <h2>Login your account</h2>
                        <p style="color: red; font-weight:bold;"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></p>
                    </div>
                    <div class="login-input">
                        <label for="">Email:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </i>
                        <input type="email" name="email" placeholder="Enter your email" maxlength="30" onkeydown="return event.key !== ' ';" required>
                    </div>
                    <div class="login-input">
                        <label for="">Password:</label><br>
                        <i>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-lock">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </i>
                        <input type="password" name="password" placeholder="Enter your password" maxlength="30" onkeydown="return event.key !== ' ';" required>
                    </div>
                    <div class="forget">
                        <a href="forget-password.php">Forget password?</a>
                    </div>
                    <div class="login-btn">
                        <button type="submit">Login</button>
                    </div>
                    <div class="divider">
                        <span class="circle"></span>
                    </div>
                    <div class="create-btn">
                        <a href="register.php">
                            <button type="button">Create account</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <!-- <script>
        const openLogin = document.getElementById("openLogin");
        const loginModal = document.getElementById("loginModal");
        const closeLogin = document.getElementById("closeLogin");

        function showLogin(){
            loginModal.style.display = "flex";
        }
        function hideLogin(){
            loginModal.style.display = "none";
        }
        openLogin.addEventListener("click", showLogin);
        closeLogin.addEventListener("click", hideLogin);

        window.addEventListener("click", (e) => {
            if (e.target == loginModal){
                hideLogin();
            }
        });
    </script> -->
</body>
</html>
