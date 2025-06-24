<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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

// Set ID to delete
$stage->id = $data->id;

// Delete stage
if($stage->delete()) {
    echo json_encode(
        array('message' => 'Stage Deleted', 'response' => 'success')
    );
} else {
    echo json_encode(
        array('message' => 'Stage Not Deleted', 'response' => 'error')
    );
} 