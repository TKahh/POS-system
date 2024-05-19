<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = $_GET['email'];

if ($email) {
    // Generate new activation link
    $activationLink = bin2hex(random_bytes(16));
    $linkExpiration = date('Y-m-d H:i:s', strtotime('+1 minute'));

    // Update the database with the new activation link and expiration time
    $stmt = $pdo->prepare("UPDATE users SET account_activation_link = ?, link_expiration = ? WHERE email = ?");
    $stmt->execute([$activationLink, $linkExpiration, $email]);

    // Prepare the activation email
    $activationUrl = "http://localhost/Final-Web2023-2024/pos-system/public/verify_account.php?link=$activationLink";
    $subject = "Account Activation";
    $message = "Click the following link to activate your account: $activationUrl. This link is valid for 1 minute.";

    // Set up PHPMailer
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'trankyanh202@gmail.com';
        $mail->Password = 'snpm scpr xfib pngd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('trankyanh202@gmail.com', 'Ky Anh');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo "Activation email resent to $email.";
        echo "<script>
                setTimeout(function(){
                    window.location.href = 'dashboard.php';
                }, 2000);
              </script>";
    } catch (Exception $e) {
        echo "Failed to send activation email. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid email address.";
}
?>
