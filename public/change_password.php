<?php
session_start();


require_once '../config/database.php';
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if user is not authenticated
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];


try {


   
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newPassword = $_POST['new_password'];
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("UPDATE users SET password = ?, account_activation_link = NULL, link_expiration = NULL, must_change_password = 0 WHERE id = ?");
        $stmt->execute([$hashedPassword,$userId]);  
    
        if ($stmt->rowCount() > 0) {
            echo "<script>
                    alert('Password updated successfully. Redirecting to dashboard...');
                    window.location.href = 'dashboard.php';
                    </script>";
            exit;
        }else {
            echo "Failed to update password. Row count: " . $stmt->rowCount();
        }
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        .bg {
            background-color: rgb(203, 183, 183);
        }
    </style>
</head>
<body>
    
    <div class="d-flex justify-content-center align-items-center bg" style="height: 100vh;">
        <form action="" method="post" class="bg-light p-4 rounded shadow">
            <h1 >Change your Password</h1>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <input class="form-control" type="password" id="new_password" name="new_password" required>
            </div>
            <button class="btn btn-primary" type="submit">Set New Password</button> 
            
        </form> 
    </div>

</body>
</html>
