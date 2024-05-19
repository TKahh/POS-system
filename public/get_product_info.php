<?php
require_once '../config/database.php';

$search_query = $_GET['product_search'] ?? '';

$stmt = $pdo->prepare("SELECT id, product_name, price FROM products WHERE product_name LIKE ? OR barcode LIKE ?");
$stmt->execute(['%' . $search_query . '%', '%' . $search_query . '%']);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($products);
?>

