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

$query = 'SELECT v.id, v.number_plate, v.member_id, m.name as member_name
          FROM vehicles v
          LEFT JOIN member m ON v.member_id = m.id
          ORDER BY v.number_plate ASC';

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if($num > 0) {
    $vehicles_arr = array();
    $vehicles_arr['data'] = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $vehicle_item = array(
            'id' => $id,
            'number_plate' => $number_plate,
            'member_id' => $member_id,
            'member_name' => $member_name
        );

        array_push($vehicles_arr['data'], $vehicle_item);
    }
    
    $vehicles_arr['message'] = 'Vehicles retrieved successfully';
    $vehicles_arr['response'] = 'success';

    echo json_encode($vehicles_arr);
} else {
    echo json_encode(
        array('message' => 'No Vehicles Found', 'response' => 'success', 'data' => [])
    );
} 