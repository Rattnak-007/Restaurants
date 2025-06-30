<?php
session_start();
$conn = require_once('config/database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=Please fill in all fields");
        exit();
    }

    // Prepare the query
    $sql = "SELECT id, name, email, password, role FROM users WHERE email = :email AND is_deleted = 0";
    $stmt = oci_parse($conn, $sql);

    // Bind parameters
    oci_bind_by_name($stmt, ":email", $email);

    // Execute the query
    oci_execute($stmt);

    // Fetch the result
    $user = oci_fetch_assoc($stmt);

    if ($user && password_verify($password, $user['PASSWORD'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['user_name'] = $user['NAME'];
        $_SESSION['user_email'] = $user['EMAIL'];
        $_SESSION['user_role'] = $user['ROLE'];

        // Set cookie for name
        setcookie('user_name', $user['NAME'], time() + (86400 * 30), "/"); // 30 days

        // Generate and store remember token if remember me is checked
        if (isset($_POST['remember_me'])) {
            $token = bin2hex(random_bytes(32));
            $update_sql = "UPDATE users SET remember_token = :token WHERE id = :id";
            $update_stmt = oci_parse($conn, $update_sql);
            oci_bind_by_name($update_stmt, ":token", $token);
            oci_bind_by_name($update_stmt, ":id", $user['ID']);
            oci_execute($update_stmt);
            oci_commit($conn);
            oci_free_statement($update_stmt);

            setcookie('remember_token', $token, time() + (86400 * 30), "/");
        }

        // Redirect based on role
        if ($user['ROLE'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: welcome.php");
        }
        exit();
    } else {
        header("Location: login.php?error=Invalid email or password");
        exit();
    }

    // Free the statement
    oci_free_statement($stmt);
}

// Close the connection
oci_close($conn);
