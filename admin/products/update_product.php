<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $is_available = isset($_POST['is_available']) ? intval($_POST['is_available']) : 0;

    $sql = "UPDATE products SET 
            name = :name, 
            category = :category, 
            price = :price, 
            description = :description, 
            is_available = :is_available";
    $params = array();

    // Handle file upload if new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = dirname(__DIR__, 2) . '/uploads/products/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $allowed_mime_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $file_mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($file_extension, $allowed_extensions) || !in_array($file_mime, $allowed_mime_types)) {
            header("Location: edit_product.php?id=$id&error=" . urlencode("Invalid image file type."));
            exit();
        }
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = rtrim($upload_dir, '/\\') . DIRECTORY_SEPARATOR . $file_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_url = 'uploads/products/' . $file_name;
            $sql .= ", image_url = :image_url";
            $params[':image_url'] = $image_url;

            // Delete old image if exists
            $old_image_sql = "SELECT image_url FROM products WHERE id = :id";
            $old_image_stmt = oci_parse($conn, $old_image_sql);
            oci_bind_by_name($old_image_stmt, ":id", $id);
            oci_execute($old_image_stmt);
            $old_image = oci_fetch_assoc($old_image_stmt);
            oci_free_statement($old_image_stmt);
            if ($old_image && $old_image['IMAGE_URL']) {
                $old_image_path = ltrim($old_image['IMAGE_URL'], '/\\');
                $old_file = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $old_image_path);
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
        }
    }

    $sql .= " WHERE id = :id";

    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":name", $name);
    oci_bind_by_name($stmt, ":category", $category);
    oci_bind_by_name($stmt, ":price", $price);
    oci_bind_by_name($stmt, ":description", $description);
    oci_bind_by_name($stmt, ":is_available", $is_available);
    oci_bind_by_name($stmt, ":id", $id);
    if (isset($params[':image_url'])) {
        oci_bind_by_name($stmt, ":image_url", $params[':image_url']);
    }

    if (oci_execute($stmt)) {
        oci_commit($conn);
        header("Location: manage_products.php?success=Product updated successfully");
    } else {
        $e = oci_error($stmt);
        header("Location: edit_product.php?id=$id&error=" . urlencode($e['message']));
    }

    oci_free_statement($stmt);
    oci_close($conn);
}
?>