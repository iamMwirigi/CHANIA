<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['admin', 'user']);

$database = new Database();
$db = $database->connect();

if($db === null) {
    http_response_code(503);
    echo json_encode(['message' => 'Failed to connect to the database.', 'response' => 'error']);
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : die(json_encode(['message' => 'Transaction ID not provided.']));

$query = 'SELECT * FROM new_transaction WHERE id = ?';

$stmt = $db->prepare($query);
$stmt->bindParam(1, $id);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userData->role === 'user' && $row['collected_by'] !== $userData->username) {
        http_response_code(403);
        echo json_encode(array("message" => "Forbidden. You don't have permission to access this resource.", "response" => "error"));
        exit();
    }

    extract($row);
    $transaction_item = array(
        'id' => $id,
        'number_plate' => $number_plate,
        'sacco_fee' => $sacco_fee,
        'investment' => $investment,
        'savings' => $savings,
        'tyres' => $tyres,
        'insurance' => $insurance,
        'welfare' => $welfare,
        't_time' => $t_time,
        't_date' => $t_date,
        'collected_by' => $collected_by,
        'stage_name' => $stage_name,
        'amount' => $amount
    );
    
    echo json_encode([
        'message' => 'Collection found',
        'response' => 'success',
        'data' => $transaction_item
    ]);

} else {
    http_response_code(404);
    echo json_encode(
        array('message' => 'Collection not found.', 'response' => 'error')
    );
} 