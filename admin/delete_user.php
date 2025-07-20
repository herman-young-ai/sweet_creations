<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Check if user is admin - only admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../index.php?error=access_denied');
    exit;
}

// Get user ID from URL
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($userId <= 0) {
    header('Location: manage_users.php?status=not_found');
    exit;
}

// Check if user exists
$user = getUserById($userId);
if (!$user) {
    header('Location: manage_users.php?status=not_found');
    exit;
}

// Safety checks
// 1. Cannot delete yourself
if ($userId == $_SESSION['user_id']) {
    header('Location: manage_users.php?status=cannot_delete_self');
    exit;
}

// 2. Cannot delete admin accounts (additional safety)
if ($user['role'] === 'Admin') {
    header('Location: manage_users.php?status=cannot_delete_admin');
    exit;
}

// Attempt to delete the user
$success = deleteUser($userId);

if ($success) {
    // Success! Redirect with success message
    header('Location: manage_users.php?status=deleted');
} else {
    // Error occurred
    header('Location: manage_users.php?status=error');
}
exit;
?> 