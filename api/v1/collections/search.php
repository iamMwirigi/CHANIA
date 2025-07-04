<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';

$database = new Database();
$db = $database->connect();

if ($db === null) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed.", "response" => "error"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$query = isset($data->query) ? trim($data->query) : '';
$start_date = isset($data->start_date) ? $data->start_date : null;
$end_date = isset($data->end_date) ? $data->end_date : null;

$where_clauses = [];
$params = [];

if ($query !== '') {
    $where_clauses[] = "(m.name LIKE ? OR m.phone_number LIKE ? OR m.number LIKE ?)";
    $like_query = "%$query%";
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
}
if ($start_date) {
    $where_clauses[] = "c.t_date >= ?";
    $params[] = $start_date;
}
if ($end_date) {
    $where_clauses[] = "c.t_date <= ?";
    $params[] = $end_date;
}
$where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : '';

$sql = "SELECT c.*, m.id as member_id, m.name, m.phone_number, m.number
        FROM new_transaction c
        JOIN vehicle v ON c.number_plate = v.number_plate
        JOIN member m ON v.owner = m.id
        $where_sql
        ORDER BY c.t_date DESC, c.t_time DESC
        LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = array_map(function($row) {
    return [
        "collection_id" => $row['id'],
        "number_plate" => $row['number_plate'],
        "amount" => $row['amount'] ?? null,
        "t_date" => $row['t_date'],
        "t_time" => $row['t_time'],
        "member" => [
            "id" => $row['member_id'],
            "name" => $row['name'],
            "phone_number" => $row['phone_number'],
            "number" => $row['number']
        ]
    ];
}, $results);

http_response_code(200);
echo json_encode([
    "message" => "Collections search successful",
    "response" => "success",
    "data" => $data
]); 