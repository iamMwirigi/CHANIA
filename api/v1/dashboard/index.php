<?php
require_once __DIR__ . '/../../../config/Database.php';

$db_instance = new Database();
$db = $db_instance->connect();

if ($db === null) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed.", "response" => "error"]);
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

try {
    // Base queries
    $transactions_query = "SELECT COUNT(*) as transactions, SUM(amount) as total_amount FROM new_transaction";
    $vehicles_query = "SELECT COUNT(DISTINCT number_plate) as vehicles FROM new_transaction";
    $owners_query = "SELECT COUNT(DISTINCT id) as owners FROM member";
    $office_stats_query = "SELECT stage_name as office_name, COUNT(*) as transaction_count, SUM(amount) as office_sales FROM new_transaction";

    // Date filtering
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
    $where_sql = count($where_clauses) > 0 ? " WHERE " . implode(" AND ", $where_clauses) : "";

    // Append WHERE clause to queries that need it
    $transactions_query .= $where_sql;
    $office_stats_query .= $where_sql . " GROUP BY stage_name";

    // Execute queries
    $stmt_transactions = $db->prepare($transactions_query);
    $stmt_transactions->execute($params);
    $transactions_data = $stmt_transactions->fetch(PDO::FETCH_ASSOC);

    $stmt_vehicles = $db->prepare($vehicles_query);
    $stmt_vehicles->execute();
    $vehicles_data = $stmt_vehicles->fetch(PDO::FETCH_ASSOC);

    $stmt_owners = $db->prepare($owners_query);
    $stmt_owners->execute();
    $owners_data = $stmt_owners->fetch(PDO::FETCH_ASSOC);

    $stmt_office_stats = $db->prepare($office_stats_query);
    $stmt_office_stats->execute($params);
    $office_stats_data = $stmt_office_stats->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response data
    $response_data = [
        "transactions" => $transactions_data['transactions'] ?? "0",
        "total_amount" => (int)($transactions_data['total_amount'] ?? 0),
        "vehicles" => $vehicles_data['vehicles'] ?? "0",
        "owners" => $owners_data['owners'] ?? "0",
        "office_stats" => array_map(function ($office) {
            return [
                "office_name" => $office['office_name'],
                "transaction_count" => $office['transaction_count'],
                "office_sales" => (int)$office['office_sales'],
                "coins" => 0, // Placeholder
                "sacco" => 0, // Placeholder
                "new_terminus" => 0, // Placeholder
                "service" => 0, // Placeholder
                "delivery" => 0, // Placeholder
            ];
        }, $office_stats_data)
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