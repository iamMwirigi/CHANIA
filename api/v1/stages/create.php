<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Stage.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate stage object
$stage = new Stage($db);

// Get raw posted data
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(
        array('message' => 'No data provided.', 'response' => 'error')
    );
    exit();
}

$stage->name = $data->name;
$stage->prefix = $data->prefix;
$stage->quota_start = $data->quota_start;
$stage->quota_end = $data->quota_end;
$stage->current_quota = $data->current_quota;

// Create stage
if($stage->create()) {
    echo json_encode(
        array('message' => 'Stage Created', 'response' => 'success')
    );
} else {
    echo json_encode(
        array('message' => 'Stage Not Created', 'response' => 'error')
    );
} 