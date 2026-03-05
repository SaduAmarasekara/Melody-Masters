<?php
session_start();
include 'db_connect.php';

/**
 * Business Rule: Secure File Delivery System
 * Requirement: Only authenticated users who have successfully paid for 
 * digital products should be allowed to access the download.
 */

// 1. Authentication Check: Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access Denied: You must be logged in to download files.");
}

// 2. Input Validation: Check if Order ID and Product ID are present in the request
if (!isset($_GET['oid']) || !isset($_GET['pid'])) {
    die("Invalid Request: Missing parameters.");
}

$order_id = mysqli_real_escape_string($conn, $_GET['oid']);
$product_id = mysqli_real_escape_string($conn, $_GET['pid']);
$user_id = $_SESSION['user_id'];

/**
 * 3. Security & Verification: 
 * Perform a relational check to verify that this specific user 
 * actually purchased this specific product in the given order.
 */
$verify_query = "SELECT o.order_id 
                 FROM orders o 
                 JOIN order_items oi ON o.order_id = oi.order_id 
                 WHERE o.order_id = '$order_id' 
                 AND o.user_id = '$user_id' 
                 AND oi.product_id = '$product_id'";

$result = $conn->query($verify_query);

if ($result->num_rows > 0) {
    // 4. Data Retrieval: Get product name to create a user-friendly filename
    $prod_sql = "SELECT product_name FROM products WHERE product_id = '$product_id'";
    $prod_res = $conn->query($prod_sql);
    $product = $prod_res->fetch_assoc();
    
    // Formatting the filename (e.g., "Piano_Sonata_SheetMusic.pdf")
    $safe_name = str_replace(' ', '_', $product['product_name']);
    $filename = $safe_name . "_SheetMusic.pdf";

    /**
     * 5. File Transmission Headers:
     * These headers tell the browser to download the content as a PDF file
     * rather than displaying it as plain text.
     */
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // For the assignment, we output the file content or a secure placeholder.
    // In a production environment, you would use: readfile("uploads/pdf_files/" . $item['file_name']);
    echo "%PDF-1.4 [Secure Digital Content for " . $product['product_name'] . "]";
    exit();
} else {
    // Unauthorized access attempt
    die("Permission Denied: You do not have a valid purchase record for this item.");
}
?>