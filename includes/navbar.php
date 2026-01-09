<?php
// includes/navbar.php (NOW SIDEBAR LAYOUT WRAPPER)
// This file is included at the top of every page. 
// We will use it to START the layout: <div flex><sidebar><main><header>

$current_page = basename($_SERVER['PHP_SELF']);
$user_role = $_SESSION['role'] ?? 'guest';
$user_name = $_SESSION['username'] ?? 'User';

// Determine Theme Color based on Role
$theme_color = 'bg-blue-600';
$text_theme = 'text-blue-600';
$hover_theme = 'hover:bg-blue-700'; // Not used in dynamic classes usually but good for ref
$logo_text = 'text-blue-500';

if ($user_role === 'admin') {
    $theme_color = 'bg-red-600';
    $text_theme = 'text-red-600';
    $logo_text = 'text-red-500';
}
?>

<!-- Main App Wrapper -->
<div class="flex h-screen bg-gray-100 overflow-hidden" x-data="{ sidebarOpen: false, showLogoutModal: false }" x-init="sidebarOpen = false" @resize.window="if (window.innerWidth > 1024) sidebarOpen = false">

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen" style="display: none;" x-cloak @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-20 bg-black bg-opacity-50 !lg:hidden"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition-duration-300 transform bg-gray-900 text-white lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out">
        
        <!-- Logo -->
        <div class="flex items-center justify-center mt-8">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-white">Mero<span class="<?php echo $logo_text; ?>">Bill</span></span>
                <?php if ($user_role === 'demo'): ?>
                    <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-yellow-500 text-yellow-900 rounded-full">DEMO</span>
                <?php elseif ($user_role === 'admin'): ?>
                     <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-red-500 text-white rounded-full">ADMIN</span>
                     <?php elseif ($user_role ==='signup'): ?>
                     <span class="ml-2 px-2 py-0.5 text-xs font-semibold bg-green-500 text-white rounded-full">SIGNUP</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="mt-10 px-4 space-y-2">
            
            <?php if ($user_role === 'merchant' || $user_role === 'demo'): ?>
                <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'dashboard.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="mx-3 font-medium">Dashboard</span>
                </a>

                <a href="invoice_create.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'invoice_create.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span class="mx-3 font-medium">New Invoice</span>
                </a>

                <a href="invoices.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'invoices.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="mx-3 font-medium">Invoices</span>
                </a>

                <a href="products.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'products.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span class="mx-3 font-medium">Products</span>
                </a>

                <a href="customers.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'customers.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="mx-3 font-medium">Customers</span>
                </a>

                <a href="reports.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'reports.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="mx-3 font-medium">Reports</span>
                </a>

                <a href="expenses.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'expenses.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                     <span class="mx-3 font-medium">Expenses</span>
                </a>
                
                <a href="settings.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'settings.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="mx-3 font-medium">Settings</span>
                </a>

            <?php elseif ($user_role === 'admin'): ?>
                <a href="../admin/admin_dashboard.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin_dashboard.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="mx-3 font-medium">Admin Dashboard</span>
                </a>
                <a href="../admin/admin_users.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?php echo $current_page == 'admin_users.php' ? $theme_color . ' text-white shadow-lg' : 'text-gray-400 hover:bg-gray-800 hover:text-white'; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="mx-3 font-medium">Users</span>
                </a>
            <?php endif; ?>

            <button @click="showLogoutModal = true" class="flex w-full items-center px-4 py-3 text-red-400 hover:bg-gray-800 hover:text-red-200 rounded-lg transition-colors duration-200 mt-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="mx-3 font-medium">Logout</span>
            </button>
        </nav>
    </aside>

    <!-- Logout Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showLogoutModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showLogoutModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showLogoutModal = false" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showLogoutModal" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Logout Confirmation</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Are you sure you want to logout? You will be returned to the login screen.</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <a href="../includes/logout_handler.php" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Yes, Logout
                        </a>
                        <button type="button" @click="showLogoutModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Content Area (Header + Main) -->
    <div class="flex-1 flex flex-col overflow-hidden">
        
        <!-- Header / Top Bar -->
        <header class="flex justify-between items-center py-4 px-6 bg-white shadow-sm bottom-border">
            <div class="flex items-center">
                <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 6H20M4 12H20M4 18H11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="flex items-center">
                <!-- User Profile Dropdown (Simplified) -->
                <div class="relative flex items-center gap-2">
                    <span class="text-gray-700 text-sm font-medium mr-2 max-w-[150px] truncate hidden sm:block">Hello, <?php echo htmlspecialchars($user_name); ?></span>
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold border border-blue-200">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content (Scrollable) -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 px-8 py-6 relative">
            <!-- Content Injected Here by Page Files -->
