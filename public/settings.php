<?php
// public/settings.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $b_name = trim($_POST['business_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']); // Optional: Allow email change? Let's assume yes for merchant info display.
    
    // Simple update
    $sql = "UPDATE users SET business_name=?, phone=?, address=?, email=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$b_name, $phone, $address, $email, $user_id])) {
        $success = "Settings updated successfully!";
        // Update session if needed, mainly business name might be stored there?
        $_SESSION['user_name'] = $b_name; 
    } else {
        $error = "Failed to update settings.";
    }
}

// Fetch Current Data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$title = "Settings";
include '../includes/header.php';
include '../includes/navbar.php'; // This will now load the Sidebar layout once refactored
?>

<div class="max-w-4xl mx-auto">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Business Profile</h3>
                <p class="mt-1 text-sm text-gray-600">This information will be displayed on your invoices.</p>
            </div>
        </div>
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="" method="POST">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <!-- Notifications -->
                        <?php if ($success): ?>
                            <div class="bg-green-50 border-l-4 border-green-400 p-4">
                                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-4">
                                <label for="business_name" class="block text-sm font-medium text-gray-700">Business / Store Name</label>
                                <input type="text" name="business_name" id="business_name" value="<?php echo htmlspecialchars($user['business_name']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>

                            <div class="col-span-6 sm:col-span-4">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 border">
                            </div>

                            <div class="col-span-6">
                                <label for="address" class="block text-sm font-medium text-gray-700">Business Address</label>
                                <textarea name="address" id="address" rows="3" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md p-2"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Details
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
