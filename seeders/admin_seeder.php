<?php
$conn = require_once('../config/database.php');

$name = 'admin';
$email = 'admin@gmail.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

// Check if admin already exists
$check_sql = "SELECT COUNT(*) AS CNT FROM users WHERE email = :email";
$check_stmt = oci_parse($conn, $check_sql);
oci_bind_by_name($check_stmt, ":email", $email);
oci_execute($check_stmt);
$row = oci_fetch_assoc($check_stmt);
oci_free_statement($check_stmt);

if ($row && $row['CNT'] > 0) {
    echo "Admin user already exists.";
} else {
    // Prepare the SQL statement
    $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
    $stmt = oci_parse($conn, $sql);

    // Bind the parameters
    oci_bind_by_name($stmt, ":name", $name);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":password", $password);
    oci_bind_by_name($stmt, ":role", $role);

    // Execute the statement
    $success = oci_execute($stmt);

    if ($success) {
        echo "Admin user created successfully";
        // Commit the transaction
        oci_commit($conn);
    } else {
        $e = oci_error($stmt);
        echo "Error creating admin user: " . $e['message'];
        // Rollback the transaction
        oci_rollback($conn);
    }

    // Free the statement
    oci_free_statement($stmt);
}

// Close the connection
oci_close($conn);
?>
