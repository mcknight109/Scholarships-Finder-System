<?php
session_start();
date_default_timezone_set('Asia/Manila');
require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';
$enteredEmail = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Send Email Button Clicked
    if (isset($_POST['send_email']) && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $enteredEmail = $email; // retain entered value

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $updateQuery = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("sss", $otp, $expiry, $email);
            $stmt->execute();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'miraculous.knight109@gmail.com';
                $mail->Password = 'otcdplpsgaahvsnl';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('miraculous.knight109@gmail.com', 'Scholarship Finder');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Password Reset OTP";
                $mail->Body = "Your OTP for password reset is <b>$otp</b>. It expires in 10 minutes.";

                $mail->send();
                $_SESSION['email'] = $email;
                $message = "OTP sent successfully to your email!";
            } catch (Exception $e) {
                $message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $message = "Email address not found.";
        }
        // OTP Form Submitted
    } elseif (isset($_POST['verify_otp']) && isset($_POST['otp'])) {
        if (!isset($_SESSION['email'])) {
            die("Unauthorized access.");
        }

        $email = $_SESSION['email'];
        $otp = trim($_POST['otp']);

        $query = "SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $_SESSION['otp_verified'] = true;
            header("Location: reset-password.php");
            exit();
        } else {
            $message = "Invalid or expired OTP.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.scss">
    <title>Forget Password</title>
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/realogo.png" alt="Logo Picture">
        </div>
        <ul>
            <li>
                <a href="login.php">
                    <p>Go back</p>
                </a>
            </li>
        </ul>
    </header>

    <div class="wrapper">

        <div class="login-wrapper" id="loginModal"> 
            <div class="login-container">
                <form action="forget-password.php" method="POST">
                    <div class="login-header">
                        <h2>Forget Password</h2>
                        <p><?= $message ?></p>
                    </div>
                    <div class="login-input">
                        <label for="">Email:</label><br>
                        <i>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </i>
                        <input type="email" name="email" value="<?= htmlspecialchars($enteredEmail)?>" placeholder="Enter your email">
                    </div>
                    <div class="login-input">
                        <label for="">OTP:</label><br>
                        <i>
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-mail">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </i>
                        <input type="text" name="otp">
                    </div>
                    <div class="login-btn">
                        <button type="submit" name="send_email">Send email</button>
                    </div>
                    <div class="divider">
                        <span class="circle"></span>
                    </div>
                    <div class="login-btn">
                        <button type="submit" name="verify_otp">Verify OTP</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
