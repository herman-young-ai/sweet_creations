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
                            <input type="text" id="username" name="username" required="required" class="form-control" 
                                   maxlength="50" minlength="3"
                                   pattern="^[a-zA-Z0-9._-]{3,50}$" 
                                   title="Username must be 3-50 characters and can only contain letters, numbers, dots, hyphens, and underscores" 
                                   value="<?php echo htmlspecialchars($userData['username']); ?>"
                                   oninput="validateUsername(this)"
                                   autocomplete="username">
                            <div class="invalid-feedback" id="username-feedback"></div>
                            <?php if (!empty($errors['username'])): ?><span class="text-danger"><?php echo $errors['username']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="password" id="password" name="password" class="form-control" 
                                   maxlength="255" minlength="6"
                                   pattern="^.{6,255}$"
                                   placeholder="Leave blank to keep current password" 
                                   title="Password must be at least 6 characters long (leave blank to keep current)" 
                                   value=""
                                   oninput="validatePasswordEdit(this)"
                                   autocomplete="new-password">
                            <div class="invalid-feedback" id="password-feedback"></div>
                            <small class="form-text text-muted">Leave blank to keep the current password</small>
                            <?php if (!empty($errors['password'])): ?><span class="text-danger"><?php echo $errors['password']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="full_name">Full Name</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="full_name" name="full_name" required="required" class="form-control" 
                                   maxlength="100" minlength="2"
                                   pattern="^[a-zA-Z\s\-'.]{2,100}$" 
                                   title="Name must be 2-100 characters and can only contain letters, spaces, hyphens, apostrophes, and periods" 
                                   value="<?php echo htmlspecialchars($userData['full_name']); ?>"
                                   oninput="validateFullName(this)"
                                   autocomplete="name">
                            <div class="invalid-feedback" id="full_name-feedback"></div>
                            <?php if (!empty($errors['full_name'])): ?><span class="text-danger"><?php echo $errors['full_name']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="email" id="email" name="email" class="form-control" 
                                   maxlength="100"
                                   pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
                                   placeholder="user@example.com" 
                                   title="Please enter a valid email address"
                                   value="<?php echo htmlspecialchars($userData['email']); ?>"
                                   oninput="validateEmail(this)"
                                   autocomplete="email">
                            <div class="invalid-feedback" id="email-feedback"></div>
                            <small class="form-text text-muted">Optional - for notifications and password recovery</small>
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

<script>
// Real-time validation functions
function validateUsername(input) {
    const value = input.value.trim();
    const feedback = document.getElementById('username-feedback');
    
    if (value.length === 0) {
        setFieldState(input, feedback, '', false);
        return;
    }
    
    if (value.length < 3) {
        setFieldState(input, feedback, 'Username must be at least 3 characters long', false);
        return;
    }
    
    if (value.length > 50) {
        setFieldState(input, feedback, 'Username must not exceed 50 characters', false);
        return;
    }
    
    if (!/^[a-zA-Z0-9._-]+$/.test(value)) {
        setFieldState(input, feedback, 'Username can only contain letters, numbers, dots, hyphens, and underscores', false);
        return;
    }
    
    setFieldState(input, feedback, 'Username looks good!', true);
}

function validatePasswordEdit(input) {
    const value = input.value;
    const feedback = document.getElementById('password-feedback');
    
    // For edit form, empty password is valid (keeps current password)
    if (value.length === 0) {
        setFieldState(input, feedback, 'Current password will be kept', true);
        return;
    }
    
    if (value.length < 6) {
        setFieldState(input, feedback, 'Password must be at least 6 characters long', false);
        return;
    }
    
    if (value.length > 255) {
        setFieldState(input, feedback, 'Password must not exceed 255 characters', false);
        return;
    }
    
    setFieldState(input, feedback, 'New password will be set!', true);
}

function validateFullName(input) {
    const value = input.value.trim();
    const feedback = document.getElementById('full_name-feedback');
    
    if (value.length === 0) {
        setFieldState(input, feedback, '', false);
        return;
    }
    
    if (value.length < 2) {
        setFieldState(input, feedback, 'Full name must be at least 2 characters long', false);
        return;
    }
    
    if (value.length > 100) {
        setFieldState(input, feedback, 'Full name must not exceed 100 characters', false);
        return;
    }
    
    if (!/^[a-zA-Z\s\-'.]+$/.test(value)) {
        setFieldState(input, feedback, 'Name can only contain letters, spaces, hyphens, apostrophes, and periods', false);
        return;
    }
    
    setFieldState(input, feedback, 'Full name looks good!', true);
}

function validateEmail(input) {
    const value = input.value.trim();
    const feedback = document.getElementById('email-feedback');
    
    if (value.length === 0) {
        setFieldState(input, feedback, '', true); // Email is optional
        return;
    }
    
    if (value.length > 100) {
        setFieldState(input, feedback, 'Email must not exceed 100 characters', false);
        return;
    }
    
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(value)) {
        setFieldState(input, feedback, 'Please enter a valid email address', false);
        return;
    }
    
    setFieldState(input, feedback, 'Email address is valid!', true);
}

function setFieldState(input, feedback, message, isValid) {
    if (isValid) {
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        feedback.classList.remove('invalid-feedback');
        feedback.classList.add('valid-feedback');
        feedback.textContent = message;
    } else {
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback');
        feedback.textContent = message;
    }
}

// Form submission validation
document.querySelector('form').addEventListener('submit', function(e) {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    
    // Trigger validation for all fields
    validateUsername(username);
    validatePasswordEdit(password); // Use edit-specific password validation
    validateFullName(fullName);
    validateEmail(email);
    
    // Check if any field is invalid (excluding password since it's optional in edit)
    const invalidFields = document.querySelectorAll('.is-invalid');
    if (invalidFields.length > 0) {
        e.preventDefault();
        alert('Please fix the validation errors before submitting the form.');
        invalidFields[0].focus();
    }
});

// Trigger validation on page load for existing values
document.addEventListener('DOMContentLoaded', function() {
    const username = document.getElementById('username');
    const fullName = document.getElementById('full_name');
    const email = document.getElementById('email');
    
    // Validate pre-filled fields
    if (username.value) validateUsername(username);
    if (fullName.value) validateFullName(fullName);
    if (email.value) validateEmail(email);
});
</script>

<?php include '../includes/footer.php'; ?> 