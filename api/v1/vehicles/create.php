<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->number_plate) || empty($data->member_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data for vehicle.", "response" => "error"));
    return;
}

$query = 'INSERT INTO vehicles SET number_plate = :number_plate, member_id = :member_id';

$stmt = $db->prepare($query);

// Clean data
$number_plate = htmlspecialchars(strip_tags($data->number_plate));
$member_id = htmlspecialchars(strip_tags($data->member_id));

// Bind data
$stmt->bindParam(':number_plate', $number_plate);
$stmt->bindParam(':member_id', $member_id);

if($stmt->execute()) {
    http_response_code(201);
    echo json_encode(
        array('message' => 'Vehicle Created', 'response' => 'success')
    );
} else {
    http_response_code(500);
    echo json_encode(
        array('message' => 'Vehicle Not Created. It might already exist.', 'response' => 'error')
    );
} 