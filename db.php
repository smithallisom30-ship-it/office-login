<?php
require __DIR__ . '/vendor/autoload.php';

// Reads from Render environment variable, or falls back to local config.php
$uri = getenv('MONGODB_URI') 
    ?: (file_exists(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : null);

try {
    $client = new MongoDB\Client($uri);
    $client->selectDatabase('admin')->command(['ping' => 1]);
    $users  = $client->office->users;
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}