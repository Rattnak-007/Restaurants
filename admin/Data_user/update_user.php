<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    
    // Validation
    if (empty($name) || empty($email) || empty($role)) {
        header("Location: edit_user.php?id=$id&error=All fields are required");
        exit();
    }
    
    // Check if email exists for other users
    $check_sql = "SELECT id FROM users WHERE email = :email AND id != :id AND is_deleted = 0";
    $check_stmt = oci_parse($conn, $check_sql);
    oci_bind_by_name($check_stmt, ":email", $email);
    oci_bind_by_name($check_stmt, ":id", $id);
    oci_execute($check_stmt);
    
    if (oci_fetch($check_stmt)) {
        header("Location: edit_user.php?id=$id&error=Email already exists");
        exit();
    }
    oci_free_statement($check_stmt);
    
    // Update user
    $sql = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
    $stmt = oci_parse($conn, $sql);
    
    oci_bind_by_name($stmt, ":name", $name);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":role", $role);
    oci_bind_by_name($stmt, ":id", $id);
    
    if (oci_execute($stmt)) {
        oci_commit($conn);
        header("Location: ../dashboard.php?success=User updated successfully");
    } else {
        $e = oci_error($stmt);
        header("Location: edit_user.php?id=$id&error=" . urlencode($e['message']));
    }
    
    oci_free_statement($stmt);
}

oci_close($conn);
?>
