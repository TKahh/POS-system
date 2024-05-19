<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$is_admin = $_SESSION['role'] === 'admin';
$user_role = $_SESSION['role'];


$query = "SELECT id, barcode, product_name, price, category, creation_date";
if ($is_admin) {
    $query .= ", price";
}
$query .= " FROM products";

$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Catalog</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg{
            background-color: rgb(203, 183, 183)
        }
    </style>
</head>
<body class="bg">
    <div class="container mt-5">
        <h1>Product Catalog</h1>
        <table class="table table-bordered table-secondary text-center">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Creation Date</th>
                    <?php if ($is_admin): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody table-group-divider>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['barcode']) ?></td>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td><?= htmlspecialchars($product['price']) ?></td>
                        <td><?= htmlspecialchars($product['category']) ?></td>
                        <td><?= htmlspecialchars($product['creation_date']) ?></td>
                        <?php if ($is_admin): ?>
                            <td>
                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-success btn-outline-light">Edit</a>
                                <form action="delete_product.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-outline-light">Delete</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($is_admin): ?>
            <a href="add_product.php" class="btn btn-success">Add New Product</a>
        <?php endif; ?>
        <button type="button" class="btn btn-danger" onclick="window.location.href='dashboard.php'">GO back</button>
    </div>
</body>
</html>
