<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Leave empty for default XAMPP/WAMP
define('DB_NAME', 'invoice_system');

// Create database connection
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Company details (customize these)
define('COMPANY_NAME', 'Your Business Name');
define('COMPANY_EMAIL', 'info@yourbusiness.com');
define('COMPANY_PHONE', '+254 700 000 000');
define('COMPANY_ADDRESS', '123 Business St, Nairobi, Kenya');

// Tax rate (16% VAT in Kenya)
define('TAX_RATE', 0.16);
?>