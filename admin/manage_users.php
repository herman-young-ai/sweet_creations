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

$pageTitle = 'Manage Staff Accounts';

// Handle status messages from redirects
$statusMessage = '';
$messageType = 'info';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'added':
            $statusMessage = 'Staff account successfully created.';
            $messageType = 'success';
            break;
        case 'updated':
            $statusMessage = 'Staff account successfully updated.';
            $messageType = 'success';
            break;
        case 'deleted':
            $statusMessage = 'Staff account successfully deleted.';
            $messageType = 'success';
            break;
        case 'error':
            $statusMessage = 'An error occurred during the operation.';
            $messageType = 'danger';
            break;
        case 'not_found':
            $statusMessage = 'The requested staff account was not found.';
            $messageType = 'warning';
            break;
        case 'cannot_delete_self':
            $statusMessage = 'You cannot delete your own account.';
            $messageType = 'warning';
            break;
        case 'cannot_delete_admin':
            $statusMessage = 'Admin accounts cannot be deleted.';
            $messageType = 'warning';
            break;
    }
}

// Get all staff accounts sorted by ID (exclude the current admin from deletion)
$users = getAllUsers('user_id', 'ASC');

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Manage Staff Accounts</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($statusMessage)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $statusMessage; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <a href="add_user.php" class="btn btn-primary mb-3">
                            <i class="fa fa-plus"></i> Add New Staff Member
                        </a>
                    </div>
                </div>

                <?php if (empty($users)): ?>
                    <div class="alert alert-info">No staff accounts found. <a href="add_user.php">Create the first staff account!</a></div>
                <?php else: ?>
                    <table class="table table-striped table-bordered users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Last Login</th>
                                <th style="width: 20%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email'] ?: 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : 'info'; ?>">
                                        <?php echo htmlspecialchars($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if ($user['last_login']) {
                                        echo formatDate($user['last_login'], 'd M Y H:i');
                                    } else {
                                        echo '<span class="text-muted">Never</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="view_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-info btn-sm">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                    <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-edit"></i> Edit
                                    </a>
                                    <?php if ($user['user_id'] != $_SESSION['user_id'] && $user['role'] !== 'Admin'): ?>
                                        <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Are you sure you want to delete this staff account? This action cannot be undone.')">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 