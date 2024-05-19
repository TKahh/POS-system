<?php
session_start();
require_once '../config/database.php';


if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'salesperson'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: dashboard.php');
    exit;
}

$order_id = $_GET['order_id'];

$stmt = $pdo->prepare("SELECT o.id, o.order_date, o.status, c.fullname, c.phone_number, c.address 
                        FROM orders o 
                        JOIN customers c ON o.customer_id = c.id 
                        WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('Location: dashboard.php');
    exit;
}


$stmt = $pdo->prepare("SELECT oi.quantity, oi.price, p.product_name 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Summary</title>
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
        <h1>Order Summary</h1>
        <div class="card mb-3">
            <div class="card-header">
                Order Details
            </div>
            <div class="card-body">
                <h5 class="card-title">Order ID: <?= htmlspecialchars($order['id']) ?></h5>
                <p class="card-text">Order Date: <?= htmlspecialchars($order['order_date']) ?></p>
                <p class="card-text">Status: <?= htmlspecialchars($order['status']) ?></p>
                <h5 class="card-title">Customer Details</h5>
                <p class="card-text">Name: <?= htmlspecialchars($order['fullname']) ?></p>
                <p class="card-text">Phone Number: <?= htmlspecialchars($order['phone_number']) ?></p>
                <p class="card-text">Address: <?= htmlspecialchars($order['address']) ?></p>
            </div>
        </div>
        <h3>Order Items</h3>
        <table class="table table-bordered table-secondary text-center">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_cost = 0;
                foreach ($order_items as $item): 
                    $item_total = $item['quantity'] * $item['price'];
                    $total_cost += $item_total;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= htmlspecialchars(number_format($item['price'], 2)) ?></td>
                        <td><?= htmlspecialchars(number_format($item_total, 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3"><strong>Total Cost</strong></td>
                    <td><strong><?= htmlspecialchars(number_format($total_cost, 2)) ?></strong></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-primary" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
    </div>
</body>
</html>