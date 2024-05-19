<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// $stmt = $pdo->query("SELECT id, full_name, email, status FROM users WHERE role = 'salesperson'");
// $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// $userRole = $_SESSION['role'];

$stmt = $pdo->prepare("SELECT id, full_name, email, status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($userRole === 'admin') {
    // For admin, retrieve all salespersons
    $stmt = $pdo->query("SELECT id, full_name, email, status FROM users WHERE role = 'salesperson'");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($userRole !== 'salesperson') {
    // Redirect if user role is neither admin nor salesperson
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee List</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg{
            background-color: rgb(203, 183, 183)
        }
    </style>
</head>
<body class="bg">
<div class="container">
        <h1>Employee List</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    
                    <th>Actions</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($employee['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($employee['email']); ?></td>
                    <td><?php echo htmlspecialchars($employee['status']); ?></td>
                    <td>
                        <?php if ($userRole === 'admin'): ?>
                            <button class="btn btn-danger" onclick="deleteAccount('<?php echo $employee['id']; ?>')">Delete Account</button>
                        <?php endif; ?>
                        <?php if ($userRole === 'admin'): ?>
                            <button class="btn btn-primary" onclick="resendActivationEmail('<?php echo $employee['email']; ?>')">Resend Activation Email</button>
                            <?php if ($employee['status'] === 'active'): ?>
                                <button class="btn btn-warning" onclick="changeAccountStatus('<?php echo $employee['id']; ?>', 'lock')">Lock Account</button>
                            <?php else: ?>
                                <button class="btn btn-success" onclick="changeAccountStatus('<?php echo $employee['id']; ?>', 'unlock')">Unlock Account</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <script>
    function resendActivationEmail(email) {
        if (confirm("Are you sure you want to resend the activation email?")) {
            window.location.href = 'resend_activation.php?email=' + email;
        }
    }

    function changeAccountStatus(userId, action) {
        if (confirm(`Are you sure you want to ${action} this account?`)) {
            window.location.href = `lock_unlock_account.php?id=${userId}&action=${action}`;
        }
    }

    function deleteAccount(userId) {
        if (confirm("Are you sure you want to delete this account?")) {
            window.location.href = `delete_account.php?id=${userId}`;
        }
    }
    </script>
</body>
</html>
