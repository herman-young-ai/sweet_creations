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

// Get user data
$user = getUserById($userId);
if (!$user) {
    header('Location: manage_users.php?status=not_found');
    exit;
}

$pageTitle = 'View Staff Member';

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Staff Member Details</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">User ID:</th>
                                    <td><?php echo $user['user_id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Username:</th>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                </tr>
                                <tr>
                                    <th>Full Name:</th>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td><?php echo htmlspecialchars($user['email'] ?: 'Not provided'); ?></td>
                                </tr>
                                <tr>
                                    <th>Role:</th>
                                    <td>
                                        <span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : 'info'; ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Login:</th>
                                    <td>
                                        <?php 
                                        if ($user['last_login']) {
                                            echo formatDate($user['last_login'], 'd M Y H:i');
                                        } else {
                                            echo '<span class="text-muted">Never logged in</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <a href="manage_users.php" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back to Staff List
                        </a>
                        <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary">
                            <i class="fa fa-edit"></i> Edit Staff Member
                        </a>
                        <?php if ($user['user_id'] != $_SESSION['user_id'] && $user['role'] !== 'Admin'): ?>
                            <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this staff account? This action cannot be undone.')">
                                <i class="fa fa-trash"></i> Delete Staff Member
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 