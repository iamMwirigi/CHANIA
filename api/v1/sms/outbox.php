<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Sms.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

$sms = new Sms($db);

$result = $sms->read_outbox();
$num = $result->rowCount();

if($num > 0) {
    $outbox_arr = array();
    $outbox_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $outbox_item = array(
            'id' => $id,
            'phone_number' => $phone_number,
            'message' => $message,
            'cost' => $cost,
            'status' => $status,
            'date_sent' => $date_sent
        );
        array_push($outbox_arr['data'], $outbox_item);
    }

    echo json_encode($outbox_arr);
} else {
    echo json_encode(
        array('message' => 'No Messages Found in Outbox')
    );
} 