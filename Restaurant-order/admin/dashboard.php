<?php
require_once('../includes/auth.php');
checkAdminAuth();
require_once('../config/database.php');

// Fetch all users
$sql = "SELECT id, name, email, role, created_at, is_deleted FROM users ORDER BY is_deleted ASC, id ASC";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$users = array();
while ($row = oci_fetch_assoc($stmt)) {
  $users[] = $row;
}

// Get total users count
$total_users = count($users);
$active_users = array_filter($users, function ($user) {
  return !$user['IS_DELETED'];
});
$active_users_count = count($active_users);

// Get admin users count
$admin_users = array_filter($users, function ($user) {
  return $user['ROLE'] === 'admin';
});
$admin_count = count($admin_users);

oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard - QuickFeast Delivery</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
    rel="stylesheet" />
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel="stylesheet" href="../Assets/css/admin.css" />
</head>

<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-brand">
        <i class="fas fa-utensils"></i> QuickFeast Delivery
      </div>
      <ul class="nav-menu">
        <li class="nav-item active">
          <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        </li>
        <li class="nav-item">
          <a href="products/manage_products.php"><i class="fas fa-boxes"></i> Products</a>
        </li>
        <li class="nav-item">
          <a href="orders/manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        </li>
        <li class="nav-item">
          <a href="categories/manage_categories.php"><i class="fas fa-tags"></i> Categories</a>
        </li>
        <li class="nav-item">
          <a href="reports/manage_Daily_and_monthly.php"><i class="fas fa-chart-bar"></i> Daily and Monthly Reports</a>
        </li>
        <li class="nav-item">
          <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>
          Welcome,
          <?php echo htmlspecialchars($_COOKIE['user_name']); ?>
        </h1>
      </div>

      <!-- Statistics Cards -->
      <div class="dashboard-cards">
        <div class="card">
          <div class="card-title">Total Users</div>
          <div class="card-value"><?php echo $total_users; ?></div>
        </div>
        <div class="card">
          <div class="card-title">Active Users</div>
          <div class="card-value"><?php echo $active_users_count; ?></div>
        </div>
        <div class="card">
          <div class="card-title">Admin Users</div>
          <div class="card-value"><?php echo $admin_count; ?></div>
        </div>
      </div>

      <!-- User Management Section -->
      <div class="section-header">
        <h2><i class="bi bi-people-fill"></i> User Management</h2>
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
              <th><i class="fas fa-hashtag"></i> ID</th>
              <th><i class="fas fa-user"></i> Name</th>
              <th><i class="fas fa-envelope"></i> Email</th>
              <th><i class="fas fa-user-tag"></i> Role</th>
              <th><i class="fas fa-calendar-alt"></i> Created At</th>
              <th><i class="fas fa-toggle-on"></i> Status</th>
              <th><i class="fas fa-cogs"></i> Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?php echo htmlspecialchars($user['ID']); ?></td>
                <td>
                  <?php echo htmlspecialchars($user['NAME']); ?>
                  <?php if ($user['IS_DELETED']): ?>
                    <span class="status-badge status-pending">Deleted</span>
                  <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['EMAIL']); ?></td>
                <td>
                  <span
                    class="status-badge <?php echo $user['ROLE'] === 'admin' ? 'status-success' : ''; ?>">
                    <?php echo htmlspecialchars($user['ROLE']); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars($user['CREATED_AT']); ?></td>
                <td>
                  <span
                    class="status-badge <?php echo $user['IS_DELETED'] ? 'status-pending' : 'status-success'; ?>">
                    <?php echo $user['IS_DELETED'] ? 'Inactive' : 'Active'; ?>
                  </span>
                </td>
                <td>
                  <?php if (!$user['IS_DELETED']): ?>
                    <div class="btn-group" role="group">
                      <a
                        href="Data_user/edit_user.php?id=<?php echo $user['ID']; ?>"
                        class="btn btn-sm btn-primary">
                        <i class="bi bi-pencil-square"></i>
                      </a>
                      <?php if ($user['ID'] != $_SESSION['user_id']): ?>
                        <button
                          onclick="deleteUser(<?php echo $user['ID']; ?>)"
                          class="btn btn-sm btn-danger">
                          <i class="fa-solid fa-trash"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <button
                      onclick="restoreUser(<?php echo $user['ID']; ?>)"
                      class="btn btn-sm btn-success">
                      <i class="bi bi-arrow-counterclockwise"></i> Restore
                    </button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function deleteUser(id) {
      if (confirm("Are you sure you want to delete this user?")) {
        window.location.href = `Data_user/delete_user.php?id=${id}`;
      }
    }

    function restoreUser(id) {
      if (confirm("Are you sure you want to restore this user?")) {
        window.location.href = `Data_user/restore_user.php?id=${id}`;
      }
    }
  </script>
</body>

</html>

</html>