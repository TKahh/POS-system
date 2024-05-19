<?php
session_start();
require_once '../config/database.php';

if (!isset($_GET['order_id'])) {
    header('Location: dashboard.php');
    exit;
}

$order_id = $_GET['order_id'];

$stmt = $pdo->prepare("SELECT orders.id, orders.order_date, customers.fullname, customers.phone_number, customers.address
                       FROM orders
                       JOIN customers ON orders.customer_id = customers.id
                       WHERE orders.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT products.product_name, order_items.quantity, order_items.price
                       FROM order_items
                       JOIN products ON order_items.product_id = products.id
                       WHERE order_items.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details</title>
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
        <h1>Order Details</h1>
        <table class="table table-bordered table-secondary">
            <tr>
                <th>Order ID</th>
                <td><?= htmlspecialchars($order['id']) ?></td>
            </tr>
            <tr>
                <th>Order Date</th>
                <td><?= htmlspecialchars($order['order_date']) ?></td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td><?= htmlspecialchars($order['fullname']) ?></td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td><?= htmlspecialchars($order['phone_number']) ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?= htmlspecialchars($order['address']) ?></td>
            </tr>
        </table>

        <h2>Items</h2>
        <table class="table table-bordered table-secondary">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="order_summary.php?order_id=<?= htmlspecialchars($order_id) ?>" class="btn btn-primary">Back to Order Summary</a>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
