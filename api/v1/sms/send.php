<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods,Authorization,X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/Sms.php';
include_once __DIR__ . '/../../../models/Member.php';

// Instantiate DB & connect
$database = new Database();
$db = $database->connect();

$sms = new Sms($db);
$member = new Member($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->member_ids) || !is_array($data->member_ids) || empty($data->message)) {
    http_response_code(400);
    echo json_encode(['message' => 'Missing member_ids array or message.', 'response' => 'error']);
    return;
}

$member_ids = $data->member_ids;
$message = $data->message;
$results = [];

foreach ($member_ids as $member_id) {
    $member->id = $member_id;
    $member->read_one(); // This method needs to be created in Member model

    if ($member->phone_number) {
        $sms->member_id = $member_id;
        $sms->phone_number = $member->phone_number;
        $sms->message = $message;

        // In a real app, you would integrate with an SMS gateway here.
        // For now, we'll simulate it.
        $sms->status = 'Sent';
        $sms->cost = 'KES 0.80'; // Simulated cost

        if ($sms->log_sms()) {
            $results[] = ['member_id' => $member_id, 'status' => 'success'];
        } else {
            $results[] = ['member_id' => $member_id, 'status' => 'failed_to_log'];
        }
    } else {
        $results[] = ['member_id' => $member_id, 'status' => 'member_not_found'];
    }
}

echo json_encode(['message' => 'Bulk SMS process completed.', 'response' => 'success', 'data' => $results]); 