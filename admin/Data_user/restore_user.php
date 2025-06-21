<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    $sql = "UPDATE users SET is_deleted = 0 WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":id", $user_id);
    
    if (oci_execute($stmt)) {
        oci_commit($conn);
        header("Location: ../dashboard.php?success=User restored successfully");
    } else {
        $e = oci_error($stmt);
        header("Location: ../dashboard.php?error=" . urlencode($e['message']));
    }
    
    oci_free_statement($stmt);
}

oci_close($conn);
header("Location: ../dashboard.php");
?>
