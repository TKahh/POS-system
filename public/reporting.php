<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'salesperson'])) {
    header('Location: login.php');
    exit;
}

function getSalesData($pdo, $startDate, $endDate) {
    $data = [];

    // Total amount received
    $stmt = $pdo->prepare("SELECT SUM(oi.price * oi.quantity) as total_amount FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.id 
                           WHERE o.order_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $data['total_amount'] = $stmt->fetchColumn();

    // Number of orders
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE order_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $data['total_orders'] = $stmt->fetchColumn();

    // Number of products sold
    $stmt = $pdo->prepare("SELECT SUM(oi.quantity) as total_products FROM order_items oi 
                           JOIN orders o ON oi.order_id = o.id 
                           WHERE o.order_date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $data['total_products'] = $stmt->fetchColumn();

    // Order details
    $stmt = $pdo->prepare("SELECT o.id, o.order_date, c.fullname, c.phone_number, SUM(oi.price * oi.quantity) as total 
                           FROM orders o 
                           JOIN customers c ON o.customer_id = c.id 
                           JOIN order_items oi ON o.id = oi.order_id 
                           WHERE o.order_date BETWEEN ? AND ? 
                           GROUP BY o.id, o.order_date, c.fullname, c.phone_number 
                           ORDER BY o.order_date");
    $stmt->execute([$startDate, $endDate]);
    $data['orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $data;
}

// Default to today's data
$startDate = date('Y-m-d 00:00:00');
$endDate = date('Y-m-d 23:59:59');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
}

$salesData = getSalesData($pdo, $startDate, $endDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
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
        <h1>Sales Report</h1>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="datetime-local" class="form-control" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" required>
                </div>
                <div class="col-md-5">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="datetime-local" class="form-control" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" required>
                </div>
                <div class="col-md-2 align-self-end">
                    <button type="submit" class="btn btn-primary w-100">View Report</button>
                </div>
            </div>
        </form>

        <div class="card mb-3">
            <div class="card-header">
                Sales Summary
            </div>
            <div class="card-body">
                <p>Total Amount Received: <?= htmlspecialchars(number_format($salesData['total_amount'], 2)) ?></p>
                <p>Total Orders: <?= htmlspecialchars($salesData['total_orders']) ?></p>
                <p>Total Products Sold: <?= htmlspecialchars($salesData['total_products']) ?></p>
            </div>
        </div>

        <h3>Order Details</h3>
        <table class="table table-bordered table-secondary text-center">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Customer Name</th>
                    <th>Phone Number</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salesData['orders'] as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['order_date']) ?></td>
                        <td><?= htmlspecialchars($order['fullname']) ?></td>
                        <td><?= htmlspecialchars($order['phone_number']) ?></td>
                        <td><?= htmlspecialchars(number_format($order['total'], 2)) ?></td>
                        <td><a href="order_summary.php?order_id=<?= htmlspecialchars($order['id']) ?>" class="btn btn-info">View Details</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <button type="button" class="btn btn-primary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>

    </div>

</body>
</html>
