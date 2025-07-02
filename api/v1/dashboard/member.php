<?php
// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../auth/authorize.php';

$userData = authorize(['member']);

$database = new Database();
$db = $database->connect();

if ($db === null) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed.", "response" => "error"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

$start_date = isset($data->start_date) ? $data->start_date : (isset($_GET['start_date']) ? $_GET['start_date'] : null);
$end_date = isset($data->end_date) ? $data->end_date : (isset($_GET['end_date']) ? $_GET['end_date'] : null);

try {
    // Find the member's _user_ username (assuming member.number = _user_.username)
    $stmt = $db->prepare('SELECT number FROM member WHERE id = :id');
    $stmt->bindParam(':id', $userData->id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $member_number = $row ? $row['number'] : null;

    if (!$member_number) {
        echo json_encode(["message" => "Unable to determine member number for dashboard.", "response" => "error"]);
        exit();
    }

    // Base queries for member
    $where_clauses = ["collected_by = :collected_by"];
    $params = [':collected_by' => $member_number];
    if ($start_date) {
        $where_clauses[] = "t_date >= :start_date";
        $params[':start_date'] = $start_date;
    }
    if ($end_date) {
        $where_clauses[] = "t_date <= :end_date";
        $params[':end_date'] = $end_date;
    }
    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    $transactions_query = "SELECT COUNT(*) as transactions, SUM(amount) as total_amount FROM new_transaction" . $where_sql;
    $vehicles_query = "SELECT COUNT(DISTINCT number_plate) as vehicles FROM new_transaction" . $where_sql;

    // Execute queries
    $stmt_transactions = $db->prepare($transactions_query);
    $stmt_transactions->execute($params);
    $transactions_data = $stmt_transactions->fetch(PDO::FETCH_ASSOC);

    $stmt_vehicles = $db->prepare($vehicles_query);
    $stmt_vehicles->execute($params);
    $vehicles_data = $stmt_vehicles->fetch(PDO::FETCH_ASSOC);

    // Prepare response data (no owners, no office_stats)
    $response_data = [
        "transactions" => $transactions_data['transactions'] ?? "0",
        "total_amount" => (int)($transactions_data['total_amount'] ?? 0),
        "vehicles" => $vehicles_data['vehicles'] ?? "0"
    ];
    
    // Final Response
    http_response_code(200);
    echo json_encode([
        "message" => "Dashboard data retrieved successfully",
        "response" => "success",
        "data" => $response_data
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error: " . $e->getMessage(), "response" => "error"]);
} 