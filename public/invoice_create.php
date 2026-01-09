<?php
// public/invoice_create.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];

// Handle POST to save invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input since we'll likely send complex data
    // OR handle standard form post with arrays. Let's do standard POST for simplicity with PHP.
    
    // However, for dynamic rows, often JSON payload is easier.
    // Let's stick to standard POST with array names: product_id[], qty[], etc.
    
    try {
        $pdo->beginTransaction();
        
        $customer_id = empty($_POST['customer_id']) ? NULL : $_POST['customer_id'];
        $invoice_date = date('Y-m-d H:i:s');
        $status = $_POST['status'];
        
        // Generate Invoice Number (Simple Auto-increment or Random)
        $inv_num = 'INV-' . strtoupper(uniqid());
        
        // Calculate Totals
        $grand_total = 0;
        $items = [];
        
        for ($i = 0; $i < count($_POST['product_id']); $i++) {
            $pid = $_POST['product_id'][$i];
            $qty = $_POST['qty'][$i];
            
            if ($pid && $qty > 0) {
                // Fetch current price to ensure validity
                $stmt = $pdo->prepare("SELECT price, name FROM products WHERE id = ?");
                $stmt->execute([$pid]);
                $prod = $stmt->fetch();
                
                if ($prod) {
                    $price = $prod['price'];
                    $subtotal = $price * $qty;
                    $grand_total += $subtotal;
                    
                    $items[] = [
                        'product_id' => $pid,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ];
                }
            }
        }
        
        // Additional Discount/Tax Logic (Simplification: Flat discount from input if added, or 0)
        $discount = $_POST['discount'] ?? 0;
        $tax_percent = 13; // VAT
        
        $tax_amount = ($grand_total * $tax_percent) / 100;
        $final_total = ($grand_total + $tax_amount) - $discount;
        
        // Insert Invoice
        $stmt = $pdo->prepare("INSERT INTO invoices (invoice_number, merchant_id, customer_id, total_amount, tax_amount, discount_amount, grand_total, status, invoice_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$inv_num, $user_id, $customer_id, $grand_total, $tax_amount, $discount, $final_total, $status, $invoice_date]);
        $invoice_id = $pdo->lastInsertId();
        
        // Insert Items and Update Stock
        $stmt_item = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        
        foreach ($items as $item) {
            $stmt_item->execute([$invoice_id, $item['product_id'], $item['qty'], $item['price'], $item['subtotal']]);
            $stmt_stock->execute([$item['qty'], $item['product_id']]);
        }
        
        $pdo->commit();
        header("Location: invoices.php");
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error creating invoice: " . $e->getMessage();
    }
}

// Fetch Data for Dropdowns
$customers = $pdo->prepare("SELECT * FROM customers WHERE merchant_id = ?");
$customers->execute([$user_id]);
$customer_list = $customers->fetchAll();

$products = $pdo->prepare("SELECT * FROM products WHERE merchant_id = ? AND stock_quantity > 0");
$products->execute([$user_id]);
$product_list = $products->fetchAll();

// Product JSON for JS
$product_json = json_encode($product_list);

$title = "Create Invoice";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="max-w-5xl mx-auto animate-fade-in pb-12">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">New Invoice</h1>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6"><p class="text-red-700"><?php echo htmlspecialchars($error); ?></p></div>
    <?php endif; ?>

    <form method="POST" action="" id="invoiceForm">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <!-- Header Info -->
            <div class="p-6 bg-gray-50 border-b border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Customer</label>
                    <select name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white">
                        <option value="">-- Select Customer --</option>
                        <option value="">Walk-in Customer</option>
                        <?php foreach ($customer_list as $cust): ?>
                            <option value="<?php echo $cust['id']; ?>"><?php echo htmlspecialchars($cust['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="text" value="<?php echo date('Y-m-d'); ?>" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm p-2">
                </div>
                <div>
                     <label class="block text-sm font-medium text-gray-700">Status</label>
                     <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 bg-white">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                     </select>
                </div>
            </div>

            <!-- Line Items -->
            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-200" id="itemsTable">
                    <thead>
                        <tr>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">Product</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Stock</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Price</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Qty</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="itemsBody">
                        <!-- Rows will be added by JS -->
                    </tbody>
                </table>
                <div class="mt-4">
                    <button type="button" onclick="addItem()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-hover">
                        + Add Item
                    </button>
                </div>
            </div>

            <!-- Footer Totals -->
            <div class="bg-gray-50 p-6 border-t border-gray-200 flex flex-col items-end">
                <div class="w-full md:w-1/3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal:</span>
                        <span class="font-medium" id="subtotalDisplay">0.00</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">VAT (13%):</span>
                        <span class="font-medium" id="taxDisplay">0.00</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Discount:</span>
                        <input type="number" name="discount" id="discountInput" value="0" min="0" onchange="calculateTotals()" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1 text-right">
                    </div>
                    <div class="border-t border-gray-300 pt-2 flex justify-between text-lg font-bold">
                        <span>Grand Total:</span>
                        <span class="text-blue-600" id="grandTotalDisplay">0.00</span>
                    </div>
                </div>
                
                <div class="mt-6 w-full md:w-1/3">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn-hover">
                        Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const products = <?php echo $product_json; ?>;
    
    function addItem() {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        row.className = 'animate-slide-in';
        
        let options = '<option value="">Select Product...</option>';
        products.forEach(p => {
             options += `<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock_quantity}">${p.name} (Qty: ${p.stock_quantity})</option>`;
        });

        row.innerHTML = `
            <td class="py-3 pr-2">
                <select name="product_id[]" onchange="updateRow(this)" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" required>
                    ${options}
                </select>
            </td>
            <td class="py-3 px-2 text-sm text-gray-500 stock-display">-</td>
            <td class="py-3 px-2 text-sm text-gray-500 price-display">0.00</td>
            <td class="py-3 px-2">
                <input type="number" name="qty[]" value="1" min="1" onchange="updateRowTotal(this)" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2 w-20 qty-input" required>
            </td>
            <td class="py-3 px-2 text-right text-sm font-medium row-total">0.00</td>
            <td class="py-3 pl-2 text-right">
                <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">x</button>
            </td>
        `;
        tbody.appendChild(row);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotals();
    }

    function updateRow(select) {
        const row = select.closest('tr');
        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.getAttribute('data-price')) || 0;
        const stock = option.getAttribute('data-stock') || '-';

        row.querySelector('.price-display').textContent = price.toFixed(2);
        row.querySelector('.stock-display').textContent = stock;
        
        // Reset qty max based on stock if needed, for now just visual
        updateRowTotal(select);
    }

    function updateRowTotal(element) {
        const row = element.closest('tr');
        const select = row.querySelector('select');
        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.getAttribute('data-price')) || 0;
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        
        const total = price * qty;
        row.querySelector('.row-total').textContent = total.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.row-total').forEach(el => {
            subtotal += parseFloat(el.textContent);
        });

        const taxRate = 0.13;
        const tax = subtotal * taxRate;
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const grandTotal = (subtotal + tax) - discount;

        document.getElementById('subtotalDisplay').textContent = subtotal.toFixed(2);
        document.getElementById('taxDisplay').textContent = tax.toFixed(2);
        document.getElementById('grandTotalDisplay').textContent = grandTotal.toFixed(2);
    }

    // Add one empty row on load
    window.onload = function() {
        addItem();
    };
</script>

<?php include '../includes/footer.php'; ?>
