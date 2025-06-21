<?php
require_once('../includes/auth.php');
checkAdminAuth();
require_once('../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    
    // Handle file upload
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload_dir = '../uploads/products/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
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
