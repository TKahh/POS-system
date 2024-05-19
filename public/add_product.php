<?php
session_start();
require_once '../config/database.php';


if ($_SESSION['role'] !== 'admin') {
    header('Location: product_list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = $_POST['barcode'];
    $productName = $_POST['product_name'];
    $importPrice = $_POST['price'];
    $category = $_POST['category'];

    $stmt = $pdo->prepare("INSERT INTO products (barcode, product_name, price, category) VALUES (?, ?, ?, ?)");
    $stmt->execute([$barcode, $productName, $importPrice, $category]);

    header('Location: product_list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg{
            background-color: rgb(203, 183, 183)
        }
    </style>
</head>
<body class="bg">
    <div class="container mt-5 fw-bold">
        <h1 class="text-uppercase">Add New Product</h1>
        <form action="" method="POST">
            <div class="mb-3 text-uppercase">
                <label for="barcode" class="form-label">Barcode</label>
                <input type="text" class="form-control" id="barcode" name="barcode" required>
            </div>
            <div class="mb-3 text-uppercase">
                <label for="product_name" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="product_name" name="product_name" required>
            </div>
            <div class="mb-3 text-uppercase">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            </div>
            <div class="mb-3 text-uppercase">
                <label for="category" class="form-label">Category</label>
                <input type="text" class="form-control" id="category" name="category" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Product</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='product_list.php'">Cancel</button>
        </form>
    </div>
</body>
</html>
