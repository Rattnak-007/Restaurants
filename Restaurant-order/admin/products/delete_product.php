<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

$product_id = $_GET['id'] ?? null;

if ($product_id) {
    try {
        // Check if product has related order_items
        $check_sql = "SELECT COUNT(*) AS cnt FROM order_items WHERE product_id = :id";
        $check_stmt = oci_parse($conn, $check_sql);
        oci_bind_by_name($check_stmt, ":id", $product_id);
        oci_execute($check_stmt);
        $check = oci_fetch_assoc($check_stmt);
        oci_free_statement($check_stmt);

        if ($check && $check['CNT'] > 0) {
            // Soft delete if related order_items exist
            $sql = "UPDATE products SET is_deleted = 1 WHERE id = :id";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":id", $product_id);
            if (oci_execute($stmt)) {
                oci_commit($conn);
                oci_free_statement($stmt);
                oci_close($conn);
                header("Location: manage_products.php?success=Product soft deleted (has order history)");
                exit();
            } else {
                $error = oci_error($stmt);
                oci_free_statement($stmt);
                oci_close($conn);
                header("Location: manage_products.php?error=" . urlencode($error['message']));
                exit();
            }
        } else {
            // Get image URL before deleting product
            $img_sql = "SELECT image_url FROM products WHERE id = :id";
            $img_stmt = oci_parse($conn, $img_sql);
            oci_bind_by_name($img_stmt, ":id", $product_id);
            oci_execute($img_stmt);
            $product = oci_fetch_assoc($img_stmt);
            oci_free_statement($img_stmt);

            // Hard delete if no order_items
            $sql = "DELETE FROM products WHERE id = :id";
            $stmt = oci_parse($conn, $sql);
            oci_bind_by_name($stmt, ":id", $product_id);
            if (oci_execute($stmt)) {
                // Delete image file if exists
                if ($product && $product['IMAGE_URL']) {
                    $imageRelativePath = ltrim($product['IMAGE_URL'], '/\\');
                    $imageDiskPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imageRelativePath);
                    if (file_exists($imageDiskPath)) {
                        unlink($imageDiskPath);
                    }
                }
                oci_commit($conn);
                oci_free_statement($stmt);
                oci_close($conn);
                header("Location: manage_products.php?success=Product deleted successfully");
                exit();
            } else {
                $error = oci_error($stmt);
                oci_free_statement($stmt);
                oci_close($conn);
                header("Location: manage_products.php?error=" . urlencode($error['message']));
                exit();
            }
        }
    } catch (Exception $e) {
        oci_rollback($conn);
        oci_close($conn);
        header("Location: manage_products.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}

oci_close($conn);
header("Location: manage_products.php");
exit();
?>
