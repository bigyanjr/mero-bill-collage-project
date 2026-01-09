<?php
// public/products.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Form Submission (Add/Edit/Delete)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Common fields
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    
    if ($action === 'add' || $action === 'edit') {
        if ($action === 'add') {
             // Check Demo validation
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE merchant_id = ?");
            $stmt->execute([$user_id]);
            $current_count = $stmt->fetch()['count'];
             
            if ($_SESSION['role'] === 'demo' && $current_count >= 50) {
                 $error = "Demo Limit Reached."; 
            } else {
                $stmt = $pdo->prepare("INSERT INTO products (merchant_id, name, sku, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
                if($stmt->execute([$user_id, $name, $sku, $price, $stock])) {
                    $success = "Product added successfully!";
                }
            }
        } elseif ($action === 'edit') {
            $product_id = $_POST['product_id'];
            // Verify ownership
            $stmt = $pdo->prepare("UPDATE products SET name=?, sku=?, price=?, stock_quantity=? WHERE id=? AND merchant_id=?");
            if($stmt->execute([$name, $sku, $price, $stock, $product_id, $user_id])) {
                $success = "Product updated successfully!";
            }
        }
    } elseif ($action === 'delete') {
         $prod_id = $_POST['product_id'];
         $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND merchant_id = ?");
         if($stmt->execute([$prod_id, $user_id])) {
             $success = "Product deleted.";
         }
    }
}

// Fetch Products
$products = $pdo->prepare("SELECT * FROM products WHERE merchant_id = ? ORDER BY id DESC");
$products->execute([$user_id]);
$product_list = $products->fetchAll();

// Make sure we have a clean array for JS
$products_json = json_encode($product_list);

$title = "Products";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div x-data="{ 
    showModal: false, 
    modalTitle: 'Add New Product',
    action: 'add',
    productId: '',
    name: '',
    sku: '',
    price: '',
    stock: '',
    
    openAdd() {
        this.modalTitle = 'Add New Product';
        this.action = 'add';
        this.productId = '';
        this.name = '';
        this.sku = 'SKU-' + Math.random().toString(36).substr(2, 6).toUpperCase();
        this.price = '';
        this.stock = '';
        this.showModal = true;
    },
    
    openEdit(prod) {
        this.modalTitle = 'Edit Product';
        this.action = 'edit';
        this.productId = prod.id;
        this.name = prod.name;
        this.sku = prod.sku;
        this.price = prod.price;
        this.stock = prod.stock_quantity;
        this.showModal = true;
    },

    // Delete Logic
    showDeleteModal: false,
    deleteId: '',
    
    confirmDelete(id) {
        this.deleteId = id;
        this.showDeleteModal = true;
    }
}"><div class="animate-fade-in">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">Products</h1>
                <p class="mt-2 text-sm text-gray-700">A list of all products in your inventory.</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button @click="openAdd()" type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto btn-hover">
                    Add Product
                </button>
            </div>
        </div>

        <!-- Notifications -->
        <?php if ($error): ?>
            <div class="mt-4 bg-red-50 border-l-4 border-red-500 p-4">
                <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mt-4 bg-green-50 border-l-4 border-green-500 p-4">
                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Product Table -->
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">SKU</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Price</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Stock</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php if (empty($product_list)): ?>
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-gray-500">No products found. Click "Add Product" to start.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($product_list as $prod): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"><?php echo htmlspecialchars($prod['name']); ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($prod['sku']); ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">NPR <?php echo number_format($prod['price'], 2); ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="<?php echo $prod['stock_quantity'] < 5 ? 'text-red-600 font-bold' : ''; ?>">
                                                <?php echo $prod['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <!-- Edit Button -->
                                            <button @click='openEdit(<?php echo json_encode($prod); ?>)' class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                                            
                                            <!-- Delete Button -->
                                            <button @click="confirmDelete('<?php echo $prod['id']; ?>')" class="text-red-600 hover:text-red-900">Delete</button>
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

    <!-- Modal (Alpine.js driven) -->
    <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <form method="POST">
                        <input type="hidden" name="action" :value="action">
                        <input type="hidden" name="product_id" :value="productId">
                        
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900" x-text="modalTitle"></h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product Name</label>
                                    <input type="text" name="name" x-model="name" required class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                     <div>
                                        <label class="block text-sm font-medium text-gray-700">SKU / Code</label>
                                        <input type="text" name="sku" x-model="sku" class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Stock Qty</label>
                                        <input type="number" name="stock" x-model="stock" required class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (NPR)</label>
                                    <input type="number" step="0.01" name="price" x-model="price" required class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none sm:col-start-2 sm:text-sm">Save Product</button>
                            <button @click="showModal = false" type="button" class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none sm:col-start-1 sm:mt-0 sm:text-sm">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


<!-- Delete Confirmation Modal -->
<div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="showDeleteModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteModal = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="showDeleteModal" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Product</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">Are you sure you want to delete this product? This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" :value="deleteId">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                </form>
                <button type="button" @click="showDeleteModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<?php include '../includes/footer.php'; ?>
