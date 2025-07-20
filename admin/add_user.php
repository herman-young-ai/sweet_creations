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

$pageTitle = 'Add New Staff Member';

// Initialize variables
$userData = [
    'username' => '',
    'password' => '',
    'full_name' => '',
    'email' => '',
    'role' => 'Staff'
];
$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $userData = [
        'username' => trim($_POST['username'] ?? ''),
        'password' => trim($_POST['password'] ?? ''),
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role' => trim($_POST['role'] ?? 'Staff')
    ];

    // Validate form data
    $errors = validateUserForm($userData, false);
    
    // Check if username already exists
    if (empty($errors['username'])) {
        $conn = connectDB();
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT user_id FROM USERS WHERE username = ?");
                $stmt->execute([$userData['username']]);
                if ($stmt->fetch()) {
                    $errors['username'] = "Username already exists. Please choose a different username.";
                }
            } catch (PDOException $e) {
                error_log("Username check error: " . $e->getMessage());
            } finally {
                $conn = null;
            }
        }
    }

    // If validation passes, attempt to add user
    if (empty($errors)) {
        $newUserId = addUser($userData);
        if ($newUserId) {
            // Success! Redirect to manage users page
            header('Location: manage_users.php?status=added');
            exit;
        } else {
            $errors['database'] = "Failed to add staff member to the database. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Add New Staff Member</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($errors['database'])): ?>
                     <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>

                <form action="add_user.php" method="post" class="form-horizontal form-label-left">

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="username">Username</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="username" name="username" required="required" class="form-control" maxlength="50" pattern="[a-zA-Z0-9._-]+" title="Username can only contain letters, numbers, dots, hyphens, and underscores" value="<?php echo htmlspecialchars($userData['username']); ?>">
                            <?php if (!empty($errors['username'])): ?><span class="text-danger"><?php echo $errors['username']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="password">Password</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="password" id="password" name="password" required="required" class="form-control" maxlength="255" minlength="6" title="Password must be at least 6 characters long" value="<?php echo htmlspecialchars($userData['password']); ?>">
                            <?php if (!empty($errors['password'])): ?><span class="text-danger"><?php echo $errors['password']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="full_name">Full Name</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="full_name" name="full_name" required="required" class="form-control" maxlength="100" pattern="[a-zA-Z\s\-'.]+" title="Name can only contain letters, spaces, hyphens, apostrophes, and periods" value="<?php echo htmlspecialchars($userData['full_name']); ?>">
                            <?php if (!empty($errors['full_name'])): ?><span class="text-danger"><?php echo $errors['full_name']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="email" id="email" name="email" class="form-control" maxlength="100" placeholder="user@example.com" value="<?php echo htmlspecialchars($userData['email']); ?>">
                            <?php if (!empty($errors['email'])): ?><span class="text-danger"><?php echo $errors['email']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="role">Role</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select id="role" name="role" class="form-control" required>
                                <option value="Staff" <?php echo $userData['role'] === 'Staff' ? 'selected' : ''; ?>>Staff</option>
                                <option value="Admin" <?php echo $userData['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <?php if (!empty($errors['role'])): ?><span class="text-danger"><?php echo $errors['role']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <a href="manage_users.php" class="btn btn-secondary">Cancel / View List</a>
                            <button class="btn btn-success" type="submit">Add Staff Member</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 