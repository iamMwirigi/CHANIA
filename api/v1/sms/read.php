<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Sms.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin']);

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate sms object
$sms = new Sms($db);

// SMS query
$result = $sms->read();
// Get row count
$num = $result->rowCount();

// Check if any messages
if ($num > 0) {
    // SMS array
    $sms_arr = array();
    $sms_arr['data'] = array();

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $sms_item = array(
            'id' => $id,
            'recipient' => $sent_to,
            'message' => $text_message,
            'cost' => 'KES ' . number_format($cost, 2),
            'status' => $sent_status == 1 ? 'Delivered' : 'Failed',
            'date' => date('M d, Y', strtotime($sent_date)),
            'time' => $sent_time,
        );
        // Push to "data"
        array_push($sms_arr['data'], $sms_item);
    }
    // Turn to JSON & output
    echo json_encode($sms_arr);
} else {
    // No Messages
    echo json_encode(
        array('message' => 'No Messages Found', 'data' => [])
    );
} 