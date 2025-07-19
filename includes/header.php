<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Base URL - adjust if your project is not in the web root's subdirectory named 'sweet_creations'
// It's generally better to calculate this dynamically if possible, but define() is simple.
if (!defined('BASE_URL')) {
    // Assumes the project is in http://localhost/sweet_creations/
    // Adjust the path if necessary
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    // Try to determine the base path automatically
    $scriptName = $_SERVER['SCRIPT_NAME']; // e.g., /sweet_creations/index.php
    // Find the assumed project directory name
    $projectName = 'sweet_creations'; // Hardcoded for simplicity, matches expected setup
    $basePath = substr($scriptName, 0, strpos($scriptName, $projectName) + strlen($projectName));
    if(empty($basePath) || strpos($scriptName, $projectName) === false){
         // Fallback if auto-detection fails or project name isn't in path
        $basePath = '/' . $projectName; 
    }
    define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | Sweet Creations' : 'Sweet Creations'; ?></title>

    <!-- Bootstrap -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Assuming it's still needed) -->
    <!-- NOTE: Font Awesome was inside gentelella/vendors, need to move it or use CDN -->
    <!-- <link href="<?php echo BASE_URL; ?>assets/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet"> -->
    <!-- Using Font Awesome CDN for now -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Custom Theme Style (REMOVED - Using app.css for custom theme) -->
    <!-- <link href="<?php echo BASE_URL; ?>assets/gentelella/css/custom.min.css" rel="stylesheet"> -->

    <!-- Custom App Style -->
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet"> 

</head>

<body> 
    <!-- Bootstrap Navbar -->
    <nav class="navbar navbar-expand-md navbar-dark sweet-navbar fixed-top">
        <a class="navbar-brand sweet-navbar-brand" href="<?php echo BASE_URL; ?>index.php">
             <i class="fa fa-birthday-cake"></i> Sweet Creations
        </a>
        <span class="sweet-dashboard-title-navbar"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></span> <!-- Use dynamic page title -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav ml-auto">
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUserLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                         <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>
                                </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownUserLink">
                        <a class="dropdown-item" href="<?php echo BASE_URL; ?>logout.php">
                            <i class="fa fa-sign-out"></i> Log Out
                        </a>
                                </div>
                            </li>
                        </ul>
        </div>
                    </nav>

    <div class="container-fluid sweet-main-container">
        <div class="row">
            <!-- Sidebar Column -->
            <?php include 'sidebar.php'; // Include the sidebar ?>

            <!-- Main Content Column -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4 sweet-main-content">
                 <!-- Page Title (REMOVED - Displayed in Navbar) -->
                 <?php /* if (isset($pageTitle)): ?>
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><?php echo htmlspecialchars($pageTitle); ?></h1>
                    </div>
                 <?php endif; */ ?>
                 
                    <!-- Content starts here --> 