<?php
// public/register_demo.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $business_name = trim($_POST['business_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic Validation
    if (empty($business_name) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email or username exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->rowCount() > 0) {
            $error = "Email or Username already taken.";
        } else {
            // Get Demo Plan Details
            $stmt = $pdo->prepare("SELECT * FROM plans WHERE name = 'Demo' LIMIT 1");
            $stmt->execute();
            $demo_plan = $stmt->fetch();

            if (!$demo_plan) {
                 $error = "System Error: Demo plan not configured.";
            } else {
                try {
                    $pdo->beginTransaction();

                    // Hash Password
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Calculate Expiry
                    $expiry_date = date('Y-m-d H:i:s', strtotime("+{$demo_plan['duration_days']} days"));

                    // Insert User
                    $sql = "INSERT INTO users (username, email, password_hash, role, plan_id, business_name, phone, plan_expires_at) 
                            VALUES (?, ?, ?, 'demo', ?, ?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$username, $email, $password_hash, $demo_plan['id'], $business_name, $phone, $expiry_date]);
                    $user_id = $pdo->lastInsertId();

                    // Record in User Plans History
                    $sql_plan = "INSERT INTO user_plans (user_id, plan_id, start_date, end_date, payment_status, amount_paid) 
                                 VALUES (?, ?, NOW(), ?, 'completed', 0.00)";
                    $stmt_plan = $pdo->prepare($sql_plan);
                    $stmt_plan->execute([$user_id, $demo_plan['id'], $expiry_date]);

                    $pdo->commit();
                    
                    $success = "Registration successful! Redirecting to login...";
                    header("refresh:2;url=login.php");

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "Registration failed: " . $e->getMessage();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Demo | Mero Bill</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="bg-slate-50 min-h-screen py-10 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center">

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Start your Demo</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Experience Mero Bill risk-free with our limited feature demo.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
        <div class="bg-white py-8 px-4 shadow-xl sm:rounded-lg sm:px-10 border border-gray-100 animate-slide-in">
            
            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded">
                    <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                </div>
            <?php else: ?>

            <form class="space-y-6" action="" method="POST">
                
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                    <div class="col-span-2">
                         <label for="business_name" class="block text-sm font-medium text-gray-700">Business Name</label>
                         <div class="mt-1">
                             <input type="text" name="business_name" required value="<?php echo isset($_POST['business_name']) ? htmlspecialchars($_POST['business_name']) : ''; ?>"
                                    class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                         </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <div class="mt-1">
                            <input type="text" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                        <div class="mt-1">
                            <input type="text" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                
                    <div class="col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm</label>
                        <div class="mt-1">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 p-4 rounded-md">
                    <div class="flex">
                        <div class="flex-shrink-0">
                             <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Demo Limitations</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>15 Days Validity</li>
                                    <li>Max 50 Products</li>
                                    <li>Max 50 Invoices</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-hover">
                        Create Demo Account
                    </button>
                </div>
            </form>
            <?php endif; ?>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Already have an account?
                        </span>
                    </div>
                </div>
                <div class="mt-6 text-center">
                    <a href="login.php" class="text-blue-600 hover:text-blue-500 font-medium">Log in to Mero Bill</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
