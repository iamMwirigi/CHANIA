<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin', 'user', 'member']);

$database = new Database();
$db = $database->connect();

if($db === null) {
    http_response_code(503);
    echo json_encode(['message' => 'Failed to connect to the database.', 'response' => 'error']);
    exit();
}

$query = 'SELECT * FROM sms';
$params = array();

if ($userData->role === 'member') {
    // Find the member's phone number
    $phone_number = $userData->phone_number ?? null;
    if (!$phone_number) {
        // Fallback: try to fetch from DB
        $stmt = $db->prepare('SELECT phone_number FROM member WHERE id = :id');
        $stmt->bindParam(':id', $userData->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $phone_number = $row ? $row['phone_number'] : null;
    }
    if ($phone_number) {
        $query .= ' WHERE sent_to = :sent_to';
        $params[':sent_to'] = $phone_number;
    } else {
        echo json_encode(['message' => 'Unable to determine member phone number for messages.', 'response' => 'error', 'data' => []]);
        exit();
    }
}

$query .= ' ORDER BY sent_date DESC, id DESC';

$stmt = $db->prepare($query);
$stmt->execute($params);

$num = $stmt->rowCount();

if ($num > 0) {
    $sms_arr = array();
    $sms_arr['data'] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sms_arr['data'][] = $row;
    }
    $sms_arr['message'] = 'Messages retrieved successfully';
    $sms_arr['response'] = 'success';

    echo json_encode($sms_arr);
} else {
    echo json_encode(
        array('message' => 'No Messages Found', 'response' => 'success', 'data' => [])
    );
} 