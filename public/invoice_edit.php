<?php
// public/invoice_edit.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$invoice_id = $_GET['id'] ?? null;

if (!$invoice_id) {
    die("Invoice ID required.");
}

// Fetch Existing Invoice
$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ? AND merchant_id = ?");
$stmt->execute([$invoice_id, $user_id]);
$invoice = $stmt->fetch();

if (!$invoice) {
    die("Invoice not found or access denied.");
}

// Fetch Existing Items
$stmt_items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$stmt_items->execute([$invoice_id]);
$existing_items = $stmt_items->fetchAll();


// Handle UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        $customer_id = empty($_POST['customer_id']) ? NULL : $_POST['customer_id'];
        $status = $_POST['status'];
        
        // 1. Restore Stock for OLD items (simplest way to handle stock updates is revert then apply new)
        // Note: This logic assumes we want to strictly track stock. 
        foreach ($existing_items as $item) {
             $stmt_restore = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?");
             $stmt_restore->execute([$item['quantity'], $item['product_id']]);
        }
        
        // 2. Delete OLD Items
        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$invoice_id]);
        
        // 3. Process NEW Items
        $grand_total = 0;
        $items_to_insert = [];
        
        for ($i = 0; $i < count($_POST['product_id']); $i++) {
            $pid = $_POST['product_id'][$i];
            $qty = $_POST['qty'][$i];
            
            if ($pid && $qty > 0) {
                $stmt_prod = $pdo->prepare("SELECT price FROM products WHERE id = ?");
                $stmt_prod->execute([$pid]);
                $prod = $stmt_prod->fetch();
                
                if ($prod) {
                    $price = $prod['price'];
                    $subtotal = $price * $qty;
                    $grand_total += $subtotal;
                    
                    $items_to_insert[] = [
                        'pid' => $pid,
                        'qty' => $qty,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ];
                }
            }
        }
        
        $discount = $_POST['discount'] ?? 0;
        $tax_percent = 13;
        $tax_amount = ($grand_total * $tax_percent) / 100;
        $final_total = ($grand_total + $tax_amount) - $discount;
        
        // 4. Update Invoice Record
        $stmt_update = $pdo->prepare("UPDATE invoices SET customer_id=?, total_amount=?, tax_amount=?, discount_amount=?, grand_total=?, status=? WHERE id=?");
        $stmt_update->execute([$customer_id, $grand_total, $tax_amount, $discount, $final_total, $status, $invoice_id]);
        
        // 5. Insert NEW Items and Deduct Stock
        $stmt_insert = $pdo->prepare("INSERT INTO invoice_items (invoice_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_deduct = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        
        foreach ($items_to_insert as $item) {
            $stmt_insert->execute([$invoice_id, $item['pid'], $item['qty'], $item['price'], $item['subtotal']]);
            $stmt_deduct->execute([$item['qty'], $item['pid']]);
        }
        
        $pdo->commit();
        header("Location: invoice_view.php?id=" . $invoice_id);
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating invoice: " . $e->getMessage();
    }
}

// Fetch Dropdown Data
$customers = $pdo->prepare("SELECT * FROM customers WHERE merchant_id = ?");
$customers->execute([$user_id]);
$customer_list = $customers->fetchAll();

$products = $pdo->prepare("SELECT * FROM products WHERE merchant_id = ?");
$products->execute([$user_id]);
$product_list = $products->fetchAll();
$product_json = json_encode($product_list);
$existing_items_json = json_encode($existing_items);

$title = "Edit Invoice";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="max-w-5xl mx-auto animate-fade-in pb-12">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Invoice #<?php echo htmlspecialchars($invoice['invoice_number']); ?></h1>
        <a href="invoice_view.php?id=<?php echo $invoice_id; ?>" class="text-gray-600 hover:text-gray-900">Cancel</a>
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
                    <select name="customer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 bg-white">
                        <option value="">Walk-in Customer</option>
                        <?php foreach ($customer_list as $cust): ?>
                            <option value="<?php echo $cust['id']; ?>" <?php echo $invoice['customer_id'] == $cust['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cust['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                     <label class="block text-sm font-medium text-gray-700">Status</label>
                     <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 bg-white">
                        <option value="unpaid" <?php echo $invoice['status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="paid" <?php echo $invoice['status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="partial" <?php echo $invoice['status'] == 'partial' ? 'selected' : ''; ?>>Partial</option>
                     </select>
                </div>
            </div>

            <!-- items -->
            <div class="p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/2">Product</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Price</th>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Qty</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Total</th>
                            <th class="w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200" id="itemsBody"></tbody>
                </table>
                <div class="mt-4">
                    <button type="button" onclick="addItem()" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        + Add Item
                    </button>
                </div>
            </div>

            <!-- Footer -->
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
                        <input type="number" name="discount" id="discountInput" value="<?php echo $invoice['discount_amount']; ?>" min="0" onchange="calculateTotals()" class="w-24 rounded-md border-gray-300 shadow-sm p-1 text-right">
                    </div>
                    <div class="border-t border-gray-300 pt-2 flex justify-between text-lg font-bold">
                        <span>Grand Total:</span>
                        <span class="text-blue-600" id="grandTotalDisplay">0.00</span>
                    </div>
                </div>
                <div class="mt-6 w-full md:w-1/3">
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Update Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    const products = <?php echo $product_json; ?>;
    const existingItems = <?php echo $existing_items_json; ?>;
    
    function addItem(pid = '', qty = 1) {
        const tbody = document.getElementById('itemsBody');
        const row = document.createElement('tr');
        
        let options = '<option value="">Select Product...</option>';
        products.forEach(p => {
             const selected = p.id == pid ? 'selected' : '';
             options += `<option value="${p.id}" data-price="${p.price}" ${selected}>${p.name}</option>`;
        });

        row.innerHTML = `
            <td class="py-3 pr-2">
                <select name="product_id[]" onchange="updateRow(this)" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2" required>
                    ${options}
                </select>
            </td>
            <td class="py-3 px-2 text-sm text-gray-500 price-display">0.00</td>
            <td class="py-3 px-2">
                <input type="number" name="qty[]" value="${qty}" min="1" onchange="updateRowTotal(this)" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 w-20 qty-input" required>
            </td>
            <td class="py-3 px-2 text-right text-sm font-medium row-total">0.00</td>
            <td class="py-3 pl-2 text-right">
                <button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">x</button>
            </td>
        `;
        tbody.appendChild(row);
        
        // Initialize values
        const select = row.querySelector('select');
        updateRow(select);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateTotals();
    }

    function updateRow(select) {
        const row = select.closest('tr');
        const option = select.options[select.selectedIndex];
        const price = parseFloat(option.getAttribute('data-price')) || 0;
        
        row.querySelector('.price-display').textContent = price.toFixed(2);
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

    // Load existing items
    window.onload = function() {
        if (existingItems.length > 0) {
            existingItems.forEach(item => {
                addItem(item.product_id, item.quantity);
            });
        } else {
            addItem();
        }
    };
</script>
<?php include '../includes/footer.php'; ?>
