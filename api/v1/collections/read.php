<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once __DIR__ . '/../../../config/Database.php';

$database = new Database();
$db = $database->connect();

if(!$db) {
    echo json_encode(['message' => 'Database connection error']);
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$query = 'SELECT * FROM new_transaction';

$where_clauses = [];
$params = [];
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

$query .= ' ORDER BY t_date DESC';

$stmt = $db->prepare($query);
$stmt->execute($params);

$num = $stmt->rowCount();

if($num > 0) {
    $transactions_arr = array();
    $transactions_arr['data'] = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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

        array_push($transactions_arr['data'], $transaction_item);
    }
    
    $transactions_arr['message'] = 'Collections retrieved successfully';
    $transactions_arr['response'] = 'success';

    echo json_encode($transactions_arr);
} else {
    echo json_encode(
        array('message' => 'No Collections Found', 'response' => 'success', 'data' => [])
    );
} 