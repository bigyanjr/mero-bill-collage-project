<?php
// admin/admin_dashboard.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure user is logged in as ADMIN
require_role('admin');

// Fetch Admin Stats
// 1. Total Users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role != 'admin'");
$stmt->execute();
$total_users = $stmt->fetch()['count'];

// 2. Demo Users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'demo'");
$stmt->execute();
$demo_users = $stmt->fetch()['count'];

// 3. Paid Users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'merchant'");
$stmt->execute();
$paid_users = $stmt->fetch()['count'];

// 4. Total Revenue (Sum of completed payments for plan upgrades)
$stmt = $pdo->prepare("SELECT SUM(amount_paid) as total FROM user_plans WHERE payment_status = 'completed'");
$stmt->execute();
$total_revenue = $stmt->fetch()['total'] ?? 0.00;

$title = "Admin Dashboard";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Overview of system users and revenue.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        
        <!-- Total Users Card (Red Theme) -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-600 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($total_users); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="admin_users.php" class="font-medium text-red-600 hover:text-red-500">View all users</a>
                </div>
            </div>
        </div>

        <!-- Demo Users Card (Already Yellow/Red mix, keeping consistent Red icon/text for admin theme focus or use standard colors? User asked for Red. I'll use Red accents.) -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Demo Users</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($demo_users); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="admin_users.php" class="font-medium text-red-600 hover:text-red-500">Manage users</a>
                </div>
            </div>
        </div>

        <!-- Paid Users Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-600 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Paid Merchants</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($paid_users); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="admin_users.php" class="font-medium text-red-600 hover:text-red-500">View paid users</a>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-lg transition-shadow duration-300">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-600 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="text-2xl font-semibold text-gray-900">NPR <?php echo number_format($total_revenue, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="#" class="font-medium text-red-600 hover:text-red-500">View transactions</a>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>
