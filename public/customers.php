<?php
// public/customers.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Form Submission (Add/Edit)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $type = $_POST['type'] ?? '';
    
    if ($action === 'add') {
        // Check if phone number already exists for this merchant
        $stmt_check = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND merchant_id = ?");
        $stmt_check->execute([$phone, $user_id]);
        if ($stmt_check->fetch()) {
            $error = "Phone number already exists. Please use a different phone number.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO customers (merchant_id, name, phone, type) VALUES (?, ?, ?, ?)");
            if($stmt->execute([$user_id, $name, $phone, $type])) {
                $success = "Customer added successfully!";
            }
        }
    } elseif ($action === 'edit') {
        $cust_id = $_POST['customer_id'];
        // Check if phone number already exists for another customer of this merchant
        $stmt_check = $pdo->prepare("SELECT id FROM customers WHERE phone = ? AND merchant_id = ? AND id != ?");
        $stmt_check->execute([$phone, $user_id, $cust_id]);
        if ($stmt_check->fetch()) {
            $error = "Phone number already exists. Please use a different phone number.";
        } else {
            $stmt = $pdo->prepare("UPDATE customers SET name=?, phone=?, type=? WHERE id=? AND merchant_id=?");
            if($stmt->execute([$name, $phone, $type, $cust_id, $user_id])) {
                $success = "Customer updated successfully!";
            }
        }
    } elseif ($action === 'delete') {
         $cust_id = $_POST['customer_id'];
         $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ? AND merchant_id = ?");
         if($stmt->execute([$cust_id, $user_id])) {
             $success = "Customer deleted.";
         }
    }
}

// Fetch Customers
$stmt = $pdo->prepare("SELECT * FROM customers WHERE merchant_id = ? ORDER BY id DESC");
$stmt->execute([$user_id]);
$customers = $stmt->fetchAll();

$title = "Customers";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div x-data="{ 
    showModal: false, 
    modalTitle: 'Add New Customer',
    action: 'add',
    customerId: '',
    phone: '',
    name: '',
    type: 'normal',

    // Delete Logic
    showDeleteModal: false,
    deleteId: '',
    
    openAdd() {
        this.modalTitle = 'Add New Customer';
        this.action = 'add';
        this.customerId = '';
        this.phone = '';
        this.name = '';
        this.type = 'normal';
        this.showModal = true;
    },
    
    openEdit(cust) {
        this.modalTitle = 'Edit Customer';
        this.action = 'edit';
        this.customerId = cust.id;
        this.name = cust.name;
        this.phone = cust.phone;
        this.type = cust.type;
        this.showModal = true;
    },

    confirmDelete(id) {
        this.deleteId = id;
        this.showDeleteModal = true;
    }
}">
    <div class="animate-fade-in">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-2xl font-semibold text-gray-900">Customers</h1>
                <p class="mt-2 text-sm text-gray-700">Manage your diverse customer base (Mart vs Normal).</p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <button @click="openAdd()" type="button" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto btn-hover">
                    Add Customer
                </button>
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

        <!-- Customer Table -->
        <div class="mt-8 flex flex-col">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Phone</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <?php if (empty($customers)): ?>
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-gray-500">No customers found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($customers as $cust): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"><?php echo htmlspecialchars($cust['phone']); ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($cust['name']); ?></td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $cust['type'] == 'mart' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                <?php echo ucfirst($cust['type']); ?>
                                            </span>
                                        </td>
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                            <button @click='openEdit(<?php echo json_encode($cust); ?>)' class="text-blue-600 hover:text-blue-900 mr-2">Edit</button>
                                            <button @click="confirmDelete('<?php echo $cust['id']; ?>')" class="text-red-600 hover:text-red-900">Delete</button>
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

    <!-- Add/Edit Customer Modal -->
    <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <form method="POST">
                        <input type="hidden" name="action" :value="action">
                        <input type="hidden" name="customer_id" :value="customerId">
                        <div>
                            <h3 class="text-lg font-medium leading-6 text-gray-900" x-text="modalTitle"></h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="text" name="phone" x-model="phone" required class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                                    <input type="text" name="name" x-model="name" required class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Type</label>
                                    <select name="type" x-model="type" class="mt-1 block w-full rounded-md border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2">
                                        <option value="normal">Normal</option>
                                        <option value="mart">Mart (Wholesale)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none sm:col-start-2 sm:text-sm">Save Customer</button>
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Customer</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this customer? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="customer_id" :value="deleteId">
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
