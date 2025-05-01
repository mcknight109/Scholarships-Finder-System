<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if the email already exists in the database
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered!";
        } else {
            // Hash the password before saving to database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user into the database (role defaults to 'student')
            $role = 'student'; // Default role
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
</head>
<body>
    <div class="background-wrapper">
        <div class="welcome-container">
            <h1>Register for Scholarship Finder</h1><br>
        </div>

        <!-- Display success or error message -->
        <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        <?php if (isset($_SESSION['success'])) { echo "<p style='color:green;'>".$_SESSION['success']."</p>"; unset($_SESSION['success']); } ?>

        <div class="login-wrapper" id="registerModal"> 
            <div class="login-container">
                <form action="register.php" method="POST">
                    <h2 class="login-label">Create an Account</h2>
                    <div class="login-input">
                        <label for="">Name:</label><br>
                        <input type="text" name="name" required>
                    </div>
                    <div class="login-input">
                        <label for="">Email:</label><br>
                        <input type="email" name="email" required>
                    </div>
                    <div class="login-input">
                        <label for="">Password:</label><br>
                        <input type="password" name="password" required>
                    </div>
                    <div class="login-input">
                        <label for="">Confirm Password:</label><br>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <div class="login-btn">
                        <button type="submit">Register</button>
                    </div>
                    <div class="back-btn">
                        <a href="login.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
