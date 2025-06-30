<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

// Fetch all products
$sql = "SELECT * FROM products WHERE is_deleted = 0 ORDER BY category, name";
$stmt = oci_parse($conn, $sql);
oci_execute($stmt);

$products = array();
while ($row = oci_fetch_assoc($stmt)) {
    $products[] = $row;
}
oci_free_statement($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
    <link rel="stylesheet" href="../../Assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    .product-thumbnail {
        width: 100px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .thumbnail-container {
        position: relative;
        width: 100px;
        height: 80px;
    }

    .no-image-placeholder {
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 24px;
    }
    </style>
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
                    <a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="../products/manage_products.php"><i class="fas fa-boxes"></i> Products</a>
                </li>
                <li class="nav-item">
                    <a href="../orders/manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
                </li>
                <li class="nav-item">
                    <a href="../categories/manage_categories.php"><i class="fas fa-tags"></i> Categories</a>
                </li>
                <li class="nav-item">
                    <a href="../reports/manage_Daily_and_monthly.php"><i class="fas fa-chart-bar"></i> Daily and Monthly
                        Reports</a>
                </li>
                <li class="nav-item">
                    <a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="section-header">
                <h2><i class="fas fa-boxes"></i> Manage Products</h2>
                <a href="add_product.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </a>
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
                            <th><i class="fas fa-image"></i> Image</th>
                            <th><i class="fas fa-tag"></i> Name</th>
                            <th><i class="fas fa-list"></i> Category</th>
                            <th><i class="fas fa-dollar-sign"></i> Price</th>
                            <th><i class="fas fa-toggle-on"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="thumbnail-container">
                                    <?php
                                    $imageRelativePath = !empty($product['IMAGE_URL']) ? $product['IMAGE_URL'] : '';
                                    $imgSrc = '../../Assets/images/no-image.png';
                                    if ($imageRelativePath) {
                                        $imageRelativePath = ltrim($imageRelativePath, '/\\');
                                        if (strpos($imageRelativePath, 'uploads/products/') === 0) {
                                            $webPath = '../../' . str_replace('\\', '/', $imageRelativePath);
                                            $diskPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imageRelativePath);
                                        } else {
                                            $webPath = '../../uploads/products/' . $imageRelativePath;
                                            $diskPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . $imageRelativePath;
                                        }
                                        if (file_exists($diskPath)) {
                                            $imgSrc = $webPath;
                                        }
                                    }
                                    if ($imgSrc === '../../Assets/images/no-image.png' && !file_exists(dirname(__DIR__, 2) . '/Assets/images/no-image.png')) {
                                        $imgSrc = 'data:image/svg+xml;base64,' . base64_encode(
                                            '<svg width="100" height="80" xmlns="http://www.w3.org/2000/svg"><rect width="100" height="80" fill="#eee"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#aaa" font-size="14">No Image</text></svg>'
                                        );
                                    }
                                    ?>
                                    <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                                        alt="<?php echo htmlspecialchars($product['NAME']); ?>"
                                        class="product-thumbnail">
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($product['NAME']); ?></td>
                            <td><?php echo htmlspecialchars($product['CATEGORY']); ?></td>
                            <td>$<?php echo number_format($product['PRICE'], 2); ?></td>
                            <td>
                                <?php if ($product['IS_DELETED']): ?>
                                <span class="badge bg-danger">Deleted</span>
                                <?php else: ?>
                                <?php echo ($product['IS_AVAILABLE'] == 1 || $product['IS_AVAILABLE'] === true || $product['IS_AVAILABLE'] === '1')
                                            ? '<span class="badge bg-success">Available</span>'
                                            : '<span class="badge bg-warning text-dark">Disabled</span>'; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view_product.php?id=<?php echo $product['ID']; ?>"
                                        class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_product.php?id=<?php echo $product['ID']; ?>"
                                        class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmDelete(<?php echo $product['ID']; ?>, '<?php echo htmlspecialchars($product['NAME']); ?>')"
                                        title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                    <?php if ($product['IS_AVAILABLE'] == 1 || $product['IS_AVAILABLE'] === true || $product['IS_AVAILABLE'] === '1'): ?>
                                    <a href="toggle_product.php?id=<?php echo $product['ID']; ?>&action=disable"
                                        class="btn btn-sm btn-warning" title="Disable">
                                        <i class="fas fa-ban"></i> Disable
                                    </a>
                                    <?php else: ?>
                                    <a href="toggle_product.php?id=<?php echo $product['ID']; ?>&action=enable"
                                        class="btn btn-sm btn-success" title="Enable">
                                        <i class="fas fa-check"></i> Enable
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i> Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                    <p class="text-danger"><small><i class="bi bi-info-circle"></i> This action cannot be undone</small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="bi bi-trash3-fill"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete(id, name) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteItemName').textContent = name;
        document.getElementById('confirmDeleteBtn').onclick = function() {
            window.location.href = `delete_product.php?id=${id}`;
        };
        modal.show();
    }
    </script>
</body>

</html>