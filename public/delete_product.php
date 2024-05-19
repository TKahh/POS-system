<?php
session_start();
require_once '../config/database.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['id'];

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $stmt->execute([$productId]);
    $productInOrders = $stmt->fetchColumn();

    if ($productInOrders > 0) {
        // If the product is in any orders, don't allow deletion
        echo "<script>
                alert('Cannot delete product. It is associated with existing orders.');
                window.location.href = 'product_list.php';
              </script>";
        exit;
    }

    // If the product isn't in any orders, proceed with deletion
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$productId]);

    if ($stmt->rowCount() > 0) {
        echo "<script>
                alert('Product deleted successfully.');
                window.location.href = 'product_list.php';
              </script>";
    } else {
        echo "<script>
                alert('Failed to delete product.');
                window.location.href = 'product_list.php';
              </script>";
    }
    exit;
} else {
    header('Location: product_list.php');
    exit;
}
?>
