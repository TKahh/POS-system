<?php
require_once '../config/database.php';
require_once '../src/controllers/AuthController.php';

$authController = new AuthController($pdo);
$authController->logout();

header('Location: login.php');
?>
