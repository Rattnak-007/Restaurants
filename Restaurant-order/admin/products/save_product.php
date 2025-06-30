<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);

    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Use local upload directory
        $upload_dir = dirname(__DIR__, 2) . '/uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_mime = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime, $allowed_mime_types)) {
            header("Location: add_product.php?error=" . urlencode("Invalid image file type."));
            exit();
        }

        $file_name = uniqid() . '.' . $file_extension;
        $target_file = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Save relative path with forward slashes for web access
            $image_url = 'uploads/products/' . $file_name;
        }
    }

    // Insert product
    $sql = "INSERT INTO products (name, category, price, description, image_url) 
            VALUES (:name, :category, :price, :description, :image_url)";

    $stmt = oci_parse($conn, $sql);

    oci_bind_by_name($stmt, ":name", $name);
    oci_bind_by_name($stmt, ":category", $category);
    oci_bind_by_name($stmt, ":price", $price);
    oci_bind_by_name($stmt, ":description", $description);
    oci_bind_by_name($stmt, ":image_url", $image_url);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        header("Location: manage_products.php?success=Product added successfully");
    } else {
        $e = oci_error($stmt);
        header("Location: add_product.php?error=" . urlencode($e['message']));
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>