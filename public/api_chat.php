<?php
// public/api_chat.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = trim($input['message'] ?? '');

if (empty($userMessage)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message required']);
    exit;
}

// Configuration
$apiKey = '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'; 
$model = 'tngtech/deepseek-r1t-chimera:free';

// Context Selection
$role = $_SESSION['role'] ?? 'demo';
$username = $_SESSION['username'] ?? 'User';
// Basic cleaning of username for the prompt
$cleanUsername = preg_replace('/[^a-zA-Z0-9 ]/', '', $username);

// Initialize History
if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

// System Prompt - Branch by Role
if ($role === 'admin') {
    // --- SUPER ADMIN LOGIC (UNTOUCHED) ---
    $baseInstruction = "You are the Mero Bill Assistant talking to $role '$cleanUsername'.
CRITICAL: You are a JSON-Generator. You must output VALID JSON ONLY.
Do not output markdown, reasoning, or any text outside the JSON block.

Response Schema:
{
  \"reply\": \"string - your friendly message to the user\",
  \"action\": \"string|null - 'CREATE_DEMO' OR 'CREATE_MERCHANT|name|email|pass' OR null\"
}

LOGIC:
- If user wants DEMO: set action='CREATE_DEMO', reply='Creating your demo account...'.
- If user wants REAL: Ask for Name, Email, Password. When you have ALL 3, set action='CREATE_MERCHANT|...' and reply='Creating your merchant account...'.
- Otherwise: set action=null, reply=your conversation response.

History is provided constraints. Do not repeat completed actions.";

} else {
    // --- MERCHANT & DEMO LOGIC (NEW) ---
    
    // 1. Define Persona
    if ($role === 'demo') {
        $persona = "You are a HYPED Demo Assistant. Talk like a friendly tech bro ('Yo', 'Let's go', 'Bet'). Your job is to show off the system speed.";
    } else {
        $persona = "You are a Professional Business Assistant. Be concise, efficient, and polite.";
    }

    $baseInstruction = "$persona You are talking to $role '$cleanUsername'.
CRITICAL: You are a JSON-Generator. You must output VALID JSON ONLY. No markdown, no thinking.

Response Schema:
{
  \"reply\": \"string - message to user\",
  \"action\": \"string|null - see supported actions below\"
}

SUPPORTED ACTIONS (Use ONLY when you have ALL details):
1. Create Product: 'CREATE_PRODUCT|Name|Price|Stock' (e.g. 'CREATE_PRODUCT|iPhone|999|10')
2. Create Customer: 'CREATE_CUSTOMER|Name|Phone' (e.g. 'CREATE_CUSTOMER|John Doe|9800000000')
3. Create Invoice: 'CREATE_INVOICE|CustomerName|ProductName|Qty' (e.g. 'CREATE_INVOICE|John Doe|iPhone|1')
4. Null: If gathering info, set action=null.

LOGIC:
- Ask 1 question at a time.
- when you have all info for an action, trigger it.";
}

// Build Message Chain
$messages = [['role' => 'system', 'content' => $baseInstruction]];
foreach ($_SESSION['chat_history'] as $msg) {
    $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
}
$messages[] = ['role' => 'user', 'content' => $userMessage];

$requestData = [
    'model' => $model,
    'messages' => $messages,
    'temperature' => 0.3,
];

// Execute API Call
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: http://localhost/pos',
        'X-Title: Mero Bill POS'
    ],
    CURLOPT_SSL_VERIFYPEER => false 
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$botReply = "I'm having trouble connecting right now.";

if ($httpCode === 200) {
    $apiJson = json_decode($response, true);
    $rawContent = $apiJson['choices'][0]['message']['content'] ?? '{}';
    
    // DEBUG: Log raw output
    file_put_contents('chat_debug.txt', date('Y-m-d H:i:s') . " RAW: " . $rawContent . "\n\n", FILE_APPEND);
    
    // 1. Strip <think> blocks
    $cleanContent = preg_replace('/<think>.*?<\/think>/s', '', $rawContent);
    
    // 2. Extract from Markdown Code Block
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $cleanContent, $matches)) {
        $jsonStr = $matches[1];
    } elseif (preg_match('/\{(?:[^{}]|(?R))*\}/s', $cleanContent, $matches)) {
        // Advanced Match: Balanced braces (recursive) - best for nested structures
        $jsonStr = $matches[0];
    } elseif (preg_match('/\{.*?\}/s', $cleanContent, $matches)) {
        // Fallback: Non-greedy match (first {...} block found)
        $jsonStr = $matches[0];
    } else {
        $jsonStr = $cleanContent;
    }
    
    $parsed = json_decode($jsonStr, true);
    
    if (json_last_error() === JSON_ERROR_NONE && isset($parsed['reply'])) {
        $botReply = $parsed['reply'];
        $action = $parsed['action'] ?? null;
        
        // Execute Action - ADMIN
        if ($role === 'admin') {
            if ($action === 'CREATE_DEMO') {
                $rnd = substr(str_shuffle("0123456789"), 0, 4);
                $newUser = "demo$rnd";
                $newPass = "pass$rnd";
                $newEmail = "demo$rnd@example.com";
                $res = create_user_command($pdo, $newUser, $newEmail, $newPass, 'demo');
                $botReply .= "\n\n" . "✅ Demo Account Ready!\n**Username:** $newUser\n**Email:** $newEmail\n**Password:** $newPass";
                
            } elseif ($action && strpos($action, 'CREATE_MERCHANT') === 0) {
                $parts = explode('|', $action);
                if (count($parts) >= 4) {
                     $res = create_user_command($pdo, trim($parts[1]), trim($parts[2]), trim($parts[3]), 'merchant');
                     $botReply .= "\n\n" . $res;
                }
            }
        } 
        // Execute Action - MERCHANT/DEMO
        else {
             if ($action && strpos($action, 'CREATE_PRODUCT') === 0) {
                 $parts = explode('|', $action);
                 if (count($parts) >= 4) {
                     $res = create_product_command($pdo, $_SESSION['user_id'], trim($parts[1]), trim($parts[2]), trim($parts[3]));
                     $botReply .= "\n\n" . $res;
                 }
             } elseif ($action && strpos($action, 'CREATE_CUSTOMER') === 0) {
                 $parts = explode('|', $action);
                  if (count($parts) >= 3) {
                     $res = create_customer_command($pdo, $_SESSION['user_id'], trim($parts[1]), trim($parts[2]));
                     $botReply .= "\n\n" . $res;
                 }
             } elseif ($action && strpos($action, 'CREATE_INVOICE') === 0) {
                 $parts = explode('|', $action);
                  if (count($parts) >= 4) {
                     $res = create_invoice_command($pdo, $_SESSION['user_id'], trim($parts[1]), trim($parts[2]), trim($parts[3]));
                     $botReply .= "\n\n" . $res;
                 }
             }
        }

    } else {
        $botReply = "I understood, but had a system error processing the request. Please try again.";
        file_put_contents('chat_debug.txt', "JSON ERROR: " . json_last_error_msg() . "\n", FILE_APPEND);
    }
} else {
    // Log Connection Errors
    $err = curl_error($ch);
    file_put_contents('chat_debug.txt', date('Y-m-d H:i:s') . " HTTP $httpCode ERROR: $err \nResponse: $response\n\n", FILE_APPEND);
}

// Update History
$_SESSION['chat_history'][] = ['role' => 'user', 'content' => $userMessage];
$_SESSION['chat_history'][] = ['role' => 'assistant', 'content' => $botReply];
if (count($_SESSION['chat_history']) > 6) {
    $_SESSION['chat_history'] = array_slice($_SESSION['chat_history'], -6);
}

echo json_encode(['reply' => $botReply]);

// Helper Function for Creation
function create_user_command($pdo, $username, $email, $password, $role) {
    // Basic Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Error: Invalid email address.";
    
    try {
        // Check exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) return "Error: User already exists.";

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $business = ucfirst($username) . "'s Business";
        
        // Handle Plan (simplification: Demo plan for all for now)
        $stmt = $pdo->prepare("SELECT id FROM plans WHERE name = 'Demo' LIMIT 1");
        $stmt->execute();
        $plan = $stmt->fetch();
        $planId = $plan['id'] ?? 1;

        $pdo->beginTransaction();
        $sql = "INSERT INTO users (username, email, password_hash, role, plan_id, business_name, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $email, $hash, $role, $planId, $business]);
        $userId = $pdo->lastInsertId();
        
        // Add Plan Record
        $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
        $stmtPlan = $pdo->prepare("INSERT INTO user_plans (user_id, plan_id, start_date, end_date, payment_status, amount_paid) VALUES (?, ?, NOW(), ?, 'completed', 0.00)");
        $stmtPlan->execute([$userId, $planId, $expiry]);
        
        $pdo->commit();

        return "Success! Created $role user '$username' ($email).";
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return "System Error: " . $e->getMessage();
    }
}


function create_product_command($pdo, $userId, $name, $price, $stock) {
    try {
        $stmt = $pdo->prepare("INSERT INTO products (merchant_id, name, price, stock_quantity) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $name, $price, $stock]);
        return "✅ Product Created: **$name** ($$price, Qty: $stock)";
    } catch (Exception $e) {
        return "Error creating product: " . $e->getMessage();
    }
}

function create_customer_command($pdo, $userId, $name, $phone) {
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (merchant_id, name, phone) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $name, $phone]);
        return "✅ Customer Added: **$name** ($phone)";
    } catch (Exception $e) {
        return "Error creating customer: " . $e->getMessage();
    }
}

function create_invoice_command($pdo, $userId, $customerName, $productName, $qty) {
    try {
        // Smart Lookup: Find IDs from Names
        $custStmt = $pdo->prepare("SELECT id FROM customers WHERE merchant_id = ? AND name LIKE ? LIMIT 1");
        $custStmt->execute([$userId, "%$customerName%"]);
        $cust = $custStmt->fetch();
        if (!$cust) return "❌ Error: Could not find customer '$customerName'. Please add them first.";

        $prodStmt = $pdo->prepare("SELECT id, price FROM products WHERE merchant_id = ? AND name LIKE ? LIMIT 1");
        $prodStmt->execute([$userId, "%$productName%"]);
        $prod = $prodStmt->fetch();
        if (!$prod) return "❌ Error: Could not find product '$productName'. Please add it first.";

        // Calculate Totals
        $price = $prod['price'];
        $subtotal = $price * $qty;
        $invNum = "INV-" . strtoupper(substr(uniqid(), -5));

        $pdo->beginTransaction();

        // Create Invoice Header
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, merchant_id, customer_id, total_amount, grand_total, status, invoice_date) VALUES (?, ?, ?, ?, ?, 'unpaid', NOW())");
        $stmt->execute([$invNum, $userId, $cust['id'], $subtotal, $subtotal]);
        $invId = $pdo->lastInsertId();

        // Create Invoice Item
        $stmtItem = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtItem->execute([$invId, $prod['id'], $qty, $price, $subtotal]);

        $pdo->commit();

        return "✅ Invoice Generated: **$invNum** for $customerName\nOrdered: $productName x$qty\nTotal: $$subtotal";

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return "Error creates invoice: " . $e->getMessage();
    }
}
?>
