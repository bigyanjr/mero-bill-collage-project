<?php
// public/invoices.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

// Initial query
$sql = "SELECT i.*, c.name as customer_name 
        FROM invoices i 
        LEFT JOIN customers c ON i.customer_id = c.id 
        WHERE i.merchant_id = ? 
        ORDER BY i.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$invoices = $stmt->fetchAll();

$title = "Invoices";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div>
    <div class="animate-fade-in">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-gray-900">Invoices</h1>
            <p class="mt-2 text-sm text-gray-700">History of all generated invoices.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="invoice_create.php" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto btn-hover">
                Create Invoice
            </a>
        </div>
    </div>

    <!-- Notifications -->
    <?php if ($success): ?>
        <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4">
            <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Invoice #</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                             <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-gray-500">No invoices generated yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-blue-600 sm:pl-6">
                                        <a href="invoice_view.php?id=<?php echo $inv['id']; ?>"><?php echo htmlspecialchars($inv['invoice_number']); ?></a>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($inv['invoice_date'])); ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($inv['customer_name'] ?? 'Walk-in Customer'); ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">NPR <?php echo number_format($inv['grand_total'], 2); ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $inv['status'] == 'paid' ? 'bg-green-100 text-green-800' : ($inv['status'] == 'partial' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <a href="javascript:void(0)" onclick="triggerDownload(<?php echo $inv['id']; ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">Download</a>
                                        <a href="invoice_view.php?id=<?php echo $inv['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Hidden iframe for download -->
    <iframe id="downloadFrame" style="position:fixed; top:-9999px; left:-9999px; width:1000px; height:1000px; visibility:hidden;"></iframe>

    <script>
        function triggerDownload(invoiceId) {
            const frame = document.getElementById('downloadFrame');
            frame.src = `invoice_view.php?id=${invoiceId}&download=true`;
        }
    </script>
</div>

<?php include '../includes/footer.php'; ?>
