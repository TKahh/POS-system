<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'salesperson'])) {
    header('Location: login.php');
    exit;
}

$customer = null;
$orders = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = $_POST['phone_number'];
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE phone_number = ?");
    $stmt->execute([$phone_number]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE customer_id = ?");
        $stmt->execute([$customer['id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Search</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg {
            background-color: rgb(203, 183, 183);
        }
    </style>
</head>
<body class="bg">
    <div class="container mt-5">
        <h1>Customer Search</h1>
        <form action="customer_search.php" method="POST" class="mb-3">
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='dashboard.php'">GO back</button>
        </form>

        <?php if ($customer): ?>
            <h2>Customer Information</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($customer['fullname']) ?></p>
            <p><strong>Phone Number:</strong> <?= htmlspecialchars($customer['phone_number']) ?></p>
            <p><strong>Address:</strong> <?= htmlspecialchars($customer['address']) ?></p>

            <h2>Purchase History</h2>
            <table class="table table-bordered table-secondary text-center">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $stmt = $pdo->prepare("SELECT SUM(quantity * price) AS total_amount FROM order_items WHERE order_id = ?");
                        $stmt->execute([$order['id']]);
                        $total_amount = $stmt->fetchColumn();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($order['id']) ?></td>
                            <td><?= htmlspecialchars($order['order_date']) ?></td>
                            <td><?= htmlspecialchars(number_format($total_amount, 2)) ?></td>
                            <td><a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-info">View Details</a></td>
                            
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <p>No customer found with this phone number.</p>
        <?php endif; ?>
    </div>
</body>
</html>
