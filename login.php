<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

try {
    require 'db.php';

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required']);
        exit;
    }

    // Check if user already exists in MongoDB
    $existingUser = $users->findOne(['email' => $email]);

    if (!$existingUser) {
        // Insert new record with plain text password
        $result = $users->insertOne([
            'email' => $email,
            'password' => $password, // Saved as plain text
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        $userId = (string) $result->getInsertedId();
    } else {
        // Optional: Update the password if they log in again
        $users->updateOne(
            ['email' => $email],
            ['$set' => ['password' => $password]]
        );
        $userId = (string) $existingUser['_id'];
    }

    // Set Session and Cookie
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    setcookie('user_email', $email, [
        'expires' => time() + (86400 * 30),
        'path' => '/',
        'secure' => false,    // Set to true if running on HTTPS
        'httponly' => false,
        'samesite' => 'Lax'
    ]);

    // Return success response to JavaScript
    echo json_encode(['message' => 'Logged in successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server exception: ' . $e->getMessage()]);
}