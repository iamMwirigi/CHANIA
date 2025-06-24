<?php
// For now, we will just return a success message.
// We will add authentication logic later.

$data = json_decode(file_get_contents("php://input"));

// A basic check for username and password
if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(array("message" => "Incomplete data.", "response" => "error"));
    return;
}

// Dummy user validation
if ($data->username === 'test' && $data->password === 'password') {
    http_response_code(200);
    echo json_encode(
        array(
            "message" => "Login successful.",
            "response" => "success",
            "data" => array(
                "token" => "dummy-jwt-token-for-now"
            )
        )
    );
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Login failed. Invalid credentials.", "response" => "error"));
} 