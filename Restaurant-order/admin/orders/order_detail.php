<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header('Location: manage_orders.php?error=Order not found');
    exit();
}

// Fetch order info
$sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, o.updated_at, u.name AS user_name, u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':id', $order_id);
oci_execute($stmt);
$order = oci_fetch_assoc($stmt);
oci_free_statement($stmt);

if (!$order) {
    header('Location: manage_orders.php?error=Order not found');
    exit();
}

// Fetch order items
$sql_items = "SELECT oi.product_id, oi.quantity, oi.price, p.name, p.image_url
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = :order_id";
$stmt_items = oci_parse($conn, $sql_items);
oci_bind_by_name($stmt_items, ':order_id', $order_id);
oci_execute($stmt_items);
$order_items = [];
while ($row = oci_fetch_assoc($stmt_items)) {
    $order_items[] = $row;
}
oci_free_statement($stmt_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../Assets/css/admin.css">
    <style>
        body {
            background: linear-gradient(120deg, #e0f7fa 0%, #f8f8f8 100%);
            min-height: 100vh;
        }
        .order-header {
            background: linear-gradient(90deg, #27ae60 0%, #2ecc71 100%);
            color: #fff;
            border-radius: 18px 18px 0 0;
            padding: 1.5rem 2.5rem 1.2rem 2.5rem;
            margin-bottom: 0;
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.10);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .order-header h2 {
            font-size: 2.1rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 1px;
        }
        .order-header i {
            font-size: 2.2rem;
        }
        .order-detail-card {
            box-shadow: 0 4px 18px rgba(44,62,80,0.10);
            border-radius: 0 0 18px 18px;
            margin-bottom: 2rem;
            background: #fff;
            border: none;
            padding: 1.5rem 2.5rem;
        }
        .order-detail-card .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #27ae60;
            margin-bottom: 1rem;
        }
        .order-detail-table {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(44,62,80,0.06);
        }
        .order-detail-table th {
            background: #e0f7fa;
            color: #2c3e50;
            font-weight: 700;
            font-size: 1.08rem;
            border-bottom: 2px solid #27ae60;
        }
        .order-detail-table th, .order-detail-table td {
            vertical-align: middle;
            text-align: center;
            padding: 0.85rem 0.5rem;
        }
        .order-detail-table tbody tr:hover {
            background: #f8f8f8;
        }
        .order-detail-table img {
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            width: 60px;
            height: 40px;
            object-fit: cover;
        }
        .order-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .order-info-list li {
            margin-bottom: 0.7rem;
            font-size: 1.08rem;
            color: #34495e;
        }
        .btn-secondary {
            background: linear-gradient(90deg, #34495e, #2c3e50);
            color: #fff;
            border: none;
            border-radius: 25px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            transition: background 0.2s;
        }
        .btn-secondary:hover {
            background: linear-gradient(90deg, #2c3e50, #34495e);
            color: #f39c12;
        }
        @media (max-width: 900px) {
            .order-header, .order-detail-card {
                padding: 1rem 1rem;
            }
        }
        @media (max-width: 768px) {
            .container {
                padding: 0 0.5rem;
            }
            .order-header {
                padding: 0.7rem 0.5rem;
                font-size: 1.1rem;
                flex-direction: column;
                align-items: flex-start;
            }
            .order-header h2 {
                font-size: 1.1rem;
                word-break: break-word;
            }
            .order-detail-card {
                padding: 0.7rem 0.5rem;
                margin-bottom: 1rem;
            }
            .order-detail-table th, .order-detail-table td {
                font-size: 0.93rem;
                padding: 0.45rem 0.15rem;
            }
            .order-detail-table img {
                width: 32px !important;
                height: 20px !important;
            }
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .order-detail-table {
                min-width: 480px;
            }
            .btn-secondary {
                width: 100%;
                padding: 0.7rem 0;
                font-size: 1rem;
            }
        }
        @media (max-width: 480px) {
            .order-header, .order-detail-card {
                padding: 0.4rem 0.1rem;
            }
            .order-header h2 {
                font-size: 0.95rem;
            }
            .order-detail-table th, .order-detail-table td {
                font-size: 0.85rem;
                padding: 0.3rem 0.08rem;
            }
            .order-detail-table img {
                width: 22px !important;
                height: 14px !important;
            }
            .order-info-list li {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 375px) {
            .order-header h2 {
                font-size: 0.8rem;
            }
            .order-detail-card .card-title {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="order-header mb-0">
        <h2><i class="fas fa-receipt"></i> Order #<?php echo htmlspecialchars($order['ID']); ?> Details</h2>
    </div>
    <a href="manage_orders.php" class="btn btn-secondary my-3">&larr; Back to Orders</a>
    <div class="card mb-4 order-detail-card">
        <div class="card-body">
            <h5 class="card-title">Order Info</h5>
            <ul class="order-info-list">
                <li><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($order['STATUS'])); ?></li>
                <li><strong>Total Amount:</strong> $<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?></li>
                <li><strong>Created At:</strong> <?php echo htmlspecialchars($order['CREATED_AT']); ?></li>
                <li><strong>Updated At:</strong> <?php echo htmlspecialchars($order['UPDATED_AT']); ?></li>
            </ul>
        </div>
    </div>
    <div class="card mb-4 order-detail-card">
        <div class="card-body">
            <h5 class="card-title">User Info</h5>
            <ul class="order-info-list">
                <li><strong>Name:</strong> <?php echo htmlspecialchars($order['USER_NAME']); ?></li>
                <li><strong>Email:</strong> <?php echo htmlspecialchars($order['USER_EMAIL']); ?></li>
            </ul>
        </div>
    </div>
    <div class="card order-detail-card">
        <div class="card-body">
            <h5 class="card-title">Order Items</h5>
            <div class="table-responsive">
                <table class="table table-bordered order-detail-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Image</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['NAME']); ?></td>
                            <td>
                                <?php if (!empty($item['IMAGE_URL'])): ?>
                                    <img src="<?php echo '../../' . htmlspecialchars($item['IMAGE_URL']); ?>" alt="<?php echo htmlspecialchars($item['NAME']); ?>" style="width:60px;height:40px;object-fit:cover;">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($item['QUANTITY']); ?></td>
                            <td>$<?php echo number_format($item['PRICE'], 2); ?></td>
                            <td>$<?php echo number_format($item['PRICE'] * $item['QUANTITY'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
