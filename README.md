# Mero Bill - Cloud-Based Billing & Invoicing System

## Introduction
**Mero Bill** is a cloud-based Point of Sale (POS) and billing system designed tailored for the Nepalese market. It simplifies business operations for marts, shops, and small businesses by providing tools for invoicing, inventory management, customer tracking, and reporting. The system supports multiple user roles, including limited demo users and full-access paid merchants, all managed via a central admin panel.

## System Overview
The system interacts with the following actors:
- **Demo User**: A limited access user (limited products, invoices, and validity) evaluating the platform.
- **Paid User (Merchant)**: A full access user with complete control over inventory, sales, and reporting.
- **Admin**: The system owner who manages users, subscription plans, and monitors platform usage.
- **System**: The backend logic handling data processing, authentication, and automation.

## High-Level Features (Use Cases)

| ID | Feature | Description |
| :--- | :--- | :--- |
| **UC-01** | **Register Demo Account** | Users can sign up for a limited-time demo account with restricted quotas. |
| **UC-02** | **Login** | Secure role-based login for Demo, Paid, and Admin users. |
| **UC-03** | **Upgrade to Paid Account** | Demo users can upgrade to a paid subscription (payment simulation). |
| **UC-04** | **Product Management** | Add, edit, delete products with stock tracking, tax, and wholesale prices. |
| **UC-05** | **Customer Management** | Manage normal customers and wholesale marts/shops. |
| **UC-06** | **Create Invoice** | Generate invoices with auto-calculated totals, taxes, and discounts. |
| **UC-07** | **Payment Handling** | Track invoice payments (Paid, Partial, Unpaid). |
| **UC-08** | **Sell to Marts** | Special wholesale pricing and bulk quantity handling for B2B sales. |
| **UC-09** | **Reports & History** | View daily/monthly sales, stock levels, and basic analytics. |
| **UC-10** | **Admin Management** | Admin dashboard to manage users, plans, and payments. |
| **UC-11** | **Security & Access Control** | Role-based access control (RBAC) to protect sensitive data. |

## Tech Stack
- **Backend**: PHP 8+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla with optional Tailwind/Bootstrap)
- **Database**: MySQL
- **Server**: XAMPP (Apache)
- **IDE**: Antigravity IDE

## System Architecture
The application follows a standard tiered architecture:
- **Presentation Layer**: HTML/CSS/JS files serving the UI, including dashboards for different roles.
- **Business Logic Layer**: PHP scripts handling requests (`/includes/auth.php`, `helpers.php`) and page controllers (`dashboard.php`, `invoices.php`).
- **Data Access Layer**: `db.php` connecting to the MySQL database.
- **External Services**: API proxy for connecting to an external Chatbot API.

## Setup Instructions

### 1. Prerequisite
Ensure **XAMPP** is installed and running (Apache and MySQL modules started).

### 2. Database Creation
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
2. Create a new database named `merobill_db`.
3. Import the schema provided in the **Database Schema** section below (or run the SQL commands).

### 3. Database Configuration
Edit `/includes/db.php` (once created) with your credentials:
```php
<?php
$host = 'localhost';
$db   = 'merobill_db';
$user = 'root';
$pass = ''; // Default XAMPP password is empty
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
```

### 4. UI/UX Notes
- **Responsiveness**: Ensure the app works on desktop and tablets.
- **Localization**: Use **NPR** (Rs.) as the currency symbol.
- **Tone**: Professional, encouraging, and clear for Nepalese business owners.
- **Interactions**: Use simple animations (toast notifications, modal fades) for a polished feel.

## Folder Structure
```
/pos
│
├── /public
│   ├── index.php             # Landing page
│   ├── login.php             # Universal login
│   ├── register_demo.php     # Demo registration
│   ├── dashboard.php         # Main User Dashboard (Demo/Paid)
│   ├── products.php          # Product Management
│   ├── customers.php         # Customer Management
│   ├── invoices.php          # Invoice Creation & List
│   ├── invoice_view.php      # Single Invoice View/Print
│   ├── payments.php          # Payment Tracking
│   ├── reports.php           # Sales & Stock Reports
│   └── chatbot.php           # Help System
│
├── /admin
│   ├── admin_dashboard.php   # Admin Overview
│   ├── admin_users.php       # User Management
│   ├── admin_plans.php       # Subscription Plans
│   └── admin_payments.php    # Payment Records
│
├── /includes
│   ├── db.php                # Database Connection
│   ├── auth.php              # Auth Middleware & Login Logic
│   ├── header.php            # HTML Header & Meta Tags
│   ├── footer.php            # HTML Footer & Scripts
│   ├── navbar.php            # Navigation Bar (Dynamic by Role)
│   ├── helpers.php           # Utility Functions (Formatting, Etc.)
│   └── security.php          # Security headers & Validation
│
├── /api
│   └── chatbot_api_proxy.php # Proxy for Chatbot API calls
│
└── /assets
    ├── /css
    │   └── styles.css        # Main Stylesheet
    ├── /js
    │   ├── app.js            # Main Application Logic
    │   └── chatbot.js        # Chatbot Frontend Logic
    └── /img                  # Images & Logotypes
```

## Database Schema (MySQL)

### 1. `plans`
Stores subscription details (Demo, Paid/Basic, Paid/Pro).
- `id` (INT, PK, Auto Increment)
- `name` (VARCHAR 50) - e.g., 'Demo', 'Gold'
- `price` (DECIMAL 10,2) - Cost in NPR
- `duration_days` (INT) - Validity in days
- `max_users` (INT)
- `max_products` (INT)
- `max_invoices` (INT)

### 2. `users`
System users including Admins, Merchants, and Demo users.
- `id` (INT, PK, Auto Increment)
- `username` (VARCHAR 50, Unique)
- `email` (VARCHAR 100, Unique)
- `password_hash` (VARCHAR 255)
- `role` (ENUM: 'admin', 'merchant', 'demo') - User's role and access level
- `plan_id` (INT, FK -> plans.id) - NULL for Admin
- `plan_expires_at` (DATETIME)
- `business_name` (VARCHAR 100)
- `phone` (VARCHAR 20)
- `address` (TEXT)
- `created_at` (DATETIME)

### 3. `user_plans` (History)
Tracks plan upgrades and history for users.
- `id` (INT, PK, Auto Increment)
- `user_id` (INT, FK -> users.id)
- `plan_id` (INT, FK -> plans.id)
- `start_date` (DATETIME)
- `end_date` (DATETIME)
- `payment_status` (ENUM: 'pending', 'completed')
- `amount_paid` (DECIMAL 10,2)

### 4. `customers`
Customers of the merchants.
- `id` (INT, PK, Auto Increment)
- `merchant_id` (INT, FK -> users.id)
- `name` (VARCHAR 100)
- `phone` (VARCHAR 20)
- `type` (ENUM: 'normal', 'mart') - differentiate retail vs wholesale
- `pan_vat` (VARCHAR 20) - For Marts/Businesses
- `address` (TEXT)
- `created_at` (DATETIME)

### 5. `products`
Inventory items managed by merchants.
- `id` (INT, PK, Auto Increment)
- `merchant_id` (INT, FK -> users.id)
- `name` (VARCHAR 100)
- `sku` (VARCHAR 50) - Barcode/Stock ID
- `price` (DECIMAL 10,2) - Retail Price
- `wholesale_price` (DECIMAL 10,2)
- `stock_quantity` (INT)
- `unit` (VARCHAR 20) - e.g., pcs, kg, box
- `created_at` (DATETIME)

### 6. `invoices`
Sales records.
- `id` (INT, PK, Auto Increment)
- `invoice_number` (VARCHAR 50) - e.g., INV-001
- `merchant_id` (INT, FK -> users.id)
- `customer_id` (INT, FK -> customers.id, Nullable)
- `total_amount` (DECIMAL 10,2)
- `tax_amount` (DECIMAL 10,2)
- `discount_amount` (DECIMAL 10,2)
- `grand_total` (DECIMAL 10,2)
- `status` (ENUM: 'paid', 'partial', 'unpaid')
- `invoice_date` (DATETIME)

### 7. `invoice_items`
Individual items within an invoice.
- `id` (INT, PK, Auto Increment)
- `invoice_id` (INT, FK -> invoices.id)
- `product_id` (INT, FK -> products.id)
- `quantity` (INT)
- `price` (DECIMAL 10,2) - Snapshot of price at sale
- `subtotal` (DECIMAL 10,2)

### 8. `payments`
Tracks payments received for invoices.
- `id` (INT, PK, Auto Increment)
- `invoice_id` (INT, FK -> invoices.id)
- `amount` (DECIMAL 10,2)
- `payment_method` (ENUM: 'cash', 'esewa', 'bank', 'credit')
- `payment_date` (DATETIME)

### 9. `audit_logs` (Optional/Advanced)
System usage tracking for security.
- `id` (INT, PK, Auto Increment)
- `user_id` (INT, FK -> users.id)
- `action` (VARCHAR 255)
- `ip_address` (VARCHAR 45)
- `timestamp` (DATETIME)
# mero-bill-collage-project
