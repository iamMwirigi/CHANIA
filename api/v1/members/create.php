<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../models/Member.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin']);

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->name) || empty($data->phone_number) || empty($data->number)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data for member.", "response" => "error"));
    return;
}

$query = 'INSERT INTO member SET name = :name, phone_number = :phone_number, number = :number';

$stmt = $db->prepare($query);

// Clean data
$name = htmlspecialchars(strip_tags($data->name));
$phone_number = htmlspecialchars(strip_tags($data->phone_number));
$number = htmlspecialchars(strip_tags($data->number));

// Bind data
$stmt->bindParam(':name', $name);
$stmt->bindParam(':phone_number', $phone_number);
$stmt->bindParam(':number', $number);

if($stmt->execute()) {
    http_response_code(201);
    echo json_encode(
        array('message' => 'Member Created', 'response' => 'success')
    );
} else {
    http_response_code(500);
    echo json_encode(
        array('message' => 'Member Not Created', 'response' => 'error')
    );
} 