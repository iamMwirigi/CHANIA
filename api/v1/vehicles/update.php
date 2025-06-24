<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id) || empty($data->number_plate) || empty($data->member_id)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data for vehicle update.", "response" => "error"));
    return;
}

$query = 'UPDATE vehicles SET number_plate = :number_plate, member_id = :member_id WHERE id = :id';

$stmt = $db->prepare($query);

// Clean data
$id = htmlspecialchars(strip_tags($data->id));
$number_plate = htmlspecialchars(strip_tags($data->number_plate));
$member_id = htmlspecialchars(strip_tags($data->member_id));

// Bind data
$stmt->bindParam(':id', $id);
$stmt->bindParam(':number_plate', $number_plate);
$stmt->bindParam(':member_id', $member_id);

if($stmt->execute()) {
    if($stmt->rowCount()) {
        http_response_code(200);
        echo json_encode(
            array('message' => 'Vehicle Updated', 'response' => 'success')
        );
    } else {
        http_response_code(404);
        echo json_encode(
            array('message' => 'Vehicle Not Found', 'response' => 'error')
        );
    }
} else {
    http_response_code(500);
    echo json_encode(
        array('message' => 'Vehicle Not Updated', 'response' => 'error')
    );
} 