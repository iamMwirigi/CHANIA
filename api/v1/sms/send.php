<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Sms.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin']);

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

// Instantiate sms object
$sms = new Sms($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->member_ids) || empty($data->message)) {
    http_response_code(400);
    echo json_encode(array('message' => 'Missing member_ids or message field.', 'response' => 'error'));
    return;
}

$results = [];

foreach ($data->member_ids as $member_id) {
    $query = 'SELECT phone_number FROM member WHERE id = :id';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $member_id);
    $stmt->execute();
    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($member && !empty($member['phone_number'])) {
        // Here you would integrate with your SMS gateway API
        // For now, we'll simulate a successful response
        $sms->sent_to = $member['phone_number'];
        $sms->text_message = $data->message;
        $sms->sent_date = date('Y-m-d');
        $sms->sent_time = date('H:i:s');
        $sms->sent_status = 1; // 1 for success, 0 for failure
        $sms->cost = 0.80; // Example cost

        if ($sms->create()) {
            $results[] = ['member_id' => $member_id, 'status' => 'success'];
        } else {
            $results[] = ['member_id' => $member_id, 'status' => 'failed_to_log'];
        }
    } else {
        $results[] = ['member_id' => $member_id, 'status' => 'member_not_found_or_no_phone'];
    }
}

echo json_encode([
    'message' => 'Bulk SMS process completed.',
    'response' => 'success',
    'data' => $results
]); 