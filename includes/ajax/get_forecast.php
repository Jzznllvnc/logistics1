<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include necessary function files
require_once '../functions/auth.php';
require_once '../functions/inventory.php';

// Ensure the user is logged in before proceeding
requireLogin();

// Check if an item_id was provided in the URL
if (isset($_GET['item_id'])) {
    // Sanitize the input to ensure it's an integer
    $itemId = intval($_GET['item_id']);
    
    // Call the forecasting function from inventory.php
    $forecastResult = forecastStock($itemId);
    
    // Check if the forecast result contains an error message
    if (strpos($forecastResult, 'Error') !== false || strpos($forecastResult, 'failed') !== false || strpos($forecastResult, 'Not enough data') !== false || strpos($forecastResult, 'Invalid response') !== false) {
        // If it's an error, return a JSON object indicating failure
        echo json_encode(['success' => false, 'error' => $forecastResult]);
    } else {
        // If successful, return the forecast data
        echo json_encode(['success' => true, 'forecast' => $forecastResult]);
    }
} else {
    // If no item_id was provided, return an error
    echo json_encode(['success' => false, 'error' => 'No item ID was provided.']);
}
?>