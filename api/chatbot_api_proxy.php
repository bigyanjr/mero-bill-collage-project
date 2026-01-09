<?php
// api/chatbot_api_proxy.php
// This script acts as a bridge between the frontend and the external AI API to avoid exposing keys.

header('Content-Type: application/json');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? '';

if (empty($message)) {
    echo json_encode(['reply' => 'Please ask something.']);
    exit;
}

// --- CONFIGURATION FOR EXTERNAL API ---
$api_url = "https://api.example-ai-provider.com/v1/chat"; // REPLACE THIS
$api_key = "YOUR_API_KEY_HERE"; // REPLACE THIS

// --- SIMULATION MODE (Since we don't have a real key yet) ---
// For the purpose of this project, we return a mock response.
// In production, uncomment the cURL block below.

$mock_responses = [
    "hello" => "Namaste! Welcome to Mero Bill. How can I help you today?",
    "pricing" => "Our Paid plan starts at NPR 1000/month for unlimited invoicing.",
    "features" => "You can manage products, create invoices, and track your sales with Mero Bill.",
    "default" => "Mero Bill is a cloud-based billing system for Nepali businesses. You can manage products, customers, invoices and see your sales reports here."
];

$reply = $mock_responses['default'];
foreach ($mock_responses as $key => $val) {
    if (stripos($message, $key) !== false) {
        $reply = $val;
        break;
    }
}

// Return the simulated response
echo json_encode([
    'reply' => $reply,
    'status' => 'success'
]);

/* 
// --- REAL API IMPLEMENTATION (cURL) ---
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => $message]]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(['error' => 'API Error: ' . curl_error($ch)]);
} else {
    echo $response; // Assuming the API returns JSON
}
curl_close($ch);
*/
?>
