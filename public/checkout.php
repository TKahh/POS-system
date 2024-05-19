<?php
session_start();
require_once '../config/database.php';

// Check if session variable for order_items exists, if not initialize it
if (!isset($_SESSION['order_items'])) {
    $_SESSION['order_items'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['complete_purchase'])) {
        $phone_number = $data['phone_number'];
        $fullname = $data['fullname'];
        $address = $data['address'];
        $order_items = $data['order_items'];
        $amount_received = $data['amount_received'];

        // Check if the customer already exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone_number = ?");
        $stmt->execute([$phone_number]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            $stmt = $pdo->prepare("INSERT INTO customers (phone_number, fullname, address) VALUES (?, ?, ?)");
            $stmt->execute([$phone_number, $fullname, $address]);
            $customer_id = $pdo->lastInsertId();
        } else {
            $customer_id = $customer['id'];
        }

        // Create order
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_date, status, customer_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], date('Y-m-d H:i:s'), 'pending', $customer_id]);
        $order_id = $pdo->lastInsertId();

        // Add order items
        foreach ($order_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }

        // Calculate change
        $total_amount = array_sum(array_column($order_items, 'total'));
        $change = $amount_received - $total_amount;

        echo json_encode(['success' => true, 'order_id' => $order_id, 'change' => $change]);
        exit;
    } elseif (isset($data['add_product'])) {
        // Handle adding product to order_items session
        $product_id = $data['product_id'];
        $quantity = $data['quantity'];

        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            $product['quantity'] = $quantity;
            $product['total'] = $product['price'] * $quantity;
            $_SESSION['order_items'][] = $product;

            echo json_encode(['success' => true, 'order_items' => $_SESSION['order_items']]);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/main.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .bg {
            background-color: rgb(203, 183, 183);
        }
    </style>
</head>
<body class="bg fw-bold text-uppercase">
    <div class="container mt-5">
        <h1>Checkout</h1>
        <form id="productForm">
            <div class="mb-3">
                <label for="product_search" class="form-label">Search Product by Name or Barcode</label>
                <input type="text" class="form-control" id="product_search" name="product_search">
            </div>
            <div id="search_results" class="list-group"></div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="1" required>
            </div>
            <button type="button" class="btn btn-primary" onclick="addProduct()">Add Product</button>
            
        </form>

        <h2>Order Items</h2>
        <table class="table table-bordered" id="orderItemsTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <!-- Order items will be dynamically added here -->
            </tbody>
        </table>
        <div>
            <h3>Total Amount: <span id="totalAmount">0</span></h3>
        </div>

        <form id="customerForm" class="mt-5" action="checkout.php" method="POST">
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" required>
            </div>
            <div id="customer_info" style="display: none;">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address"></textarea>
                </div>
            </div>
            <div class="mb-3">
                <label for="amount_received" class="form-label">Amount Received</label>
                <input type="number" class="form-control" id="amount_received" name="amount_received" required>
            </div>
            <div>
                <h3>Change: <span id="changeAmount">0</span></h3>
            </div>
            <button type="button" class="btn btn-primary" onclick="completePurchase()">Complete Purchase</button>
            <button type="button" class="btn btn-danger" onclick="window.location.href='dashboard.php'">GO back</button>
        </form>
    </div>

    <script>
        let orderItems = [];

        document.getElementById('product_search').addEventListener('input', function() {
            const query = this.value;
            if (query) {
                fetch('get_product_info.php?product_search=' + query)
                    .then(response => response.json())
                    .then(data => {
                        const results = document.getElementById('search_results');
                        results.innerHTML = '';
                        data.forEach(product => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.className = 'list-group-item list-group-item-action';
                            item.textContent = product.product_name + ' - ' + product.price;
                            item.onclick = () => selectProduct(product.id, product.product_name, product.price);
                            results.appendChild(item);
                        });
                    });
            }
        });

        function selectProduct(id, name, price) {
            document.getElementById('product_search').value = name;
            document.getElementById('product_search').dataset.productId = id;
            document.getElementById('product_search').dataset.productPrice = price;
            document.getElementById('search_results').innerHTML = '';
        }

        function addProduct() {
            const productId = document.getElementById('product_search').dataset.productId;
            const productName = document.getElementById('product_search').value;
            const productPrice = document.getElementById('product_search').dataset.productPrice;
            const quantity = document.getElementById('quantity').value;

            if (productId && productName && productPrice && quantity) {
                const product = {
                    id: productId,
                    product_name: productName,
                    price: parseFloat(productPrice),
                    quantity: parseInt(quantity),
                    total: parseFloat(productPrice) * parseInt(quantity)
                };

                orderItems.push(product);
                updateOrderTable();
            } else {
                alert('Please select a product and enter a quantity.');
            }
        }

        function updateOrderTable() {
            const tableBody = document.getElementById('orderItemsTable').getElementsByTagName('tbody')[0];
            tableBody.innerHTML = '';
            let totalAmount = 0;

            orderItems.forEach((item, index) => {
                const row = tableBody.insertRow();
                row.insertCell(0).innerText = item.product_name;
                row.insertCell(1).innerText = item.price;
                row.insertCell(2).innerHTML = `<input type="number" value="${item.quantity}" onchange="updateQuantity(${index}, this.value)">`;
                row.insertCell(3).innerText = item.total;
                row.insertCell(4).innerHTML = `<button class="btn btn-danger" onclick="removeProduct(${index})">Remove</button>`;
                    totalAmount += item.total;
                });

                document.getElementById('totalAmount').innerText = totalAmount.toFixed(2);
            }

            function updateQuantity(index, newQuantity) {
                if (newQuantity <= 0) {
                    removeProduct(index);
                    return;
                }

                orderItems[index].quantity = parseInt(newQuantity);
                orderItems[index].total = orderItems[index].price * orderItems[index].quantity;
                updateOrderTable();
            }

            function removeProduct(index) {
                orderItems.splice(index, 1);
                updateOrderTable();
            }

            function completePurchase() {
                const phone_number = document.getElementById('phone_number').value;
                const fullname = document.getElementById('fullname').value;
                const address = document.getElementById('address').value;
                const amount_received = document.getElementById('amount_received').value;

                if (!phone_number || !amount_received || orderItems.length === 0) {
                    alert('Please complete all fields and add at least one product.');
                    return;
                }

                const data = {
                    complete_purchase: true,
                    phone_number: phone_number,
                    fullname: fullname,
                    address: address,
                    order_items: orderItems,
                    amount_received: parseFloat(amount_received)
                };

                fetch('checkout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Purchase completed! Change: ' + data.change.toFixed(2));
                        window.location.href = 'order_summary.php?order_id=' + data.order_id;
                    } else {
                        alert('An error occurred. Please try again.');
                    }
                });
            }

            document.getElementById('phone_number').addEventListener('blur', function() {
                const phoneNumber = this.value;
                if (phoneNumber) {
                    fetch('get_customer_info.php?phone_number=' + phoneNumber)
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                document.getElementById('fullname').value = data.fullname;
                                document.getElementById('address').value = data.address;
                                document.getElementById('customer_info').style.display = 'none';
                            } else {
                                document.getElementById('customer_info').style.display = 'block';
                            }
                        });
            }
        });
    </script>
</body>
</html>