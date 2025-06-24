<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(['message' => 'Vehicle ID not provided.']));

$query = 'SELECT v.id, v.number_plate, v.member_id, m.name as member_name
          FROM vehicles v
          LEFT JOIN member m ON v.member_id = m.id
          WHERE v.id = ?';

$stmt = $db->prepare($query);
$stmt->bindParam(1, $id);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    extract($row);
    $vehicle_item = array(
        'id' => $id,
        'number_plate' => $number_plate,
        'member_id' => $member_id,
        'member_name' => $member_name
    );
    
    echo json_encode([
        'message' => 'Vehicle found',
        'response' => 'success',
        'data' => $vehicle_item
    ]);

} else {
    http_response_code(404);
    echo json_encode(
        array('message' => 'Vehicle not found.', 'response' => 'error')
    );
} 