<?php
session_start();
$conn = require_once('config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid CSRF token.";
        header("Location: register.php");
        exit();
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        // Begin transaction
        // oci_commit($conn); // Removed to avoid committing unrelated work

        // Check if email exists first
        $check_sql = "SELECT COUNT(*) as count FROM users WHERE email = :email";
        $check_stmt = oci_parse($conn, $check_sql);
        oci_bind_by_name($check_stmt, ":email", $email);
        oci_execute($check_stmt);
        $row = oci_fetch_assoc($check_stmt);

        if ($row['COUNT'] > 0) {
            throw new Exception("Email already exists");
        }
        oci_free_statement($check_stmt);

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user without explicitly getting sequence
        $sql = "INSERT INTO users (name, email, password, role) 
                VALUES (:name, :email, :password, 'user') 
                RETURNING id INTO :inserted_id";

        $stmt = oci_parse($conn, $sql);

        // Bind parameters
        oci_bind_by_name($stmt, ":name", $name);
        oci_bind_by_name($stmt, ":email", $email);
        oci_bind_by_name($stmt, ":password", $hashed_password);
        oci_bind_by_name($stmt, ":inserted_id", $new_user_id, -1, SQLT_INT);

        if (!oci_execute($stmt)) {
            throw new Exception(oci_error($stmt)['message']);
        }

        // Commit transaction
        oci_commit($conn);

        // Set session variables using the returned ID
        $_SESSION['user_id'] = $new_user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'user';

        // Set cookie
        setcookie('user_name', $name, time() + (86400 * 30), "/");

        // Free statement
        oci_free_statement($stmt);

        // Redirect to welcome page
        header("Location: welcome.php");
        exit();
    } catch (Exception $e) {
        oci_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
        header("Location: register.php");
        exit();
    }
}

// Close connection
oci_close($conn);
