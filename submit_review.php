<?php
/**
 * REVIEW SUBMISSION HANDLER
 * This script processes product reviews submitted by customers.
 * It enforces business rules for data integrity and security.
 */

session_start();
include 'db_connect.php'; // Ensure database connection is available

// 1. SECURITY CHECK: Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    // If not authenticated, redirect to login page
    header("Location: login.php");
    exit();
}

/**
 * 2. FORM PROCESSING: Check if the submit button was clicked
 * The 'submit_review' name must match the name attribute of your form button.
 */
if (isset($_POST['submit_review'])) {
    
    // Retrieve User ID from session
    $user_id = $_SESSION['user_id'];
    
    // SANITIZATION: Protect against SQL Injection attacks
    $product_id = mysqli_real_escape_string($conn, $_POST['product_id']);
    $comment    = mysqli_real_escape_string($conn, $_POST['comment']);
    
    // Handle rating: Default to 5 if not provided by the form
    $rating = isset($_POST['rating']) ? mysqli_real_escape_string($conn, $_POST['rating']) : 5;

    /**
     * 3. VERIFICATION LOGIC (RELATIONAL INTEGRITY):
     * As per business requirements, only users who have a 'Delivered' order 
     * for this specific product can leave a review.
     */
    $check_purchase = "SELECT oi.product_id 
                       FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.order_id 
                       WHERE o.user_id = '$user_id' 
                       AND oi.product_id = '$product_id' 
                       AND o.order_status = 'Delivered'";

    $result = $conn->query($check_purchase);

    // If a matching record is found, the user is authorized to review
    if ($result && $result->num_rows > 0) {
        
        /**
         * 4. DATA INSERTION:
         * Inserts the validated review into the 'reviews' table.
         * Uses NOW() to record the exact time of the review.
         */
        $query = "INSERT INTO reviews (user_id, product_id, rating, comment, review_date) 
                  VALUES ('$user_id', '$product_id', '$rating', '$comment', NOW())";
        
        if ($conn->query($query)) {
            // SUCCESS: Redirect to product page with success feedback
            header("Location: product_details.php?id=$product_id&status=review_success");
            exit();
        } else {
            // ERROR: Provide technical error message if the query fails (Debugging)
            die("Database Error: " . $conn->error);
        }
    } else {
        /**
         * 5. UNAUTHORIZED ATTEMPT:
         * If the user hasn't bought the item or it's not delivered yet, deny submission.
         */
        header("Location: product_details.php?id=$product_id&status=unauthorized_review");
        exit();
    }
} else {
    // If the script is accessed directly without POST data, redirect to shop
    header("Location: shop.php");
    exit();
}
?>