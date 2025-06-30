<?php
// First, check if Oracle extensions are installed
if (!extension_loaded('oci8')) {
    die("Oracle extensions are not installed. Please install PHP OCI8 extension first.");
}

// Connection parameters
$host = 'localhost';
$port = '1521';
$sid = 'XE';  // or your Oracle SID/Service name
$username = "Restaurantdb";
$password = "E12345e";

// Build the connection string
$connectionString = sprintf(
    "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=%s)(PORT=%s))(CONNECT_DATA=(SID=%s)))",
    $host,
    $port,
    $sid
);

try {
    // First try connecting with OCI8
    if (function_exists('oci_connect')) {
        $conn = oci_connect($username, $password, $connectionString, 'AL32UTF8');
        if (!$conn) {
            $e = oci_error();
            throw new Exception($e['message']);
        }
    } else {
        // Fallback to PDO_OCI
        $tns = $connectionString;
        $conn = new PDO("oci:dbname=" . $tns . ";charset=AL32UTF8", $username, $password, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_CASE => PDO::CASE_NATURAL
        ));
        
        // Set Oracle session properties
        $conn->exec("ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY-MM-DD HH24:MI:SS'");
    }
    
    // Remove or comment out this line in production
     //echo "Connected successfully to Oracle!";
    
    // Return the connection
    return $conn;
    
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n" .
        "Please ensure:\n" .
        "1. Oracle client is installed\n" .
        "2. PHP OCI8 extension is installed\n" .
        "3. Oracle service is running\n" .
        "4. Credentials are correct");
}
?>
