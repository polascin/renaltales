<?php
// Test registration programmatically
session_start();

// Set up environment
$_POST = [
    'csrf_token' => '',
    'username' => 'testuser123',
    'email' => 'testuser@example.com',
    'full_name' => 'Test User',
    'language_preference' => 'en',
    'password' => 'TestPass123!',
    'password_confirmation' => 'TestPass123!',
    'agree_terms' => '1'
];

$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/register';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// Include the application
require_once __DIR__ . '/public/index.php';
?>
