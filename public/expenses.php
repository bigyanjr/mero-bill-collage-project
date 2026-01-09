<?php
// public/expenses.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle Add/Delete Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $description = trim($_POST['description']);
        $amount = $_POST['amount'];
        $category = $_POST['category'];
        $date = $_POST['date'];

        if (!empty($description) && !empty($amount)) {
            $stmt = $pdo->prepare("INSERT INTO expenses (merchant_id, description, amount, category, expense_date) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $description, $amount, $category, $date])) {
                $success = "Expense added.";
            } else {
                $error = "Failed to add expense.";
            }
        }
    } elseif ($action === 'edit') {
        $id = $_POST['expense_id'];
        $description = trim($_POST['description']);
        $amount = $_POST['amount'];
        $category = $_POST['category'];
        $date = $_POST['date'];

        $stmt = $pdo->prepare("UPDATE expenses SET description=?, amount=?, category=?, expense_date=? WHERE id=? AND merchant_id=?");
        if ($stmt->execute([$description, $amount, $category, $date, $id, $user_id])) {
            $success = "Expense updated.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['expense_id'];
        $stmt = $pdo->prepare("DELETE FROM expenses WHERE id = ? AND merchant_id = ?");
        $stmt->execute([$id, $user_id]);
        $success = "Expense removed.";
    }
}

// Ensure table exists (Quick schema fix for this module)
$pdo->exec("CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    merchant_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) DEFAULT 'General',
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Fetch Expenses
$stmt = $pdo->prepare("SELECT * FROM expenses WHERE merchant_id = ? ORDER BY expense_date DESC");
$stmt->execute([$user_id]);
$expenses = $stmt->fetchAll();

$title = "Expenses";
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div x-data="{ 
    showModal: false, 
    modalTitle: 'Add New Expense',
    action: 'add',
    expenseId: '',
    description: '',
    amount: '',
    category: 'General',
    date: '<?php echo date('Y-m-d'); ?>',

    // Delete Logic
    showDeleteModal: false,
    deleteId: '',
    
    openAdd() {
        this.modalTitle = 'Add New Expense';
        this.action = 'add';
        this.expenseId = '';
        this.description = '';
        this.amount = '';
        this.category = 'General';
        this.date = '<?php echo date('Y-m-d'); ?>';
        this.showModal = true;
    },
    
    openEdit(exp) {
        this.modalTitle = 'Edit Expense';
        this.action = 'edit';
        this.expenseId = exp.id;
        this.description = exp.description;
        this.amount = exp.amount;
        this.category = exp.category;
        this.date = exp.expense_date;
        this.showModal = true;
    },

    confirmDelete(id) {
        this.deleteId = id;
        this.showDeleteModal = true;
    }
}">
    <div class="animate-fade-in max-w-full">
        <div class="sm:flex sm:items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Expenses</h1>
                <p class="mt-2 text-sm text-gray-700">Track your business spending.</p>
            </div>
            <div>
                 <!-- Add Expense Button -->
                 <button @click="openAdd()" class="bg-red-600 text-white px-4 py-2 rounded shadow hover:bg-red-700 transition flex items-center">
                     <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                     Add Expense
                 </button>
            </div>
        </div>

        <!-- Notifications -->
        <?php if ($success): ?>
            <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4">
                <p class="text-sm text-green-700"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <!-- Expense Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($expenses)): ?>
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No expenses recorded.</td></tr>
                    <?php else: ?>
                        <?php foreach ($expenses as $exp): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $exp['expense_date']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($exp['description']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"><?php echo htmlspecialchars($exp['category']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 font-bold">- NPR <?php echo number_format($exp['amount'], 2); ?></td>
                             <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click='openEdit(<?php echo json_encode($exp); ?>)' class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                                <button @click="confirmDelete('<?php echo $exp['id']; ?>')" class="text-red-600 hover:text-red-900">Delete</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal (Alpine.js driven) -->
    <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                    <form method="POST">
                        <input type="hidden" name="action" :value="action">
                        <input type="hidden" name="expense_id" :value="expenseId">
                        
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="modalTitle"></h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Description</label>
                                    <input type="text" name="description" x-model="description" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount (NPR)</label>
                                        <input type="number" step="0.01" name="amount" x-model="amount" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Date</label>
                                        <input type="date" name="date" x-model="date" required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500">
                                    </div>
                                </div>
                                 <div>
                                    <label class="block text-sm font-medium text-gray-700">Category</label>
                                    <select name="category" x-model="category" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-red-500 focus:border-red-500">
                                        <option>Rent</option>
                                        <option>Utilities</option>
                                        <option>Salaries</option>
                                        <option>Inventory</option>
                                        <option>Marketing</option>
                                        <option>General</option>
                                        <option>Repairs</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:col-start-2 sm:text-sm">
                                Save Expense
                            </button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:col-start-1 sm:text-sm">
                                Cancel
                            </button>
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Expense</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this expense? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="expense_id" :value="deleteId">
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
