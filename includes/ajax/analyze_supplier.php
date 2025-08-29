<?php
// Set the content type to JSON for the response
header('Content-Type: application/json');

// Include necessary function files
require_once '../functions/auth.php';
require_once '../functions/bids.php';
require_once '../config/db.php'; // Include database connection

// Ensure the user is logged in before proceeding
requireLogin();

// Check if a po_id was provided in the URL
if (isset($_GET['po_id'])) {
    // Sanitize the input to ensure it's an integer
    $po_id = intval($_GET['po_id']);
    $conn = getDbConnection();

    // --- Check for a cached analysis first ---
    $cacheExpiryHours = 24; // Cache results for 24 hours
    $stmt = $conn->prepare(
        "SELECT analysis_text FROM procurement_analysis_cache
         WHERE po_id = ? AND cached_at > NOW() - INTERVAL ? HOUR"
    );
    $stmt->bind_param("ii", $po_id, $cacheExpiryHours);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $cachedData = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        // Append "(from cache)" to the analysis text to show it's working
        echo json_encode(['success' => true, 'analysis' => $cachedData['analysis_text']]);
        exit();
    }
    $stmt->close();


    // --- If no cache, proceed to get new analysis ---
    $bids = getBidsForPO($po_id);

    if (empty($bids)) {
        $conn->close();
        echo json_encode(['success' => false, 'error' => 'No suppliers have bid on this item yet.']);
        exit();
    }

    $supplier_performance_data = [];
    foreach ($bids as $bid) {
        $history = getBiddingHistoryBySupplier($bid['supplier_id']);
        $total_bids = count($history);
        $awarded_bids = 0;
        foreach ($history as $h) {
            if ($h['status'] === 'Awarded') {
                $awarded_bids++;
            }
        }
        $win_rate = ($total_bids > 0) ? round(($awarded_bids / $total_bids) * 100) : 0;

        $supplier_performance_data[] = "- **Supplier**: {$bid['supplier_name']}\\n  - **Total Bids**: {$total_bids}\\n  - **Win Rate**: {$win_rate}%\\n  - **Current Bid**: ₱" . number_format($bid['bid_amount'], 2);
    }

    // --- **NEW, SIMPLIFIED PROMPT** ---
    $prompt = "As a procurement analyst, evaluate the following suppliers. For each, provide a one-sentence summary. Conclude with a final recommendation in a single sentence starting with 'Recommendation:'. Here is the performance data:\\n\\n" . implode("\\n", $supplier_performance_data);


    // Gemini API Integration
    $apiKey = 'AIzaSyAmMMCjXOlS7tSXFmF9jiJOxa7OW3gsjO0'; // Your Gemini API Key
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
        $analysis = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Could not retrieve analysis from AI.';

        // --- Save the new analysis to the cache ---
        $stmt_cache = $conn->prepare(
            "INSERT INTO procurement_analysis_cache (po_id, analysis_text, cached_at)
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE analysis_text = VALUES(analysis_text), cached_at = NOW()"
        );
        $stmt_cache->bind_param("is", $po_id, $analysis);
        $stmt_cache->execute();
        $stmt_cache->close();

        echo json_encode(['success' => true, 'analysis' => $analysis]);
    } else {
        echo json_encode(['success' => false, 'error' => "Failed to get analysis from AI. HTTP Code: {$http_code}."]);
    }

    $conn->close();

} else {
    echo json_encode(['success' => false, 'error' => 'No Purchase Order ID was provided.']);
}
?>