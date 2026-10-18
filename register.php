<?php
require 'db.php';
header('Content-Type: application/json');

$email    = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';

if (!$email || strlen($password) < 8) {
    http_response_code(400);
    exit(json_encode(['error' => 'Valid email and 8+ character password required']));
}

try {
    $users->insertOne([
        'email'     => strtolower($email),
        'password'  => password_hash($password, PASSWORD_DEFAULT),
        'createdAt' => new MongoDB\BSON\UTCDateTime(),
    ]);
    echo json_encode(['message' => 'Account created']);
} catch (MongoDB\Driver\Exception\BulkWriteException $e) {
    http_response_code(409);
    echo json_encode(['error' => 'Email already registered']);
}