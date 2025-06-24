<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../../config/Database.php';
include_once __DIR__ . '/../../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (
    !empty($data->username) &&
    !empty($data->password) &&
    !empty($data->name)
) {
    $user->username = $data->username;
    $user->password = $data->password;
    $user->name = $data->name;
    $user->stage = isset($data->stage) ? $data->stage : '';
    $user->user_town = isset($data->user_town) ? $data->user_town : 0;
    $user->quota_start = isset($data->quota_start) ? $data->quota_start : 0;
    $user->quota_end = isset($data->quota_end) ? $data->quota_end : 0;
    $user->current_quota = isset($data->current_quota) ? $data->current_quota : 0;
    $user->prefix = isset($data->prefix) ? $data->prefix : 'CHN-';
    $user->printer_name = isset($data->printer_name) ? $data->printer_name : 'InnerPrinter';


    if ($user->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "User was created.", "id" => $user->id));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create user."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is incomplete. `username`, `password`, and `name` are required."));
} 