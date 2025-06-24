<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Member.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate member object
$member = new Member($db);

// Member query
$result = $member->read();
$num = $result->rowCount();

// Check if any members
if($num > 0) {
    $members_arr = array();
    $members_arr['data'] = array();

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $member_item = array(
            'id' => $id,
            'name' => $name,
            'phone_number' => $phone_number
        );
        array_push($members_arr['data'], $member_item);
    }

    echo json_encode($members_arr);
} else {
    echo json_encode(
        array('message' => 'No Members Found')
    );
} 