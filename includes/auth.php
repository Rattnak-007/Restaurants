<?php
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id'])) {
        if (isset($_COOKIE['remember_token'])) {
            if (!autoLogin($_COOKIE['remember_token'])) {
                header("Location: login.php");
                exit();
            }
        } else {
            header("Location: login.php");
            exit();
        }
    }
    return true;
}

function checkAdminAuth() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // If user is not logged in, check for remember_me cookie
    if (!isset($_SESSION['user_id'])) {
        if (isset($_COOKIE['remember_token'])) {
            autoLogin($_COOKIE['remember_token']);
        }
    }
    
    // Check if user is admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }
}

function autoLogin($token) {
    $conn = require_once(__DIR__ . '/../config/database.php');
    
    $sql = "SELECT id, name, email, role FROM users 
            WHERE remember_token = :token AND is_deleted = 0";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":token", $token);
    oci_execute($stmt);
    
    $user = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);
    
    if ($user) {
        $_SESSION['user_id'] = $user['ID'];
        $_SESSION['user_name'] = $user['NAME'];
        $_SESSION['user_email'] = $user['EMAIL'];
        $_SESSION['user_role'] = $user['ROLE'];
        setcookie('user_name', $user['NAME'], time() + (86400 * 30), "/");
        return true;
    }
    
    return false;
}

function logout() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Clear remember_me token in database if it exists
    if (isset($_COOKIE['remember_token'])) {
        require_once(__DIR__ . '/../config/database.php');
        $token = $_COOKIE['remember_token'];
        $sql = "UPDATE users SET remember_token = NULL WHERE remember_token = :token";
        $stmt = oci_parse($conn, $sql);
        oci_bind_by_name($stmt, ":token", $token);
        oci_execute($stmt);
        oci_free_statement($stmt);
        
        // Remove remember_me cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Add JavaScript to clear cart before redirecting
    echo "<script>
        localStorage.removeItem('cart');
        window.location.href = 'login.php';
    </script>";
    
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
    
    // Remove the name cookie
    setcookie('user_name', '', time() - 3600, '/');
    
    // Don't redirect here since we're using JavaScript
    exit();
}
?>
