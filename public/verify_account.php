<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Set to your timezone

require_once '../config/database.php';

if (!isset($_GET['link'])) {
    exit('Activation link is missing.');
}

$activationLink = $_GET['link'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE account_activation_link = ? AND link_expiration >= NOW()");
$stmt->execute([$activationLink]);
$user = $stmt->fetch();

if (!$user) {
    exit('Activation link is invalid or expired.');
}

// Update user's account status to "active"
$stmt = $pdo->prepare("UPDATE users SET status = 'active', account_activation_link = NULL, link_expiration = NULL WHERE id = ?");
$stmt->execute([$user['id']]);

echo 'Your account has been verified. You can now log in.';
?>
