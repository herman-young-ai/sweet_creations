<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Set JSON content type
header('Content-Type: application/json');

// Check if customer ID is provided
if (!isset($_GET['customer_id']) || !is_numeric($_GET['customer_id'])) {
    echo json_encode(['error' => 'Invalid customer ID']);
    exit;
}

$customerId = (int)$_GET['customer_id'];

// Get customer data
$customer = getCustomerById($customerId);

if ($customer) {
    echo json_encode([
        'success' => true,
        'phone_number' => $customer['phone_number'],
        'email' => $customer['email'],
        'address' => $customer['address']
    ]);
} else {
    echo json_encode(['error' => 'Customer not found']);
}
?> 