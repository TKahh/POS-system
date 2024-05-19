<?php
session_start();

require_once '../config/database.php';
require_once '../src/controllers/AuthController.php';

$authController = new AuthController($pdo);

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if ($authController->login($username, $password)) {
    if ($_SESSION['must_change_password']) {
        header('Location: change_password.php');
        exit;
    } elseif ($_SESSION['role'] === 'admin') {
        // Admin accounts bypass the verification step
        header('Location: dashboard.php');
        exit;
    } elseif ($_SESSION['status'] !== 'active') {
        $_SESSION['error'] = 'Your account is not yet verified. Please verify your account to log in.';
        header('Location: login.php');
        exit;
    } else {
        header('Location: dashboard.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'Invalid username or password.';
    header('Location: login.php');
    exit;
}
?>
