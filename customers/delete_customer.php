<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Get customer ID from URL
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$status = 'error'; // Default status

if ($customerId > 0) {
    // Attempt to delete the customer
    $deleteResult = deleteCustomer($customerId);
    
    if ($deleteResult === 'deleted') {
        $status = 'deleted';
    } elseif ($deleteResult === 'has_orders') {
        $status = 'has_orders';
    } else {
        $status = 'error';
    }
} else {
    $status = 'invalid_id';
}

// Redirect back to the customer list page with a status message
header('Location: customers.php?status=' . $status);
exit;

?> 