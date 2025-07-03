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

$data = json_decode(file_get_contents("php://input"));

$start_date = isset($data->start_date) ? $data->start_date : (isset($_GET['start_date']) ? $_GET['start_date'] : null);
$end_date = isset($data->end_date) ? $data->end_date : (isset($_GET['end_date']) ? $_GET['end_date'] : null);
$stage_name = isset($data->stage_name) ? $data->stage_name : (isset($_GET['stage_name']) ? $_GET['stage_name'] : null);

$where_clauses = [];
$params = [];

if ($userData->role === 'member') {
    // For members, get collections for vehicles they own
    $query = 'SELECT t.* FROM new_transaction t JOIN vehicle v ON t.number_plate = v.number_plate WHERE v.owner = :owner';
    $params[':owner'] = $userData->id;
    if ($start_date) {
        $where_clauses[] = "t.t_date >= :start_date";
        $params[':start_date'] = $start_date;
    }
    if ($end_date) {
        $where_clauses[] = "t.t_date <= :end_date";
        $params[':end_date'] = $end_date;
    }
    if ($stage_name) {
        $where_clauses[] = "t.stage_name = :stage_name";
        $params[':stage_name'] = $stage_name;
    }
    if (count($where_clauses) > 0) {
        $query .= ' AND ' . implode(' AND ', $where_clauses);
    }
    $query .= ' ORDER BY t.t_date DESC, t.id DESC';
} else {
    $query = 'SELECT * FROM new_transaction';
    if ($userData->role === 'user') {
        $where_clauses[] = "collected_by = :username";
        $params[':username'] = $userData->username;
    } else if ($stage_name) {
        $where_clauses[] = "stage_name = :stage_name";
        $params[':stage_name'] = $stage_name;
    }
    if ($start_date) {
        $where_clauses[] = "t_date >= :start_date";
        $params[':start_date'] = $start_date;
    }
    if ($end_date) {
        $where_clauses[] = "t_date <= :end_date";
        $params[':end_date'] = $end_date;
    }
    if (count($where_clauses) > 0) {
        $query .= " WHERE " . implode(" AND ", $where_clauses);
    }
    $query .= ' ORDER BY t_date DESC, id DESC';
}

$stmt = $db->prepare($query);
$stmt->execute($params);

$num = $stmt->rowCount();

if($num > 0) {
    $transactions_arr = array();
    $transactions_arr['data'] = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $transactions_arr['data'][] = $row;
    }
    $transactions_arr['message'] = 'Collections retrieved successfully';
    $transactions_arr['response'] = 'success';

    echo json_encode($transactions_arr);
} else {
    echo json_encode(
        array('message' => 'No Collections Found', 'response' => 'success', 'data' => [])
    );
} 