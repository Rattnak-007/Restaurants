<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: manage_products.php");
    exit();
}

// Fetch product details
$sql = "SELECT * FROM products WHERE id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ":id", $product_id);
oci_execute($stmt);
$product = oci_fetch_assoc($stmt);

// Convert CLOB to string for description
if (isset($product['DESCRIPTION']) && is_object($product['DESCRIPTION'])) {
    $product['DESCRIPTION'] = $product['DESCRIPTION']->load();
}

oci_free_statement($stmt);

if (!$product) {
    header("Location: manage_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../Assets/scss/Responsive.css">
</head>
<body>
    <div class="container mt-4">
        <h2><i class="fas fa-edit"></i> Edit Product</h2>
        
        <form action="update_product.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['ID']); ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label"><i class="fas fa-tag"></i> Product Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="<?php echo htmlspecialchars($product['NAME']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="category" class="form-label"><i class="fas fa-list"></i> Category</label>
                <input type="text" class="form-control" id="category" name="category" 
                       value="<?php echo htmlspecialchars($product['CATEGORY']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label"><i class="fas fa-dollar-sign"></i> Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                       value="<?php echo htmlspecialchars($product['PRICE']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label"><i class="fas fa-align-left"></i> Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($product['DESCRIPTION']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label"><i class="fas fa-image"></i> Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                <small class="text-muted"><i class="fas fa-info-circle"></i> Leave empty to keep current image</small>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_available" name="is_available" 
                           value="1" <?php echo $product['IS_AVAILABLE'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_available">Available</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Product
            </button>
            <a href="manage_products.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
