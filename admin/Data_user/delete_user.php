<?php
require_once('../../includes/auth.php');
require_once('../../config/database.php');

$user_id = $_GET['id'] ?? null;

if ($user_id) {
    // Check if user is trying to delete themselves
    if ($user_id == $_SESSION['user_id']) {
        header("Location: ../dashboard.php?error=You cannot delete your own account");
        exit();
    }
    try {
        // First, check if user has any orders
        $check_sql = "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id";
        $check_stmt = oci_parse($conn, $check_sql);
        oci_bind_by_name($check_stmt, ":user_id", $user_id);
        oci_execute($check_stmt);
        $result = oci_fetch_assoc($check_stmt);

        if ($result && $result['COUNT'] > 0) {
            // User has orders - use soft delete
            $sql = "UPDATE users SET is_deleted = 1 WHERE id = :id";
        } else {
            // No orders - can hard delete
            $sql = "DELETE FROM users WHERE id = :id";
        }

        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":id", $user_id);

        if (oci_execute($stmt)) {
            oci_commit($conn);
            header("Location: ../dashboard.php?success=User deleted successfully");
            exit();
        } else {
            throw new Exception(oci_error($stmt)['message']);
        }
    } catch (Exception $e) {
        oci_rollback($conn);
        header("Location: ../dashboard.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../dashboard.php?error=Invalid user ID");
    exit();
}
