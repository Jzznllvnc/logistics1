<?php
// Logistic1/includes/ajax/get_purchase_orders_chart.php
require_once '../config/db.php';

header('Content-Type: application/json');

// Get filter parameter (default to 'This Month')
$filter = $_GET['filter'] ?? 'This Month';

$conn = getDbConnection();

// Determine date range based on filter
$whereClause = '';
$currentDate = date('Y-m-d');

switch ($filter) {
    case 'This Week':
        $startDate = date('Y-m-d', strtotime('monday this week'));
        $endDate = date('Y-m-d', strtotime('sunday this week'));
        break;
    
    case 'This Month':
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
    
    case 'This Year':
        $startDate = date('Y-01-01');
        $endDate = date('Y-12-31');
        break;
    
    default:
        // Default to this month
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        break;
}

try {
    if ($filter === 'This Year') {
        // For "This Year", group by months and show Jan-Dec
        $poSQL = "SELECT 
                    MONTH(order_date) as month_num,
                    MONTHNAME(order_date) as month_name,
                    COUNT(*) as order_count
                  FROM purchase_orders 
                  WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
                  GROUP BY MONTH(order_date), MONTHNAME(order_date)
                  ORDER BY MONTH(order_date) ASC";
        
        $poResult = $conn->query($poSQL);
        $purchaseOrdersData = [];
        
        if ($poResult && $poResult->num_rows > 0) {
            while ($row = $poResult->fetch_assoc()) {
                $purchaseOrdersData[(int)$row['month_num']] = (int)$row['order_count'];
            }
        }
        
        // Get SWS inventory movements data grouped by months
        $inventorySQL = "SELECT 
                            MONTH(timestamp) as month_num,
                            MONTHNAME(timestamp) as month_name,
                            COUNT(*) as movement_count
                         FROM inventory_history 
                         WHERE DATE(timestamp) BETWEEN '$startDate' AND '$endDate'
                         GROUP BY MONTH(timestamp), MONTHNAME(timestamp)
                         ORDER BY MONTH(timestamp) ASC";
        
        $inventoryResult = $conn->query($inventorySQL);
        $inventoryData = [];
        
        if ($inventoryResult && $inventoryResult->num_rows > 0) {
            while ($row = $inventoryResult->fetch_assoc()) {
                $inventoryData[(int)$row['month_num']] = (int)$row['movement_count'];
            }
        }
        
        // Create chart data for all 12 months
        $chartData = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        for ($i = 1; $i <= 12; $i++) {
            $chartData[] = [
                'date' => $months[$i - 1], // Use month abbreviation
                'purchase_orders' => $purchaseOrdersData[$i] ?? 0,
                'inventory_movements' => $inventoryData[$i] ?? 0
            ];
        }
        
    } else {
        // For Week/Month, group by date as before
        $poSQL = "SELECT 
                    DATE(order_date) as order_date,
                    COUNT(*) as order_count
                  FROM purchase_orders 
                  WHERE DATE(order_date) BETWEEN '$startDate' AND '$endDate'
                  GROUP BY DATE(order_date)
                  ORDER BY order_date ASC";
        
        $poResult = $conn->query($poSQL);
        $purchaseOrdersData = [];
        
        if ($poResult && $poResult->num_rows > 0) {
            while ($row = $poResult->fetch_assoc()) {
                $purchaseOrdersData[$row['order_date']] = (int)$row['order_count'];
            }
        }
        
        // Get SWS inventory movements data grouped by date
        $inventorySQL = "SELECT 
                            DATE(timestamp) as movement_date,
                            COUNT(*) as movement_count
                         FROM inventory_history 
                         WHERE DATE(timestamp) BETWEEN '$startDate' AND '$endDate'
                         GROUP BY DATE(timestamp)
                         ORDER BY movement_date ASC";
        
        $inventoryResult = $conn->query($inventorySQL);
        $inventoryData = [];
        
        if ($inventoryResult && $inventoryResult->num_rows > 0) {
            while ($row = $inventoryResult->fetch_assoc()) {
                $inventoryData[$row['movement_date']] = (int)$row['movement_count'];
            }
        }
        
        // Create a unified date range and combine both datasets
        $chartData = [];
        $allDates = array_unique(array_merge(array_keys($purchaseOrdersData), array_keys($inventoryData)));
        sort($allDates);
        
        foreach ($allDates as $date) {
            $chartData[] = [
                'date' => $date,
                'purchase_orders' => $purchaseOrdersData[$date] ?? 0,
                'inventory_movements' => $inventoryData[$date] ?? 0
            ];
        }
    }
    
    // Get summary statistics
    if ($filter === 'This Year') {
        $totalPO = array_sum($purchaseOrdersData);
        $totalInventoryMoves = array_sum($inventoryData);
    } else {
        $totalPO = array_sum($purchaseOrdersData);
        $totalInventoryMoves = array_sum($inventoryData);
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => $chartData,
        'summary' => [
            'total_purchase_orders' => $totalPO,
            'total_inventory_movements' => $totalInventoryMoves,
            'period' => $filter,
            'date_range' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
} finally {
    $conn->close();
}
?>
