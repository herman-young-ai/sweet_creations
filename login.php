<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'includes/config.php'; 
require_once 'includes/functions.php';

$errorMessage = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $errorMessage = "Username and password are required.";
    } else {
        // Attempt to log in the user (We will add loginUser function later)
        if (loginUser($username, $password)) {
            // Redirect to dashboard on successful login
            header("Location: index.php");
            exit;
        } else {
            $errorMessage = "Invalid username or password.";
        }
    }
}

// Define BASE_URL for asset paths (simplified version for login page)
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $scriptName = $_SERVER['SCRIPT_NAME']; 
    $projectName = 'sweet_creations'; 
    $basePath = substr($scriptName, 0, strpos($scriptName, $projectName) + strlen($projectName));
     if(empty($basePath) || strpos($scriptName, $projectName) === false){
        $basePath = '/' . $projectName; 
    }
    define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Sweet Creations</title>
    <!-- Bootstrap -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo BASE_URL; ?>assets/gentelella/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="<?php echo BASE_URL; ?>assets/gentelella/vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="<?php echo BASE_URL; ?>assets/gentelella/vendors/animate.css/animate.min.css" rel="stylesheet">
    <!-- Custom Theme Style (REMOVED FOR CUSTOM LOGIN) -->
    <!-- <link href="<?php echo BASE_URL; ?>assets/gentelella/css/custom.min.css" rel="stylesheet"> -->
    <!-- Custom App Style -->
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet">
</head>
<body class="sweet-login-page">

    <div class="login-container">
        <div class="login-icon">
            <i class="fa fa-birthday-cake"></i>
        </div>
        <div class="login-title">
            Sweet Creations
        </div>

        <form action="login.php" method="post" class="login-form">
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($errorMessage); ?>
                            </div>
                        <?php endif; ?>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" class="form-control" id="username" required name="username" value="rashni.devi" />
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" required name="password" value="admin123" />
                        </div>

            <button class="btn btn-login" type="submit">Login</button>
                    </form>
    </div>

    <!-- Optional: Add JS links if needed later -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/jquery.min.js"></script> -->
    <!-- <script src="<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js"></script> -->

</body>
</html> 