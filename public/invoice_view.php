<?php
// public/invoice_view.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();

$invoice_id = $_GET['id'] ?? null;
if (!$invoice_id) {
    die("Invoice ID required.");
}

// Fetch Invoice details with Customer and Merchant info
$sql = "SELECT i.*, 
               c.name as customer_name, c.phone as customer_phone, c.address as customer_address, c.pan_vat as customer_pan,
               u.business_name, u.phone as merchant_phone, u.address as merchant_address, u.email as merchant_email
        FROM invoices i
        LEFT JOIN customers c ON i.customer_id = c.id
        JOIN users u ON i.merchant_id = u.id
        WHERE i.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$invoice_id]);
$invoice = $stmt->fetch();

if (!$invoice || ($invoice['merchant_id'] != $_SESSION['user_id'] && $_SESSION['role'] != 'admin')) {
    die("Invoice not found or access denied.");
}

$stmt_items = $pdo->prepare("SELECT ii.*, p.name as product_name, p.sku 
                             FROM invoice_items ii 
                             LEFT JOIN products p ON ii.product_id = p.id 
                             WHERE ii.invoice_id = ?");
$stmt_items->execute([$invoice_id]);
$items = $stmt_items->fetchAll();

$title = "Invoice " . $invoice['invoice_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($title); ?></title>
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- html2pdf for Client Side PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 py-10 min-h-screen">

    <div class="max-w-3xl mx-auto mb-6 flex justify-between px-4 items-center">
        <a href="invoices.php" class="text-gray-600 hover:text-gray-900 flex items-center transition font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back
        </a>
        <div class="space-x-2 flex">
            <!-- Edit Button -->
             <a href="invoice_edit.php?id=<?php echo $invoice_id; ?>" class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50 transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                Edit Invoice
            </a>
            <!-- Download PDF Button -->
            <button onclick="downloadPDF()" class="bg-blue-600 text-white px-5 py-2 rounded-lg shadow-md hover:bg-blue-700 transition flex items-center font-medium">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download PDF
            </button>
        </div>
    </div>

    <!-- Modern Invoice Template -->
    <div id="invoiceContent" class="max-w-3xl mx-auto bg-white p-12 shadow-2xl rounded-xl border border-gray-100 relative overflow-hidden">
        
        <!-- Header Section -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <img src="../mero.png" alt="Logo" class="h-16 w-auto mb-4 object-contain">
                <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($invoice['business_name']); ?></h2>
                <div class="text-sm text-gray-500 mt-2 space-y-1">
                    <p class="max-w-xs"><?php echo nl2br(htmlspecialchars($invoice['merchant_address'])); ?></p>
                    <p>Tel: <?php echo htmlspecialchars($invoice['merchant_phone']); ?></p>
                    <p><?php echo htmlspecialchars($invoice['merchant_email']); ?></p>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight text-blue-600">INVOICE</h1>
                <p class="text-gray-500 mt-2 text-lg">#<?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                
                <div class="mt-4 inline-block bg-gray-50 rounded p-4 text-left min-w-[200px]">
                     <div class="flex justify-between mb-1">
                         <span class="text-xs text-gray-500 uppercase font-semibold mr-4">Date</span>
                         <span class="text-sm font-bold text-gray-800"><?php echo date('M d, Y', strtotime($invoice['invoice_date'])); ?></span>
                     </div>
                     <div class="flex justify-between">
                         <span class="text-xs text-gray-500 uppercase font-semibold mr-4">Status</span>
                         <span class="text-sm font-bold uppercase <?php echo $invoice['status']=='paid'?'text-green-600':'text-red-500'; ?>"><?php echo htmlspecialchars($invoice['status']); ?></span>
                     </div>
                </div>
            </div>
        </div>

        <div class="border-t-2 border-dashed border-gray-200 mb-8"></div>

        <!-- Bill To -->
        <div class="mb-10">
            <h3 class="text-gray-400 uppercase text-xs font-bold tracking-wider mb-2">Invoiced To</h3>
            <?php if ($invoice['customer_id']): ?>
                <div class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($invoice['customer_name']); ?></div>
                <div class="text-gray-500 mt-1">
                    <?php if($invoice['customer_address']) echo '<div>' . htmlspecialchars($invoice['customer_address']) . '</div>'; ?>
                    <div><?php echo htmlspecialchars($invoice['customer_phone']); ?></div>
                    <?php if($invoice['customer_pan']) echo '<div class="mt-1 font-mono text-xs">PAN: ' . htmlspecialchars($invoice['customer_pan']) . '</div>'; ?>
                </div>
            <?php else: ?>
                <div class="text-xl font-bold text-gray-900">Walk-in Customer</div>
                <p class="text-gray-500">Cash Sale</p>
            <?php endif; ?>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-10">
            <thead>
                <tr class="bg-gray-50 rounded-lg">
                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider rounded-l-lg">Item Description</th>
                    <th class="text-center py-3 px-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Qty</th>
                    <th class="text-right py-3 px-2 text-xs font-bold text-gray-500 uppercase tracking-wider">Rate</th>
                    <th class="text-right py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider rounded-r-lg">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td class="py-4 px-4 text-gray-900 font-medium">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                    </td>
                    <td class="py-4 px-2 text-center text-gray-600"><?php echo $item['quantity']; ?></td>
                    <td class="py-4 px-2 text-right text-gray-600"><?php echo number_format($item['price'], 2); ?></td>
                    <td class="py-4 px-4 text-right text-gray-900 font-bold"><?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totals & Notes -->
        <div class="flex flex-row justify-between items-start">
            <div class="w-1/2 pr-8 text-sm text-gray-500">
                <h4 class="font-bold text-gray-900 mb-2">Terms & Notes</h4>
                <p>Payment is due upon receipt. Checked by: <?php echo htmlspecialchars($_SESSION['username'] ?? 'Merchant'); ?></p>
            </div>
            <div class="w-1/2 ">
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="text-gray-900 font-medium">NPR <?php echo number_format($invoice['total_amount'], 2); ?></span>
                </div>
                <?php if ($invoice['discount_amount'] > 0): ?>
                <div class="flex justify-between py-2 text-green-600">
                    <span>Discount</span>
                    <span>- <?php echo number_format($invoice['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($invoice['tax_amount'] > 0): ?>
                <div class="flex justify-between py-2">
                    <span class="text-gray-600">VAT (13%)</span>
                    <span class="text-gray-900 font-medium"><?php echo number_format($invoice['tax_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="flex justify-between py-4 border-t-2 border-gray-900 mt-2 items-center">
                    <span class="text-2xl font-bold text-gray-900">Total</span>
                    <span class="text-2xl font-bold text-blue-600">NPR <?php echo number_format($invoice['grand_total'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="mt-16 text-center text-gray-400 text-xs border-t border-gray-100 pt-8">
            <p>Thank you for choosing <?php echo htmlspecialchars($invoice['business_name']); ?>.</p>
        </div>
    </div>

    <!-- Script for PDF -->
    <script>
        function downloadPDF() {
            var element = document.getElementById('invoiceContent');
            var opt = {
                margin:       0.5,
                filename:     'Invoice_<?php echo $invoice['invoice_number']; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('download') === 'true') {
                downloadPDF();
            }
        }
    </script>

</body>
</html>
