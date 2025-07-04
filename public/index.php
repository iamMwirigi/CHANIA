<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// This will be our simple router.
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = strtok($request_uri, '?');

// Handle pre-flight requests for CORS
if ($request_method == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple routing
switch ($uri) {
    case '/':
        echo json_encode(["message" => "Welcome to Chania Collections API."]);
        break;
    
    // Auth
    case '/api/v1/auth/login':
        require __DIR__ . '/../api/v1/auth/login.php';
        break;
    case '/api/v1/auth/member/login':
        require __DIR__ . '/../api/v1/auth/member/login.php';
        break;

    // Dashboard
    case '/api/v1/dashboard':
        require __DIR__ . '/../api/v1/dashboard/index.php';
        break;
    case '/api/v1/dashboard/member':
        require __DIR__ . '/../api/v1/dashboard/member.php';
        break;

    // Members
    case '/api/v1/members/create':
        require __DIR__ . '/../api/v1/members/create.php';
        break;
    case '/api/v1/members/read':
        require __DIR__ . '/../api/v1/members/read.php';
        break;
    case '/api/v1/members/read_one':
        require __DIR__ . '/../api/v1/members/read_one.php';
        break;
    case '/api/v1/members/update':
        require __DIR__ . '/../api/v1/members/update.php';
        break;
    case '/api/v1/members/delete':
        require __DIR__ . '/../api/v1/members/delete.php';
        break;

    // Vehicles
    case '/api/v1/vehicles/create':
        require __DIR__ . '/../api/v1/vehicles/create.php';
        break;
    case '/api/v1/vehicles/read':
        require __DIR__ . '/../api/v1/vehicles/read.php';
        break;
    case '/api/v1/vehicles/read_one':
        require __DIR__ . '/../api/v1/vehicles/read_one.php';
        break;
    case '/api/v1/vehicles/update':
        require __DIR__ . '/../api/v1/vehicles/update.php';
        break;
    case '/api/v1/vehicles/delete':
        require __DIR__ . '/../api/v1/vehicles/delete.php';
        break;

    // Collections
    case '/api/v1/collections/read':
        require __DIR__ . '/../api/v1/collections/read.php';
        break;
    case '/api/v1/collections/read_one':
        require __DIR__ . '/../api/v1/collections/read_one.php';
        break;
    case '/api/v1/collections/member_collections':
        require __DIR__ . '/../api/v1/collections/member_collections.php';
        break;
    case '/api/v1/collections/create':
        require __DIR__ . '/../api/v1/collections/create.php';
        break;
    case '/api/v1/collections/all_members_collections.php':
        require __DIR__ . '/../api/v1/collections/all_members_collections.php';
        break;
    case '/api/all_member_collections':
        require __DIR__ . '/../api/v1/collections/all_members_collections.php';
        break;
    case '/api/v1/collections/search.php':
        require __DIR__ . '/../api/v1/collections/search.php';
        break;

    // Stages
    case '/api/v1/stages/create':
        require __DIR__ . '/../api/v1/stages/create.php';
        break;
    case '/api/v1/stages/read':
        require __DIR__ . '/../api/v1/stages/read.php';
        break;
    case '/api/v1/stages/read_one':
        require __DIR__ . '/../api/v1/stages/read_one.php';
        break;
    case '/api/v1/stages/update':
        require __DIR__ . '/../api/v1/stages/update.php';
        break;
    case '/api/v1/stages/delete':
        require __DIR__ . '/../api/v1/stages/delete.php';
        break;

    // SMS
    case '/api/v1/sms/read':
        require __DIR__ . '/../api/v1/sms/read.php';
        break;
    case '/api/v1/sms/send':
        require __DIR__ . '/../api/v1/sms/send.php';
        break;
    case '/api/v1/sms/members':
        require __DIR__ . '/../api/v1/sms/members.php';
        break;

    // Users
    case '/api/v1/users/create':
        require __DIR__ . '/../api/v1/users/create.php';
        break;

    case '/api/v1/users/read':
        require __DIR__ . '/../api/v1/users/read.php';
        break;

    case '/api/v1/users/read_one':
        require __DIR__ . '/../api/v1/users/read_one.php';
        break;
    
    case '/api/v1/users/update':
        require __DIR__ . '/../api/v1/users/update.php';
        break;
    
    case '/api/v1/users/delete':
        require __DIR__ . '/../api/v1/users/delete.php';
        break;

    // Default
    default:
        http_response_code(404);
        echo json_encode(["message" => "Endpoint not found."]);
        break;
} 