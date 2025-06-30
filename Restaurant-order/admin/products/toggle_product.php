<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;
if (!$id || !in_array($action, ['enable', 'disable'])) {
    header('Location: manage_products.php?error=Invalid request');
    exit();
}

$is_available = ($action === 'enable') ? 1 : 0;
$sql = "UPDATE products SET is_available = :is_available WHERE id = :id";
$stmt = oci_parse($conn, $sql);
oci_bind_by_name($stmt, ':is_available', $is_available);
oci_bind_by_name($stmt, ':id', $id);
if (oci_execute($stmt)) {
    oci_commit($conn);
    $msg = $action === 'enable' ? 'Product enabled successfully' : 'Product disabled successfully';
    header('Location: manage_products.php?success=' . urlencode($msg));
    exit();
} else {
    $e = oci_error($stmt);
    header('Location: manage_products.php?error=' . urlencode($e['message']));
    exit();
}
oci_free_statement($stmt);
oci_close($conn);
?>