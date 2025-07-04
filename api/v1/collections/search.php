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
    $where_clauses[] = "(m.name LIKE ? OR m.phone_number LIKE ? OR m.number LIKE ? OR m.id = ?)";
    $like_query = "%$query%";
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $like_query;
    $params[] = $query; // exact match for ID
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

// Get all matching members (even those without collections)
$member_where = [];
$member_params = [];
if ($query !== '') {
    $member_where[] = "(name LIKE ? OR phone_number LIKE ? OR number LIKE ? OR id = ?)";
    $like_query = "%$query%";
    $member_params[] = $like_query;
    $member_params[] = $like_query;
    $member_params[] = $like_query;
    $member_params[] = $query;
}
$member_where_sql = count($member_where) > 0 ? " WHERE " . implode(" AND ", $member_where) : '';
$members_sql = "SELECT id, name FROM member $member_where_sql";
$member_stmt = $db->prepare($members_sql);
$member_stmt->execute($member_params);
$all_members = $member_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build a map for quick lookup
$members = [];
foreach ($all_members as $m) {
    $members[$m['id']] = [
        'member_id' => (string)$m['id'],
        'member_name' => $m['name'],
        'collection' => [],
        'totals' => [
            'welfare' => 0,
            'investment' => 0,
            'sacco_fee' => 0,
            'savings' => 0,
            'tyres' => 0,
            'insurance' => 0,
            'grand_total_deductions' => 0
        ]
    ];
}

$sql = "SELECT c.*, m.id as member_id, m.name, m.phone_number, m.number
        FROM new_transaction c
        JOIN vehicle v ON c.number_plate = v.number_plate
        JOIN member m ON v.owner = m.id
        $where_sql
        ORDER BY m.id, c.number_plate, c.t_date DESC, c.t_time DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by member, then by vehicle
foreach ($results as $row) {
    $member_id = $row['member_id'];
    if (!isset($members[$member_id])) {
        // Should not happen, but just in case
        $members[$member_id] = [
            'member_id' => (string)$member_id,
            'member_name' => $row['name'],
            'collection' => [],
            'totals' => [
                'welfare' => 0,
                'investment' => 0,
                'sacco_fee' => 0,
                'savings' => 0,
                'tyres' => 0,
                'insurance' => 0,
                'grand_total_deductions' => 0
            ]
        ];
    }
    $number_plate = $row['number_plate'];
    // Per-vehicle aggregation
    if (!isset($members[$member_id]['collection'][$number_plate])) {
        $members[$member_id]['collection'][$number_plate] = [
            'number_plate' => $number_plate,
            'welfare' => 0,
            'investment' => 0,
            'sacco_fee' => 0,
            'savings' => 0,
            'tyres' => 0,
            'insurance' => 0,
            'total_deductions' => 0
        ];
    }
    // Sum up deductions for this transaction
    $fields = ['welfare','investment','sacco_fee','savings','tyres','insurance'];
    $total = 0;
    foreach ($fields as $field) {
        $val = isset($row[$field]) ? (float)$row[$field] : 0;
        $members[$member_id]['collection'][$number_plate][$field] += $val;
        $members[$member_id]['totals'][$field] += $val;
        $total += $val;
    }
    $members[$member_id]['collection'][$number_plate]['total_deductions'] += $total;
    $members[$member_id]['totals']['grand_total_deductions'] += $total;
}

// Format collections as arrays
foreach ($members as &$member) {
    $member['collection'] = array_values($member['collection']);
}

// If only one member, return as object, else as array
$final_data = count($members) === 1 ? array_values($members)[0] : array_values($members);

http_response_code(200);
echo json_encode([
    "message" => "Member collections and deductions retrieved successfully",
    "response" => "success",
    "data" => $final_data
]); 