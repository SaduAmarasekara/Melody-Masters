<?php
// Initialize the session to access existing data
session_start();

// Unset all session variables to clear user data from memory
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// This is a security best practice to ensure the session cannot be hijacked.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session on the server side
session_destroy();

// Redirect the user to the login page with a success message in the URL
header("Location: login.php?status=logout_success");
exit();
?>