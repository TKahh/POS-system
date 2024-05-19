<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$userId = $_GET['id'];
$action = $_GET['action'];

if ($userId && ($action === 'lock' || $action === 'unlock')) {
    $newStatus = ($action === 'lock') ? 'locked' : 'active';

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $userId]);

    echo "Account status updated successfully.";
    echo "<script>
            setTimeout(function(){
                window.location.href = 'employee_list.php';
            }, 2000);
          </script>";
} else {
    echo "Invalid request.";
    echo "<script>
            setTimeout(function(){
                window.location.href = 'employee_list.php';
            }, 2000);
          </script>";
}
?>
