<?php 
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Stage.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate stage object
$stage = new Stage($db);

// Stage query
$result = $stage->read();
// Get row count
$num = $result->rowCount();

// Check if any stages
if($num > 0) {
    // Stage array
    $stages_arr = array();
    $stages_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);

        $stage_item = array(
            'id' => $id,
            'name' => $name,
            'prefix' => $prefix,
            'quota_start' => $quota_start,
            'quota_end' => $quota_end,
            'current_quota' => $current_quota
        );

        // Push to "data"
        array_push($stages_arr['data'], $stage_item);
    }

    // Turn to JSON & output
    echo json_encode($stages_arr);

} else {
    // No Stages
    echo json_encode(
        array('message' => 'No Stages Found')
    );
} 