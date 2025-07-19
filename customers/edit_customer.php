<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'Edit Customer';

// Get customer ID from URL
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    // Redirect if ID is invalid or missing
    header('Location: customers.php?error=invalid_id');
    exit;
}

// Initialize variables
$customerData = getCustomerById($customerId);
$originalData = $customerData; // Keep original for comparison or display if needed
$errors = [];
$successMessage = '';

// Check if customer exists
if (!$customerData) {
    header('Location: customers.php?error=not_found');
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $submittedData = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'notes' => trim($_POST['notes'] ?? '')
    ];

    // Validate form data
    $errors = validateCustomerForm($submittedData);

    // If validation passes, attempt to update customer
    if (empty($errors)) {
        if (updateCustomer($customerId, $submittedData)) {
            // Success! Display message and update the data shown in the form
            $successMessage = "Customer updated successfully!";
            $customerData = $submittedData; // Update form fields to show saved data
            // Optional: Redirect back to list: header('Location: customers.php?status=updated'); exit;
        } else {
            $errors['database'] = "Failed to update customer in the database. Please try again.";
            // Keep submitted data in form on database error
            $customerData = $submittedData;
        }
    } else {
        // Keep submitted data in form if validation fails
        $customerData = $submittedData;
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Edit Customer: <?php echo htmlspecialchars($originalData['full_name']); ?> (ID: <?php echo $customerId; ?>)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $successMessage; ?> <a href="customers.php" class="alert-link">Back to List</a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['database'])): ?>
                     <div class="alert alert-danger alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_customer.php?id=<?php echo $customerId; ?>" method="post" class="form-horizontal form-label-left">

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="full_name">Full Name</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" id="full_name" name="full_name" required="required" class="form-control" maxlength="100" pattern="[a-zA-Z\s\-'.]+" title="Name can only contain letters, spaces, hyphens, apostrophes, and periods" value="<?php echo htmlspecialchars($customerData['full_name']); ?>">
                            <?php if (!empty($errors['full_name'])): ?><span class="text-danger"><?php echo $errors['full_name']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12 required" for="phone_number">Phone Number</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="tel" id="phone_number" name="phone_number" required="required" class="form-control" maxlength="14" pattern="^\+230\s[5-9]\d{3}\s\d{4}$" title="Enter Mauritius mobile number in format: +230 5123 4567" placeholder="+230 XXXX XXXX" value="<?php echo htmlspecialchars($customerData['phone_number']); ?>">
                             <?php if (!empty($errors['phone_number'])): ?><span class="text-danger"><?php echo $errors['phone_number']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="email" id="email" name="email" class="form-control" placeholder="Optional" value="<?php echo htmlspecialchars($customerData['email']); ?>">
                             <?php if (!empty($errors['email'])): ?><span class="text-danger"><?php echo $errors['email']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="address">Address</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <textarea id="address" name="address" class="form-control" rows="3" placeholder="Optional"><?php echo htmlspecialchars($customerData['address']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="notes">Notes</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="Optional (e.g., allergies, preferences)"><?php echo htmlspecialchars($customerData['notes']); ?></textarea>
                        </div>
                    </div>

                     <div class="form-group row">
                         <label class="control-label col-md-3 col-sm-3 col-xs-12">Date Added</label>
                         <div class="col-md-6 col-sm-6 col-xs-12">
                             <input type="text" readonly class="form-control-plaintext" value="<?php echo date('d M Y H:i', strtotime($originalData['date_added'])); ?>">
                         </div>
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group row">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <a href="customers.php" class="btn btn-primary">Cancel / View List</a>
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </div>

                </form>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 