<?php
// public/reports.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];

// 1. Monthly Sales Data (Last 6 Months)
// Used for Chart.js
$sql_monthly = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(grand_total) as total 
                FROM invoices 
                WHERE merchant_id = ? AND status != 'unpaid' 
                GROUP BY month 
                ORDER BY month DESC LIMIT 6";
$stmt = $pdo->prepare($sql_monthly);
$stmt->execute([$user_id]);
$monthly_data = array_reverse($stmt->fetchAll()); // Reverse to show chronological

$months = array_map(function($d) { return date('M Y', strtotime($d['month'])); }, $monthly_data);
$sales = array_map(function($d) { return $d['total']; }, $monthly_data);

// 2. Top Selling Products
$sql_top = "SELECT p.name, SUM(ii.quantity) as sold_qty 
            FROM invoice_items ii 
            JOIN invoices i ON ii.invoice_id = i.id 
            JOIN products p ON ii.product_id = p.id 
            WHERE i.merchant_id = ? 
            GROUP BY p.id 
            ORDER BY sold_qty DESC LIMIT 5";
$stmt = $pdo->prepare($sql_top);
$stmt->execute([$user_id]);
$top_products = $stmt->fetchAll();

$title = "Reports";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="animate-fade-in pb-12">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Reports & Analytics</h1>
        <p class="mt-1 text-sm text-gray-500">Insights into your business performance.</p>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h3 class="text-lg font-bold text-gray-700 mb-4">Sales Trend (Last 6 Months)</h3>
        <div class="relative h-72">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top Products -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-lg font-bold text-gray-700 mb-4">Top Selling Products</h3>
            <?php if (empty($top_products)): ?>
                <p class="text-gray-500">No sales data available yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($top_products as $idx => $prod): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <span class="text-gray-400 font-bold w-6">#<?php echo $idx + 1; ?></span>
                            <span class="text-gray-800 font-medium ml-2"><?php echo htmlspecialchars($prod['name']); ?></span>
                        </div>
                        <div class="text-blue-600 font-bold"><?php echo $prod['sold_qty']; ?> sold</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- summary box or other report -->
        <div class="bg-white p-6 rounded-lg shadow flex flex-col justify-center items-center text-center">
             <h3 class="text-lg font-bold text-gray-700 mb-2">Export Data</h3>
             <p class="text-sm text-gray-500 mb-6">Download your sales data in Excel/CSV or PDF format.</p>
             <div class="flex space-x-3">
                 <a href="export_report.php?type=csv" class="bg-indigo-600 text-white px-5 py-2 rounded-full hover:bg-indigo-700 transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Excel/CSV
                 </a>
                 <button onclick="downloadPDF()" class="bg-red-600 text-white px-5 py-2 rounded-full hover:bg-red-700 transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Download PDF
                 </button>
             </div>
        </div>
    </div>
</div>

<!-- Chart.js & html2pdf -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    // JS for Chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Sales (NPR)',
                data: <?php echo json_encode($sales); ?>,
                borderColor: 'rgb(37, 99, 235)',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // PDF Download Function
    function downloadPDF() {
        // Clone the body to specific content for PDF to avoid messy whole-page capture
        // We select the main container
        const element = document.querySelector('.animate-fade-in');
        
        // Options for PDF
        const opt = {
            margin:       0.3,
            filename:     'Business_Report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true }, 
            jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        // Trick: hide the export buttons during capture
        const buttons = document.querySelector('.space-x-3');
        if(buttons) buttons.style.display = 'none';

        html2pdf().set(opt).from(element).save().then(function(){
            // Restore buttons
            if(buttons) buttons.style.display = 'flex';
        });
    }
</script>

<?php include '../includes/footer.php'; ?>
