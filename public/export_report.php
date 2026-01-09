<?php
// public/export_report.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_login();
require_role(['merchant', 'demo']);

$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'csv';

if ($type === 'csv') {
    $filename = "sales_report_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Header Row
    fputcsv($output, ['Invoice Number', 'Date', 'Customer', 'Items', 'Total Amount (NPR)', 'Status']);
    
    // Data Rows
    $sql = "SELECT i.*, c.name as customer_name, 
            (SELECT GROUP_CONCAT(p.name SEPARATOR ', ') 
             FROM invoice_items ii 
             JOIN products p ON ii.product_id = p.id 
             WHERE ii.invoice_id = i.id) as items
            FROM invoices i 
            LEFT JOIN customers c ON i.customer_id = c.id 
            WHERE i.merchant_id = ? 
            ORDER BY i.created_at DESC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $invoices = $stmt->fetchAll();
    
    foreach ($invoices as $inv) {
        fputcsv($output, [
            $inv['invoice_number'],
            $inv['invoice_date'],
            $inv['customer_name'] ?? 'Walk-in',
            $inv['items'],
            $inv['grand_total'],
            ucfirst($inv['status'])
        ]);
    }
    
    fclose($output);
    exit;
}
?>
