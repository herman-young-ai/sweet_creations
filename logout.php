<?php
// Start the session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define BASE_URL if not already defined (needed for redirect)
// Avoids error if config isn't included elsewhere before logout
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Attempt to determine base path more reliably
    $docRoot = $_SERVER['DOCUMENT_ROOT'];
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']); // Get directory part of the script path
    $basePath = rtrim($scriptDir, '/'); // Remove trailing slash if any
    // If script is in root, basePath might be empty or just '/', handle project name case
    if (empty($basePath) || $basePath == '/') {
         $projectName = 'sweet_creations'; 
         // Check if project name is the first segment after host
         if (strpos($_SERVER['REQUEST_URI'], '/' . $projectName . '/') === 0) {
             $basePath = '/' . $projectName;
         } else {
             $basePath = ''; // Assume project is in web root if not found
         }
    }
   
    define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: " . BASE_URL . "login.php");
exit;
?> 