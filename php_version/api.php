<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$pdo = get_db_connection();
init_db($pdo);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'bookings') {
        $stmt = $pdo->query("SELECT * FROM bookings ORDER BY date ASC, startTime ASC");
        echo json_encode($stmt->fetchAll());
    } 
    
    elseif ($method === 'POST' && $action === 'bookings') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['name']) || empty($data['date']) || empty($data['startTime']) || empty($data['endTime'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        // Overlap Check
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE date = ? 
            AND (
                (startTime <= ? AND endTime > ?) OR
                (startTime < ? AND endTime >= ?) OR
                (? <= startTime AND ? > startTime)
            )
        ");
        $stmt->execute([
            $data['date'], 
            $data['startTime'], $data['startTime'], 
            $data['endTime'], $data['endTime'], 
            $data['startTime'], $data['endTime']
        ]);
        
        if ($stmt->fetchColumn() > 0) {
            http_response_code(400);
            echo json_encode(['error' => 'This time slot is already booked']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO bookings (name, date, startTime, endTime, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['date'],
            $data['startTime'],
            $data['endTime'],
            $data['notes'] ?? ''
        ]);
        
        echo json_encode(['id' => $pdo->lastInsertId(), 'success' => true]);
    } 
    
    elseif ($method === 'DELETE' && $action === 'delete') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    }
    
    elseif ($action === 'test') {
        echo json_encode([
            'status' => 'ok', 
            'message' => 'Database connection is working!',
            'db_type' => DB_TYPE,
            'db_name' => DB_NAME
        ]);
    }
    
    else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found. Use ?action=bookings']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database Error: ' . $e->getMessage(),
        'hint' => 'Check your DB_HOST, DB_USER, and DB_PASS in config.php'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'General Error: ' . $e->getMessage()]);
}
?>
