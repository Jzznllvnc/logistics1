<?php
// Logistic1/includes/config/db.php

// --- Database Configuration ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'Slate1'); // <-- IMPORTANT: Change this
define('DB_USER', 'root'); // <-- IMPORTANT: Change this
define('DB_PASS', ''); // <-- IMPORTANT: Change this

/**
 * Establishes a connection to the database.
 * @return mysqli|null The mysqli connection object on success, or null on failure.
 */
function getDbConnection() {
    // Create a new mysqli connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check for connection errors
    if ($conn->connect_error) {
        // In a real application, you should log this error instead of echoing it.
        // For this example, we'll terminate the script with an error message.
        error_log("Database Connection Failed: " . $conn->connect_error);
        die("Sorry, we're having some technical difficulties. Please try again later.");
    }

    return $conn;
}

?>