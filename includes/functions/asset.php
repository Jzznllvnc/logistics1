<?php
// Logistic1/includes/functions/asset.php
require_once __DIR__ . '/../config/db.php';

// --- Asset CRUD Functions ---
function getAllAssets() {
    $conn = getDbConnection();
    $result = $conn->query("SELECT * FROM assets ORDER BY asset_name ASC");
    $assets = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $assets;
}

function createAsset($name, $type, $purchase_date, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO assets (asset_name, asset_type, purchase_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $type, $purchase_date, $status);
    $success = $stmt->execute();
    if ($success) {
        $asset_id = $conn->insert_id;
        $hist_stmt = $conn->prepare("INSERT INTO maintenance_history (asset_id, status, notes) VALUES (?, ?, 'Initial registration.')");
        $hist_stmt->bind_param("is", $asset_id, $status);
        $hist_stmt->execute();
        $hist_stmt->close();
    }
    $stmt->close();
    $conn->close();
    return $success;
}

function updateAsset($id, $name, $type, $purchase_date, $status) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE assets SET asset_name = ?, asset_type = ?, purchase_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $type, $purchase_date, $status, $id);
    $success = $stmt->execute();
    if ($success) {
        $hist_stmt = $conn->prepare("INSERT INTO maintenance_history (asset_id, status, notes) VALUES (?, ?, 'Status updated.')");
        $hist_stmt->bind_param("is", $id, $status);
        $hist_stmt->execute();
        $hist_stmt->close();
    }
    $stmt->close();
    $conn->close();
    return $success;
}

function deleteAsset($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

// --- Maintenance Schedule Functions ---
function getMaintenanceSchedules() {
    $conn = getDbConnection();
    $sql = "SELECT ms.id, a.asset_name, ms.asset_id, ms.task_description, ms.scheduled_date, ms.status, ms.notes
            FROM maintenance_schedules ms
            JOIN assets a ON ms.asset_id = a.id
            ORDER BY ms.scheduled_date ASC";
    $result = $conn->query($sql);
    $schedules = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $schedules;
}

function createMaintenanceSchedule($asset_id, $description, $scheduled_date, $notes = null) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO maintenance_schedules (asset_id, task_description, scheduled_date, notes) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $asset_id, $description, $scheduled_date, $notes);
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    return $success;
}

function updateMaintenanceStatus($schedule_id, $status) {
    $conn = getDbConnection();
    $completed_date = ($status === 'Completed') ? date('Y-m-d') : null;

    $asset_id_stmt = $conn->prepare("SELECT asset_id FROM maintenance_schedules WHERE id = ?");
    $asset_id_stmt->bind_param("i", $schedule_id);
    $asset_id_stmt->execute();
    $asset_id_result = $asset_id_stmt->get_result();
    $asset_id = $asset_id_result->fetch_assoc()['asset_id'];
    $asset_id_stmt->close();

    $stmt = $conn->prepare("UPDATE maintenance_schedules SET status = ?, completed_date = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $completed_date, $schedule_id);
    $success = $stmt->execute();

    if ($success && $status === 'Completed' && $asset_id) {
        $hist_stmt = $conn->prepare("INSERT INTO maintenance_history (asset_id, status, notes, `timestamp`) VALUES (?, 'Operational', 'Maintenance task completed.', NOW())");
        $hist_stmt->bind_param("i", $asset_id);
        $hist_stmt->execute();
        $hist_stmt->close();
        
        $cache_stmt = $conn->prepare("DELETE FROM asset_forecast_cache WHERE asset_id = ?");
        $cache_stmt->bind_param("i", $asset_id);
        $cache_stmt->execute();
        $cache_stmt->close();
    }
    
    $stmt->close();
    $conn->close();
    return $success;
}


// --- Predictive Automation Functions ---
function isMaintenanceScheduled($asset_id, $predicted_date_str) {
    if ($predicted_date_str === 'N/A' || $predicted_date_str === 'Error') return true;
    $conn = getDbConnection();
    
    $recent_past_date = date('Y-m-d', strtotime('-14 days'));
    $predicted_future_date = date('Y-m-d', strtotime('+14 days', strtotime($predicted_date_str)));

    $stmt = $conn->prepare(
        "SELECT COUNT(*) as count FROM maintenance_schedules
         WHERE asset_id = ?
         AND (
             (status = 'Scheduled' AND scheduled_date <= ?) OR
             (status = 'Completed' AND completed_date >= ?)
         )"
    );
    $stmt->bind_param("iss", $asset_id, $predicted_future_date, $recent_past_date);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result['count'] > 0;
}


function automateMaintenanceSchedules() {
    $assets = getAllAssets();
    $forecasts = getPredictiveMaintenanceForecasts($assets);
    foreach ($assets as $asset) {
        $asset_id = $asset['id'];
        if (isset($forecasts[$asset_id])) {
            $forecast = $forecasts[$asset_id];
            $risk = strip_tags($forecast['risk']);
            $predicted_date = $forecast['next_maintenance'];
            if (in_array($risk, ['High', 'Medium'])) {
                if (!isMaintenanceScheduled($asset_id, $predicted_date)) {
                    $description = "AI Recommended: Proactive check-up.";
                    $notes = "Automated based on {$risk} risk prediction.";
                    
                    // **THE FIX IS HERE**: Swapped the arguments to the correct order.
                    // createMaintenanceSchedule(asset_id, description, scheduled_date, notes)
                    createMaintenanceSchedule($asset_id, $description, date('Y-m-d', strtotime($predicted_date)), $notes);
                }
            }
        }
    }
}

// --- Predictive Maintenance Functions ---
function getAssetHistory($asset_id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT status, notes, `timestamp` FROM maintenance_history WHERE asset_id = ? ORDER BY `timestamp` ASC");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();
    $conn->close();
    return $history;
}

function getAllUsageLogsGroupedByAsset() {
    $conn = getDbConnection();
    $sql = "SELECT a.id as asset_id, a.asset_name, u.log_date, u.metric_name, u.metric_value
            FROM assets a
            JOIN asset_usage_logs u ON a.id = u.asset_id
            ORDER BY a.asset_name ASC, u.log_date DESC";
    $result = $conn->query($sql);
    $logsByAsset = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logsByAsset[$row['asset_id']]['asset_name'] = $row['asset_name'];
            $logsByAsset[$row['asset_id']]['logs'][] = $row;
        }
    }
    $conn->close();
    return $logsByAsset;
}

function getPredictiveMaintenanceForecasts(array $assets): array {
    $conn = getDbConnection();
    $finalForecasts = [];
    $assetsToFetchFromApi = [];
    $cacheExpiryHours = 24;

    foreach ($assets as $asset) {
        $stmt = $conn->prepare("SELECT risk, next_maintenance FROM asset_forecast_cache WHERE asset_id = ? AND cached_at > NOW() - INTERVAL ? HOUR");
        $stmt->bind_param("ii", $asset['id'], $cacheExpiryHours);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $finalForecasts[$asset['id']] = $result->fetch_assoc();
        } else {
            $assetsToFetchFromApi[] = $asset;
        }
        $stmt->close();
    }

    if (!empty($assetsToFetchFromApi)) {
        $apiForecasts = fetchForecastsFromGeminiForAssets($assetsToFetchFromApi);
        foreach ($apiForecasts as $assetId => $forecastData) {
            $finalForecasts[$assetId] = $forecastData;
            $risk = strip_tags($forecastData['risk']);
            $next_maintenance = strip_tags($forecastData['next_maintenance']);
            $stmt = $conn->prepare("INSERT INTO asset_forecast_cache (asset_id, risk, next_maintenance, cached_at) VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE risk = VALUES(risk), next_maintenance = VALUES(next_maintenance), cached_at = NOW()");
            $stmt->bind_param("iss", $assetId, $risk, $next_maintenance);
            $stmt->execute();
            $stmt->close();
        }
    }

    $conn->close();
    return $finalForecasts;
}

function fetchForecastsFromGeminiForAssets(array $assets): array {
    $apiKey = 'AIzaSyAmMMCjXOlS7tSXFmF9jiJOxa7OW3gsjO0';
    $geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;
    $forecasts = [];
    $assetsWithHistory = [];

    $allUsageLogs = getAllUsageLogsGroupedByAsset();

    foreach ($assets as $asset) {
        $history = getAssetHistory($asset['id']);
        $usage_logs = $allUsageLogs[$asset['id']]['logs'] ?? [];
        if (count($history) < 2 && count($usage_logs) < 2) {
            $forecasts[$asset['id']] = ['risk' => 'No Data', 'next_maintenance' => 'N/A'];
        } else {
            $assetsWithHistory[$asset['id']] = ['name' => $asset['asset_name'], 'type' => $asset['asset_type'], 'history' => $history, 'usage' => $usage_logs];
        }
    }

    if (empty($assetsWithHistory)) return $forecasts;

    $batchPrompt = "As a predictive maintenance analyst for a logistics company, analyze the following assets based on their maintenance history and usage logs. For each asset, provide your output as a JSON object with two keys: 'risk' (a one-word risk level: 'Low', 'Medium', or 'High') and 'next_maintenance' (the predicted next service date in 'M d, Y' format). Today's date is " . date('M d, Y') . ". Return a single minified JSON array containing one object for each asset.\n\n";
    foreach ($assetsWithHistory as $id => $assetData) {
        $batchPrompt .= "Asset ID: {$id}\nAsset Name: {$assetData['name']} ({$assetData['type']})\nMaintenance History:\n";
        foreach ($assetData['history'] as $record) {
            $date = date('Y-m-d', strtotime($record['timestamp']));
            $notes = $record['notes'] ? " ({$record['notes']})" : '';
            $batchPrompt .= "- Date: {$date}, Status: {$record['status']}{notes}\n";
        }
        if (!empty($assetData['usage'])) {
            $batchPrompt .= "Usage Logs:\n";
            foreach ($assetData['usage'] as $log) {
                $batchPrompt .= "- Date: {$log['log_date']}, {$log['metric_name']}: {$log['metric_value']}\n";
            }
        }
        $batchPrompt .= "\n";
    }

    $data = ["contents" => [["parts" => [["text" => $batchPrompt]]]], "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.3]];
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
        if (is_array($batch_analysis)) {
            foreach ($batch_analysis as $index => $analysis_data) {
                if (isset(array_keys($assetsWithHistory)[$index])) {
                    $assetId = array_keys($assetsWithHistory)[$index];
                    $forecasts[$assetId] = ['risk' => htmlspecialchars($analysis_data['risk'] ?? 'Error'), 'next_maintenance' => htmlspecialchars($analysis_data['next_maintenance'] ?? 'N/A')];
                }
            }
        }
    } else {
        foreach ($assetsWithHistory as $id => $asset) {
            $error_detail = !empty($curl_error) ? $curl_error : "HTTP Code: {$http_code}";
            $forecasts[$id] = ['risk' => "<span class='text-red-500' title='{$error_detail}'>API Error</span>", 'next_maintenance' => 'Error'];
        }
    }
    return $forecasts;
}
?>