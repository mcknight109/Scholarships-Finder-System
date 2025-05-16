<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../../vendor/autoload.php'; // adjust path to your PHPMailer autoload

include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST['application_id'];
    $user_email = $_POST['user_email'];
    $reason = $_POST['reason'];

    // 1. Update the application status to rejected
    $stmt = $conn->prepare("UPDATE applications SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();

    // 2. Send email using PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; 
        $mail->SMTPAuth   = true;
        $mail->Username   = 'miraculous.knight109@gmail.com'; // replace with your Gmail
        $mail->Password   = 'otcdplpsgaahvsnl';    // replace with App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('miraculous.knight109@gmail.com', 'MyScholar PH Admin');
        $mail->addAddress($user_email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Scholarship Application Rejected';
        $mail->Body    = "<p>Dear Applicant,</p>
                          <p>We regret to inform you that your application has been <strong>rejected</strong>.</p>
                          <p><strong>Reason:</strong><br>" . nl2br(htmlspecialchars($reason)) . "</p>
                          <p>Thank you for your interest.</p>
                          <p>- ScholarPathPH Team</p>";

        $mail->send();
        header("Location: ../applicants.php?reject=success");
        exit();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid Request";
}
