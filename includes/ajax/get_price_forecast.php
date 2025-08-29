<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include necessary function files
require_once '../functions/auth.php';
require_once '../functions/bids.php'; // Contains our getItemPriceHistory function
require_once '../config/db.php';     // Include database connection for caching

// Ensure the user is logged in before proceeding
requireLogin();

// Check if an item_name was provided
if (isset($_GET['item_name'])) {
    $item_name = trim($_GET['item_name']);
    $conn = getDbConnection();

    // --- Get Price History ---
    $history = getItemPriceHistory($item_name);

    if (count($history) < 3) {
        $conn->close();
        echo json_encode(['success' => false, 'error' => 'Not enough historical pricing data to generate a forecast for this item.']);
        exit();
    }

    $price_data_points = [];
    foreach ($history as $record) {
        $date = date('F Y', strtotime($record['bid_date']));
        $price_data_points[] = "- **{$date}**: ₱" . number_format($record['bid_amount'], 2);
    }

    // --- **NEW PROMPT for Price Forecasting** ---
    $prompt = "As a supply chain analyst, analyze the following price history for '{$item_name}'. Provide a brief, one-sentence trend analysis.\\n\\nPrice History:\\n" . implode("\\n", $price_data_points);

    // Gemini API Integration
    $apiKey = 'AIzaSyAmMMCjXOlS7tSXFmF9jiJOxa7OW3gsjO0';
    $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;
    $payload = json_encode(["contents" => [["parts" => [["text" => $prompt]]]]]);

    $ch = curl_init($geminiApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $response) {
        $result = json_decode($response, true);
        $analysis = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Could not retrieve price forecast from AI.';
        echo json_encode(['success' => true, 'forecast' => $analysis]);
    } else {
        echo json_encode(['success' => false, 'error' => "Failed to get forecast from AI. HTTP Code: {$http_code}."]);
    }

    $conn->close();

} else {
    echo json_encode(['success' => false, 'error' => 'No item name was provided.']);
}
?>