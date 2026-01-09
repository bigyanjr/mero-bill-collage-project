<?php
// public/dashboard.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$is_demo = $_SESSION['is_demo'];

// Stats Logic
$today = date('Y-m-d');

// 1. Get Gross Sales (Today)
$stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM invoices WHERE merchant_id = ? AND DATE(created_at) = ?");
$stmt->execute([$user_id, $today]);
$today_gross = $stmt->fetch()['total'] ?? 0.00;

// 2. Get Expenses (Today)
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE merchant_id = ? AND expense_date = ?");
$stmt->execute([$user_id, $today]);
$today_expenses = $stmt->fetch()['total'] ?? 0.00;

// 3. Net Today
$today_sales = $today_gross - $today_expenses;


// Total Stats
// 1. Get Gross Sales (Total)
$stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM invoices WHERE merchant_id = ?");
$stmt->execute([$user_id]);
$total_gross = $stmt->fetch()['total'] ?? 0.00;

// 2. Get Expenses (Total)
$stmt = $pdo->prepare("SELECT SUM(amount) as total FROM expenses WHERE merchant_id = ?");
$stmt->execute([$user_id]);
$total_expenses = $stmt->fetch()['total'] ?? 0.00;

// 3. Net Total
$total_sales = $total_gross - $total_expenses;

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM invoices WHERE merchant_id = ?");
$stmt->execute([$user_id]);
$total_invoices = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE merchant_id = ?");
$stmt->execute([$user_id]);
$total_products = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE merchant_id = ? AND stock_quantity < 5");
$stmt->execute([$user_id]);
$low_stock_count = $stmt->fetch()['count'];

// Chart Data (Last 7 Days)
$chart_dates = [];
$chart_sales = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_dates[] = date('M d', strtotime($date));
    
    $stmt = $pdo->prepare("SELECT SUM(grand_total) as total FROM invoices WHERE merchant_id = ? AND DATE(created_at) = ?");
    $stmt->execute([$user_id, $date]);
    $chart_sales[] = $stmt->fetch()['total'] ?? 0;
}
$chart_dates_json = json_encode($chart_dates);
$chart_sales_json = json_encode($chart_sales);

$title = "Dashboard";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="animate-fade-in">
    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Overview of your business performance.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="invoices.php" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-hover">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                New Invoice
            </a>
        </div>
    </div>

    <?php if ($is_demo && is_demo_expired($pdo, $user_id)): ?>
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Your Demo plan has <strong>EXPIRED</strong>. Please <a href="#" class="font-medium underline text-yellow-700 hover:text-yellow-600">Upgrade to Paid</a> to continue using all features.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1 -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Today's Sales</dt>
                            <dd class="text-2xl font-semibold text-gray-900">NPR <?php echo number_format($today_sales, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="reports.php" class="font-medium text-blue-600 hover:text-blue-500">View detailed reports</a>
                </div>
            </div>
        </div>
        
        <!-- Card 1.5 (Total Sales) -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                         <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Sales</dt>
                            <dd class="text-2xl font-semibold text-gray-900">NPR <?php echo number_format($total_sales, 2); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="reports.php" class="font-medium text-indigo-600 hover:text-indigo-500">View detailed reports</a>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Invoices</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($total_invoices); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="invoices.php" class="font-medium text-blue-600 hover:text-blue-500">View all invoices</a>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Products</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($total_products); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="products.php" class="font-medium text-blue-600 hover:text-blue-500">Manage Inventory</a>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                         <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Low Stock Alert</dt>
                            <dd class="text-2xl font-semibold text-gray-900"><?php echo number_format($low_stock_count); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
             <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="products.php" class="font-medium text-red-600 hover:text-red-500">Restock items</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section (Placeholder) -->
    <div class="mt-8">
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Sales Performance</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo $chart_dates_json; ?>,
            datasets: [{
                label: 'Sales (NPR)',
                data: <?php echo $chart_sales_json; ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
