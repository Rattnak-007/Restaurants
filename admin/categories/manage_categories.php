<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

// Fetch all order items with user and product info
$sql = "SELECT oi.id AS order_item_id, oi.order_id, oi.product_id, oi.quantity, oi.price,
               o.user_id, o.created_at AS order_date,
               u.name AS user_name, u.email AS user_email,
               p.name AS product_name, p.category AS product_category
        FROM order_items oi
        LEFT JOIN orders o ON oi.order_id = o.id
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN products p ON oi.product_id = p.id
        ORDER BY oi.id DESC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$order_items = [];
while ($row = oci_fetch_assoc($stmt)) {
    $order_items[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Order Items - Admin Dashboard</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    />
    <link rel="stylesheet" href="../../Assets/css/admin.css" />
  </head>
  <body>
    <div class="dashboard-container">
      <!-- Sidebar -->
      <div class="sidebar">
        <div class="sidebar-brand">
          <i class="fas fa-utensils"></i> QuickFeast Delivery
        </div>
        <ul class="nav-menu">
          <li class="nav-item">
            <a href="../dashboard.php"
              ><i class="fas fa-tachometer-alt"></i> Dashboard</a
            >
          </li>
          <li class="nav-item">
            <a href="../products/manage_products.php"
              ><i class="fas fa-boxes"></i> Products</a
            >
          </li>
          <li class="nav-item">
            <a href="../orders/manage_orders.php"
              ><i class="fas fa-shopping-cart"></i> Orders</a
            >
          </li>
          <li class="nav-item active">
            <a href="manage_categories.php"
              ><i class="fas fa-tags"></i> Categories</a
            >
          </li>
          <li class="nav-item">
            <a href="../reports/manage_Daily_and_monthly.php">
              <i class="fas fa-chart-bar"></i> Daily and Monthly Reports
            </a>
          </li>
          <li class="nav-item">
            <a href="../../logout.php"
              ><i class="fas fa-sign-out-alt"></i> Logout</a
            >
          </li>
        </ul>
      </div>
      <!-- Main Content -->
      <div class="main-content">
        <div class="section-header">
          <h2><i class="fas fa-tags"></i> All Order Items</h2>
        </div>
        <div class="table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag"></i> Item ID</th>
                <th><i class="fas fa-hashtag"></i> Order ID</th>
                <th><i class="fas fa-user"></i> User</th>
                <th><i class="fas fa-envelope"></i> Email</th>
                <th><i class="fas fa-box"></i> Product</th>
                <th><i class="fas fa-list"></i> Category</th>
                <th><i class="fas fa-sort-amount-up"></i> Quantity</th>
                <th><i class="fas fa-dollar-sign"></i> Price</th>
                <th><i class="fas fa-calendar-alt"></i> Order Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($order_items as $item): ?>
              <tr>
                <td><?php echo htmlspecialchars($item['ORDER_ITEM_ID']); ?></td>
                <td><?php echo htmlspecialchars($item['ORDER_ID']); ?></td>
                <td><?php echo htmlspecialchars($item['USER_NAME']); ?></td>
                <td><?php echo htmlspecialchars($item['USER_EMAIL']); ?></td>
                <td><?php echo htmlspecialchars($item['PRODUCT_NAME']); ?></td>
                <td>
                  <?php echo htmlspecialchars($item['PRODUCT_CATEGORY']); ?>
                </td>
                <td><?php echo htmlspecialchars($item['QUANTITY']); ?></td>
                <td>$<?php echo number_format($item['PRICE'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['ORDER_DATE']); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
