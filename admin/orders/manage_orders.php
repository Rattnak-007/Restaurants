<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

// Fetch all orders with user info
$sql = "SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, o.updated_at, 
               u.name AS user_name, u.email AS user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$orders = [];
while ($row = oci_fetch_assoc($stmt)) {
    $orders[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Orders - Admin Dashboard</title>
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
          <i class="fas fa-utensils"></i>  QuickFeast Delivery
        </div>
        <ul class="nav-menu">          <li class="nav-item">
            <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
          </li>
          <li class="nav-item">
            <a href="../products/manage_products.php"><i class="fas fa-boxes"></i> Products</a>
          </li>
          <li class="nav-item active">
            <a href="../orders/manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
          </li>
          <li class="nav-item">
            <a href="../categories/manage_categories.php"><i class="fas fa-tags"></i> Categories</a>
          </li>
          <li class="nav-item">
            <a href="../reports/manage_Daily_and_monthly.php"><i class="fas fa-chart-bar"></i> Daily and Monthly Reports</a>
          </li>
          <li class="nav-item">
            <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </li>
        </ul>
      </div>
      <!-- Main Content -->
      <div class="main-content">
        <div class="section-header">
          <h2><i class="fas fa-shopping-cart"></i> Manage Orders</h2>
        </div>
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
          <?php echo htmlspecialchars($_GET['success']); ?>
        </div>
        <?php endif; ?>
        <div class="table-container">
          <table class="dashboard-table">
            <thead>
              <tr>
                <th><i class="fas fa-hashtag"></i> Order ID</th>
                <th><i class="fas fa-user"></i> User</th>
                <th><i class="fas fa-envelope"></i> Email</th>
                <th><i class="fas fa-dollar-sign"></i> Total</th>
                <th><i class="fas fa-info-circle"></i> Status</th>
                <th><i class="fas fa-calendar-alt"></i> Created At</th>
                <th><i class="fas fa-cogs"></i> Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['ID']); ?></td>
                <td><?php echo htmlspecialchars($order['USER_NAME']); ?></td>
                <td><?php echo htmlspecialchars($order['USER_EMAIL']); ?></td>
                <td>
                  $<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?>
                </td>
                <td>
                  <span
                    class="status-badge <?php switch (strtolower($order['STATUS'])) { case 'pending': echo 'status-pending'; break; case 'processing': echo 'status-processing'; break; case 'completed': echo 'status-success'; break; default: echo 'status-pending'; } ?>"
                  >
                    <?php echo htmlspecialchars(ucfirst($order['STATUS'])); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($order['CREATED_AT']); ?></td>
                <td>
                  <a
                    href="order_detail.php?id=<?php echo $order['ID']; ?>"
                    class="btn btn-sm btn-info"
                  >
                    <i class="fas fa-eye"></i> View
                  </a>
                </td>
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
