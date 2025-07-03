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
        $sanitized_number = Sms::sanitizeNumber($member['phone_number']);
        if ($sanitized_number) {
            $api_result = Sms::sendTextChania($sanitized_number, $data->message, 'CHANIA');
            $api_result_decoded = json_decode($api_result, true);
            // Improved success detection
            $success = false;
            if (
                isset($api_result_decoded['ErrorCode']) && $api_result_decoded['ErrorCode'] === 0 &&
                isset($api_result_decoded['Data']) && is_array($api_result_decoded['Data'])
            ) {
                $success = true;
                foreach ($api_result_decoded['Data'] as $msg) {
                    if (!isset($msg['MessageErrorCode']) || $msg['MessageErrorCode'] !== 0) {
                        $success = false;
                        break;
                    }
                }
            }
            $sms->sent_to = $sanitized_number;
            $sms->text_message = $data->message;
            $sms->sent_date = date('Y-m-d');
            $sms->sent_time = date('H:i:s');
            $sms->sent_status = $success ? 1 : 0;
            $sms->cost = 0.80; // Example cost, adjust if needed
            $sms->sent_from = 'CHANIA'; // Set sender for DB
            $sms->package_id = '';
            $sms->af_cost = 0;
            $sms->sms_characters = strlen($data->message);
            $sms->pages = 1;
            $sms->page_cost = 0.80;

            if ($sms->create()) {
                $results[] = [
                    'member_id' => $member_id,
                    'status' => $success ? 'success' : 'api_failed',
                    'api_response' => $api_result_decoded
                ];
            } else {
                $results[] = [
                    'member_id' => $member_id,
                    'status' => 'failed_to_log',
                    'api_response' => $api_result_decoded
                ];
            }
        } else {
            $results[] = ['member_id' => $member_id, 'status' => 'invalid_phone_format'];
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