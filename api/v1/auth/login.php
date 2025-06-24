<?php

use Firebase\JWT\JWT;

include_once __DIR__ . '/../../../config/Database.php';

// Headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$database = new Database();
$db = $database->connect();

if ($db === null) {
    http_response_code(503); // Service Unavailable
    echo json_encode(array("message" => "Failed to connect to the database.", "response" => "error"));
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data.", "response" => "error"));
    return;
}

$username = $data->username;
$password = $data->password;
$user = null;
$role = null;

// Check in admin table first
$query = "SELECT * FROM `_admin_` WHERE username = :username";
$stmt = $db->prepare($query);
$stmt->bindParam(':username', $username);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    // SECURITY RISK: Passwords are in plaintext. Use password_hash() and password_verify().
    if ($password === $row['password']) {
        $user = $row;
        $role = 'admin';
    }
}

// If not found in admin, check in user table
if (!$user) {
    $query = "SELECT * FROM `_user_` WHERE username = :username";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // SECURITY RISK: Passwords are in plaintext. Use password_hash() and password_verify().
        if ($password === $row['password']) {
            $user = $row;
            $role = 'user';
        }
    }
}

if ($user) {
    $secret_key = $_ENV['JWT_SECRET'];
    $issuer_claim = $_ENV['JWT_ISSUER'] ?? 'your_issuer';
    $audience_claim = $_ENV['JWT_AUDIENCE'] ?? 'your_audience';
    $issuedat_claim = time();
    $notbefore_claim = $issuedat_claim;
    $expire_claim = $issuedat_claim + 3600; // expire in 1 hour

    $token = array(
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => array(
            "id" => $user['id'],
            "username" => $user['username'],
            "role" => $role
        )
    );

    $jwt = JWT::encode($token, $secret_key, 'HS256');

    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Login successful.",
            "response" => "success",
            "data" => array(
                "token" => $jwt
            )
        )
    );
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Login failed. Invalid credentials.", "response" => "error"));
} 