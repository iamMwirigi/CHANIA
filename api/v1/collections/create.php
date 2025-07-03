<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method Not Allowed. Use POST.', 'response' => 'error']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$required_fields = [
    'number_plate', 'sacco_fee', 'investment', 'savings', 'tyres', 'insurance', 'welfare',
    't_time', 't_date', 'collected_by', 'stage_name', 'amount'
];

foreach ($required_fields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['message' => "Missing required field: $field", 'response' => 'error']);
        exit();
    }
}

$query = 'INSERT INTO new_transaction (
    number_plate, sacco_fee, investment, savings, tyres, insurance, welfare,
    t_time, t_date, collected_by, stage_name, amount,
    s_time, s_date, client_side_id, receipt_no, delete_status, for_date
) VALUES (
    :number_plate, :sacco_fee, :investment, :savings, :tyres, :insurance, :welfare,
    :t_time, :t_date, :collected_by, :stage_name, :amount,
    :s_time, :s_date, :client_side_id, :receipt_no, :delete_status, :for_date
)';

$stmt = $db->prepare($query);

// Prepare variables for bindParam (must be variables, not expressions)
$t_date = $data['t_date'] ?? date('Y-m-d');

// Required fields
$stmt->bindParam(':number_plate', $data['number_plate']);
$stmt->bindParam(':sacco_fee', $data['sacco_fee']);
$stmt->bindParam(':investment', $data['investment']);
$stmt->bindParam(':savings', $data['savings']);
$stmt->bindParam(':tyres', $data['tyres']);
$stmt->bindParam(':insurance', $data['insurance']);
$stmt->bindParam(':welfare', $data['welfare']);
$stmt->bindParam(':t_time', $data['t_time']);
$stmt->bindParam(':t_date', $t_date);
$stmt->bindParam(':collected_by', $data['collected_by']);
$stmt->bindParam(':stage_name', $data['stage_name']);
$stmt->bindParam(':amount', $data['amount']);
// Optional fields
$s_date = $data['s_date'] ?? date('Y-m-d');
$stmt->bindValue(':s_time', $data['s_time'] ?? date('H:i:s'));
$stmt->bindValue(':s_date', $s_date);
$stmt->bindValue(':client_side_id', $data['client_side_id'] ?? null);
$stmt->bindValue(':receipt_no', $data['receipt_no'] ?? null);
$stmt->bindValue(':delete_status', $data['delete_status'] ?? 0);
$stmt->bindValue(':for_date', $data['for_date'] ?? null);

if($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        'message' => 'Collection created successfully',
        'response' => 'success',
        'id' => $db->lastInsertId()
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'message' => 'Failed to create collection',
        'response' => 'error'
    ]);
} 