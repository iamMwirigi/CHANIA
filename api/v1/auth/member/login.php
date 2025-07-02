<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once __DIR__ . '/../../../../config/Database.php';
require_once __DIR__ . '/../../../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));

$phone_number = $data->phone_number ?? null;
$password = $data->password ?? null;

if (!empty($phone_number) && !empty($password)) {
    $query = "SELECT * FROM member WHERE phone_number = :phone_number AND entry_code = :entry_code LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":phone_number", $phone_number);
    $stmt->bindParam(":entry_code", $password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        $issuedAt = time();
        $expirationTime = $issuedAt + 86400; // 1 day
        $payload = [
            "iss" => "chania_api",
            "aud" => "chania_member",
            "iat" => $issuedAt,
            "nbf" => $issuedAt,
            "exp" => $expirationTime,
            "data" => [
                "id" => $member['id'],
                "phone_number" => $member['phone_number'],
                "role" => "member"
            ]
        ];
        $secret_key = getenv('JWT_SECRET') ?: 'your_secret_key';
        $jwt = JWT::encode($payload, $secret_key, 'HS256');

        http_response_code(200);
        echo json_encode([
            "message" => "Login successful.",
            "response" => "success",
            "data" => [
                "token" => $jwt
            ]
        ]);
        exit;
    } else {
        http_response_code(401);
        echo json_encode([
            "message" => "Invalid phone number or password.",
            "response" => "error"
        ]);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode([
        "message" => "phone_number and password are required.",
        "response" => "error"
    ]);
    exit;
} 