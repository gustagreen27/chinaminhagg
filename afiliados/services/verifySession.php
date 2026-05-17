<?php
include_once "../../ad-min/services/database.php";

// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if auth token exists in the session
if (!empty($_SESSION['auth_token'])) {
    $token = $_SESSION['auth_token'];

    // Use prepared statement to prevent SQL injection
    $query = "SELECT * FROM usuarios WHERE token = ? AND banido = 0";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $_SESSION['auth_token'] = $row; // Update session with user data
        } else {
            // Token not valid or user inactive
            session_destroy();
            header('Location: /afiliados/login');
            exit();
        }

        $stmt->close();
    } else {
        // Handle query preparation error
        die("Database query error: " . $mysqli->error);
    }
} else {
    // No auth token, destroy session and redirect
    session_destroy();
    header('Location: /afiliados/login');
    exit();
}
