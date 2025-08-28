<?php
// Start output buffering to catch any errors
ob_start();

try {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once '../functions/auth.php';
    require_once '../functions/bids.php';
    require_once '../functions/purchase_order.php';
    require_once '../functions/notifications.php';

    // Check if user is logged in and has proper role
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        throw new Exception('Not authenticated');
    }
    
    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'procurement') {
        throw new Exception('Unauthorized access');
    }
} catch (Exception $e) {
    // Clear any output buffer
    ob_clean();
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}

// Clear output buffer - we want clean JSON output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'award_bid':
            $po_id = $input['po_id'] ?? 0;
            $supplier_id = $input['supplier_id'] ?? 0;
            $bid_id = $input['bid_id'] ?? 0;
            
            if (!$po_id || !$supplier_id || !$bid_id) {
                throw new Exception('Missing required parameters');
            }
            
            if (awardPOToSupplier($po_id, $supplier_id, $bid_id)) {
                echo json_encode([
                    'success' => true,
                    'message' => "Bid #$bid_id has been awarded successfully!",
                    'bid_id' => $bid_id,
                    'status' => 'Awarded'
                ]);
            } else {
                throw new Exception('Failed to award the bid');
            }
            break;
            
        case 'reject_bid':
            $bid_id = $input['bid_id'] ?? 0;
            
            if (!$bid_id) {
                throw new Exception('Missing bid ID');
            }
            
            if (rejectBid($bid_id)) {
                echo json_encode([
                    'success' => true,
                    'message' => "Bid #$bid_id has been rejected.",
                    'bid_id' => $bid_id,
                    'status' => 'Rejected'
                ]);
            } else {
                throw new Exception('Failed to reject the bid');
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    // Clear any output that might have been generated
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Catch PHP fatal errors too
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?> 