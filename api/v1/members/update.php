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

if (empty($data->id) || empty($data->name) || empty($data->phone_number) || empty($data->number)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data for member update.", "response" => "error"));
    return;
}

$query = 'UPDATE member SET name = :name, phone_number = :phone_number, number = :number WHERE id = :id';

$stmt = $db->prepare($query);

// Clean data
$id = htmlspecialchars(strip_tags($data->id));
$name = htmlspecialchars(strip_tags($data->name));
$phone_number = htmlspecialchars(strip_tags($data->phone_number));
$number = htmlspecialchars(strip_tags($data->number));

// Bind data
$stmt->bindParam(':id', $id);
$stmt->bindParam(':name', $name);
$stmt->bindParam(':phone_number', $phone_number);
$stmt->bindParam(':number', $number);

if($stmt->execute()) {
    if($stmt->rowCount()) {
        http_response_code(200);
        echo json_encode(
            array('message' => 'Member Updated', 'response' => 'success')
        );
    } else {
        http_response_code(404);
        echo json_encode(
            array('message' => 'Member Not Found', 'response' => 'error')
        );
    }
} else {
    http_response_code(500);
    echo json_encode(
        array('message' => 'Member Not Updated', 'response' => 'error')
    );
} 