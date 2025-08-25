<?php
// Logistic1/includes/functions/inventory.php

require_once __DIR__ . '/../config/db.php';

/**
 * Retrieves all items from the inventory.
 * @return array An array of inventory items.
 */
function getInventory() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT id, item_name, quantity, last_updated FROM inventory ORDER BY item_name ASC");
    $items = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $items;
}

/**
 * Handles stocking in an item (adding or creating).
 * @param string $itemName The name of the item.
 * @param int $quantity The quantity to add.
 * @return bool True on success, false on failure.
 */
function stockIn($itemName, $quantity) {
    if (empty($itemName) || !is_numeric($quantity) || $quantity <= 0) {
        return false;
    }
    
    $conn = getDbConnection();
    $quantity = (int)$quantity;

    $stmt = $conn->prepare(
        "INSERT INTO inventory (item_name, quantity) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)"
    );
    $stmt->bind_param("si", $itemName, $quantity);
    
    $success = $stmt->execute();
    
    $stmt->close();
    $conn->close();
    
    return $success;
}

/**
 * Handles stocking out an item (reducing quantity).
 * @param string $itemName The name of the item.
 * @param int $quantity The quantity to remove.
 * @return string "Success" on success, or an error message on failure.
 */
function stockOut($itemName, $quantity) {
    if (empty($itemName) || !is_numeric($quantity) || $quantity <= 0) {
        return "Invalid input.";
    }

    $conn = getDbConnection();
    $quantity = (int)$quantity;

    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE item_name = ?");
    $stmt->bind_param("s", $itemName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        return "Item not found in inventory.";
    }

    $currentStock = $result->fetch_assoc()['quantity'];
    if ($currentStock < $quantity) {
        $stmt->close();
        $conn->close();
        return "Stock-out failed. Only $currentStock items available.";
    }

    $updateStmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_name = ?");
    $updateStmt->bind_param("is", $quantity, $itemName);
    
    $message = $updateStmt->execute() ? "Success" : "An error occurred during stock-out.";

    $stmt->close();
    $updateStmt->close();
    $conn->close();
    
    return $message;
}

/**
 * Updates an inventory item's name. (Admin Only)
 * @param int $id The ID of the item to update.
 * @param string $itemName The new name for the item.
 * @return bool True on success, false on failure.
 */
function updateInventoryItem($id, $itemName) {
    if (empty($itemName) || !is_numeric($id)) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE inventory SET item_name = ? WHERE id = ?");
    $stmt->bind_param("si", $itemName, $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Deletes an inventory item. (Admin Only)
 * @param int $id The ID of the item to delete.
 * @return bool True on success, false on failure.
 */
function deleteInventoryItem($id) {
    if (!is_numeric($id)) {
        return false;
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

/**
 * Gets the total count of inventory items.
 * @return int The total number of items in inventory.
 */
function getTotalInventoryCount() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM inventory");
    $count = 0;
    
    if ($result) {
        $row = $result->fetch_assoc();
        $count = (int)$row['total'];
    }
    
    $conn->close();
    return $count;
}

/**
 * Retrieves paginated inventory items.
 * @param int $offset The starting point for the query.
 * @param int $limit The maximum number of items to retrieve.
 * @return array An array of inventory items.
 */
function getPaginatedInventory($offset, $limit) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id, item_name, quantity, last_updated FROM inventory ORDER BY item_name ASC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    
    $stmt->close();
    $conn->close();
    return $items;
}


/**
 * Gets the historical inventory data for a specific item.
 * @param int $itemId The ID of the item.
 * @return array The historical data.
 */
function getInventoryHistory($itemId) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT quantity, `timestamp` FROM inventory_history WHERE item_id = ? ORDER BY `timestamp` ASC");
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $history;
}

/**
 * Fetches forecasts from cache or Gemini API if the cache is stale.
 */
function getAutomaticForecasts(array $inventoryItems): array
{
    $conn = getDbConnection();
    $finalForecasts = [];
    $itemsToFetchFromApi = [];
    $cacheExpiryHours = 24; // Cache results for 24 hours

    // 1. Check the cache first for each item
    foreach ($inventoryItems as $item) {
        $stmt = $conn->prepare(
            "SELECT analysis, action, cached_at FROM inventory_forecast_cache 
             WHERE item_id = ? AND cached_at > NOW() - INTERVAL ? HOUR"
        );
        $stmt->bind_param("ii", $item['id'], $cacheExpiryHours);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $cachedData = $result->fetch_assoc();
            $finalForecasts[$item['id']] = [
                'analysis' => $cachedData['analysis'],
                'action' => $cachedData['action']
            ];
        } else {
            $itemsToFetchFromApi[] = $item;
        }
        $stmt->close();
    }

    // 2. If there are items that need a fresh forecast, call the API in a batch
    if (!empty($itemsToFetchFromApi)) {
        $apiForecasts = fetchForecastsFromGeminiApi($itemsToFetchFromApi);
        
        // 3. Update the cache and merge the new results
        foreach ($apiForecasts as $itemId => $forecastData) {
            $finalForecasts[$itemId] = $forecastData;
            
            // Use raw values for database insertion to avoid saving HTML spans
            $raw_analysis = strip_tags($forecastData['analysis']);
            $raw_action = strip_tags($forecastData['action']);

            $stmt = $conn->prepare(
                "INSERT INTO inventory_forecast_cache (item_id, analysis, action, cached_at) 
                 VALUES (?, ?, ?, NOW()) 
                 ON DUPLICATE KEY UPDATE analysis = VALUES(analysis), action = VALUES(action), cached_at = NOW()"
            );
            $stmt->bind_param("iss", $itemId, $raw_analysis, $raw_action);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    $conn->close();
    return $finalForecasts;
}

/**
 * Helper function to perform the actual API call.
 */
function fetchForecastsFromGeminiApi(array $inventoryItems): array
{
    $apiKey = 'AIzaSyAmMMCjXOlS7tSXFmF9jiJOxa7OW3gsjO0';

    if ($apiKey === 'YOUR_GEMINI_API_KEY') {
        return array_fill_keys(array_column($inventoryItems, 'id'), [
            'analysis' => "<span class='text-red-500'>Gemini Key Missing</span>",
            'action' => "<span class='text-red-500'>Error</span>"
        ]);
    }

    $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;
    
    $apiForecasts = [];
    $itemsWithHistory = [];
    
    foreach ($inventoryItems as $item) {
        $history = getInventoryHistory($item['id']);
        if (count($history) < 5) {
            $apiForecasts[$item['id']] = [
                'analysis' => "<span class='text-gray-400'>No Data</span>",
                'action' => "<span class='text-gray-400'>N/A</span>"
            ];
        } else {
            $itemsWithHistory[$item['id']] = ['name' => $item['item_name'], 'history' => $history];
        }
    }

    if (empty($itemsWithHistory)) {
        return $apiForecasts;
    }

    $batchPrompt = "As a supply chain analyst, analyze the following inventory items. For each item, provide your output as a JSON object with two keys: 'analysis' (a brief, one-sentence summary of the stock trend) and 'action' (a concise, two-word recommended action like 'Monitor Stock', 'Reorder Soon', or 'Expedite Reorder'). Return a single minified JSON array containing one object for each item.\n\n";
    foreach ($itemsWithHistory as $id => $itemData) {
        $batchPrompt .= "Item ID: {$id}\nItem Name: {$itemData['name']}\nData:\n";
        foreach ($itemData['history'] as $record) {
            $date = date('Y-m-d', strtotime($record['timestamp']));
            $batchPrompt .= "- Date: {$date}, Quantity: {$record['quantity']}\n";
        }
        $batchPrompt .= "\n";
    }

    $data = [
        "contents" => [["parts" => [["text" => $batchPrompt]]]],
        "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.2]
    ];
    $payload = json_encode($data);

    $ch = curl_init($geminiApiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200) {
        $result = json_decode($response, true);
        $json_string = $result['candidates'][0]['content']['parts'][0]['text'] ?? '[]';
        $batch_analysis = json_decode($json_string, true);

        foreach ($batch_analysis as $index => $analysis_data) {
            $itemId = array_keys($itemsWithHistory)[$index];
            $analysis_text = htmlspecialchars($analysis_data['analysis'] ?? 'No analysis available.');
            $action_text = htmlspecialchars($analysis_data['action'] ?? 'N/A');

            $analysis_html = $analysis_text;
            if (stripos($analysis_text, 'declin') !== false) $analysis_html = "<span class='text-amber-500'>{$analysis_text}</span>";
            if (stripos($analysis_text, 'increas') !== false) $analysis_html = "<span class='text-emerald-500'>{$analysis_text}</span>";
            
            $action_html = $action_text;
            if (stripos($action_text, 'Reorder') !== false) $action_html = "<strong class='text-amber-500'>{$action_text}</strong>";

            $apiForecasts[$itemId] = ['analysis' => $analysis_html, 'action' => $action_html];
        }
    } else {
        foreach ($itemsWithHistory as $id => $item) {
            $error_detail = !empty($curl_error) ? $curl_error : "HTTP Code: {$http_code}";
            $apiForecasts[$id] = [
                'analysis' => "<span class='text-red-500' title='{$error_detail}'>API Error</span>",
                'action' => "<span class='text-red-500'>Error</span>"
            ];
        }
    }
    
    return $apiForecasts;
}
?>