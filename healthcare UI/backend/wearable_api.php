<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'connection.php';

function store_health_data($user_id, $data) {
    global $conn;
    $sql = "INSERT INTO health_metrics (user_id, heart_rate, blood_pressure, temperature, blood_oxygen, timestamp) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisss", 
        $user_id,
        $data['heart_rate'],
        $data['blood_pressure'],
        $data['temperature'],
        $data['blood_oxygen']
    );
    return $stmt->execute();
}

function get_health_data($user_id, $metric, $duration = '24h') {
    global $conn;
    $sql = "SELECT $metric, timestamp FROM health_metrics 
            WHERE user_id = ? AND timestamp >= NOW() - INTERVAL ? 
            ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $duration);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json_data = json_decode(file_get_contents('php://input'), true);
    $user_id = $json_data['user_id'] ?? null;
    $health_data = $json_data['health_data'] ?? null;

    if (!$user_id || !$health_data) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required data']);
        exit;
    }

    if (store_health_data($user_id, $health_data)) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Health data stored successfully'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to store health data'
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_GET['user_id'] ?? null;
    $metric = $_GET['metric'] ?? null;
    $duration = $_GET['duration'] ?? '24h';

    if (!$user_id || !$metric) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
        exit;
    }

    $health_data = get_health_data($user_id, $metric, $duration);
    echo json_encode([
        'status' => 'success',
        'data' => $health_data
    ]);
} else {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>