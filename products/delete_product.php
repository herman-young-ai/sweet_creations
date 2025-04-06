<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$status = 'error'; // Default status

if ($productId > 0) {
    // Attempt to delete the product
    $deleteResult = deleteProduct($productId);
    
    if ($deleteResult === 'deleted') {
        $status = 'deleted';
    } elseif ($deleteResult === 'in_use') {
        $status = 'in_use';
    } else {
        $status = 'error';
    }
} else {
    $status = 'invalid_id';
}

// Redirect back to the product list page with a status message
header('Location: products.php?status=' . $status);
exit;

?> 