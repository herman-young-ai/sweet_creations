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

// Get existing user data
$user = getUserById($userId);
if (!$user) {
    header('Location: manage_users.php?status=not_found');
    exit;
}

$pageTitle = 'Edit Staff Member';

// Initialize variables with existing data
$userData = [
    'username' => $user['username'],
    'password' => '', // Don't pre-fill password
    'full_name' => $user['full_name'],
    'email' => $user['email'] ?: '',
    'role' => $user['role']
];
$errors = [];

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

    // Validate form data (edit mode - password optional)
    $errors = validateUserForm($userData, true);
    
    // Check if username already exists (excluding current user)
    if (empty($errors['username']) && $userData['username'] !== $user['username']) {
        $conn = connectDB();
        if ($conn) {
            try {
                $stmt = $conn->prepare("SELECT user_id FROM USERS WHERE username = ? AND user_id != ?");
                $stmt->execute([$userData['username'], $userId]);
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

    // If validation passes, attempt to update user
    if (empty($errors)) {
        // Remove password from update data if empty
        $updateData = $userData;
        if (empty($updateData['password'])) {
            unset($updateData['password']);
        }
        
        $success = updateUser($userId, $updateData);
        if ($success) {
            // Success! Redirect to manage users page
            header('Location: manage_users.php?status=updated');
            exit;
        } else {
            $errors['database'] = "Failed to update staff member. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Edit Staff Member</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($errors['database'])): ?>
                     <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_user.php?id=<?php echo $userId; ?>" method="post" class="form-horizontal form-label-left">

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="username">Username</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="username" name="username" required="required" class="form-control" maxlength="50" pattern="[a-zA-Z0-9._-]+" title="Username can only contain letters, numbers, dots, hyphens, and underscores" value="<?php echo htmlspecialchars($userData['username']); ?>">
                            <?php if (!empty($errors['username'])): ?><span class="text-danger"><?php echo $errors['username']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="password" id="password" name="password" class="form-control" maxlength="255" minlength="6" placeholder="Leave blank to keep current password" value="">
                            <small class="form-text text-muted">Leave blank to keep the current password</small>
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
                            <button class="btn btn-primary" type="submit">Update Staff Member</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 