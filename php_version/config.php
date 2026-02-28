<?php
// Database Configuration
define('DB_TYPE', 'mysql'); // Change to 'sqlite' if you want to use a local file
define('DB_HOST', 'localhost');
define('DB_NAME', 'pizza_oven_db');
define('DB_USER', 'Webapp');
define('DB_PASS', 'Sommer_6677');
define('SQLITE_FILE', __DIR__ . '/pizza_oven.db');

function get_db_connection() {
    try {
        if (DB_TYPE === 'mysql') {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
        } else {
            $pdo = new PDO("sqlite:" . SQLITE_FILE);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Connection Failed: ' . $e->getMessage(),
            'details' => [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'user' => DB_USER
            ]
        ]);
        exit;
    }
}

// Initialize Table
function init_db($pdo) {
    $query = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        startTime TIME NOT NULL,
        endTime TIME NOT NULL,
        notes TEXT,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    // SQLite syntax adjustment
    if (DB_TYPE === 'sqlite') {
        $query = "CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            date TEXT NOT NULL,
            startTime TEXT NOT NULL,
            endTime TEXT NOT NULL,
            notes TEXT,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
    }
    
    $pdo->exec($query);
}
?>
