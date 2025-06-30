<?php
require_once('includes/auth.php');
checkAuth();
require_once('config/database.php');

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $cart = $input['cart'] ?? [];

    if (empty($cart)) {
        throw new Exception('Cart is empty');
    }

    // Calculate total amount
    $total_amount = 0;
    foreach ($cart as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }

    // Start transaction
    // oci_commit($conn); // Removed to ensure transaction integrity

    // Create order
    $sql = "INSERT INTO orders (user_id, total_amount) VALUES (:user_id, :total_amount) 
            RETURNING id INTO :order_id";
    $stmt = oci_parse($conn, $sql);

    $user_id = $_SESSION['user_id'];
    $order_id = 0;

    oci_bind_by_name($stmt, ":user_id", $user_id);
    oci_bind_by_name($stmt, ":total_amount", $total_amount);
    oci_bind_by_name($stmt, ":order_id", $order_id, -1, SQLT_INT);

    oci_execute($stmt);
    oci_free_statement($stmt);

    // Insert order items
    $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (:order_id, :product_id, :quantity, :price)";
    $stmt = oci_parse($conn, $sql);

    foreach ($cart as $item) {
        oci_bind_by_name($stmt, ":order_id", $order_id);
        oci_bind_by_name($stmt, ":product_id", $item['id']);
        oci_bind_by_name($stmt, ":quantity", $item['quantity']);
        oci_bind_by_name($stmt, ":price", $item['price']);
        oci_execute($stmt);
    }

    oci_commit($conn);
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    oci_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

oci_close($conn);
