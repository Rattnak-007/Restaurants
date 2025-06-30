<?php
require_once('includes/auth.php');

// Clear cart first
echo "<script>localStorage.removeItem('cart');</script>";

// Then logout
logout();
