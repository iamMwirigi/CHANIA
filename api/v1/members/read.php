<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../models/Member.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin', 'user']);

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$query = 'SELECT id, name, phone_number, number FROM member ORDER BY name ASC';

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $members_arr = array();
    $members_arr['data'] = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $member_item = array(
            'id' => $id,
            'name' => $name,
            'phone_number' => $phone_number,
            'number' => $number
        );

        array_push($members_arr['data'], $member_item);
    }
    
    $members_arr['message'] = 'Members retrieved successfully';
    $members_arr['response'] = 'success';

    echo json_encode($members_arr);
} else {
    echo json_encode(
        array('message' => 'No Members Found', 'response' => 'success', 'data' => [])
    );
} 