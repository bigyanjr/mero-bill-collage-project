<?php
// verify_bot.php
session_start();
// Mock Admin Login
$_SESSION['user_id'] = 999;
$_SESSION['role'] = 'admin';

echo "--- Testing Admin Commands ---\n";

// Test 1: Stats
echo "CMD: stats\n";
include 'public/api_chat.php'; // This will error because api_chat.php expects JSON input and outputs JSON.
// We need to refactor or just use curl to test properly.
?>
