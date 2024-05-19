<?php
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['phone_number'])) {
    $phone_number = $_GET['phone_number'];
    
    $stmt = $pdo->prepare("SELECT fullname, address FROM customers WHERE phone_number = ?");
    $stmt->execute([$phone_number]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        echo json_encode(['exists' => true, 'fullname' => $customer['fullname'], 'address' => $customer['address']]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
