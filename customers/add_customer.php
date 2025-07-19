<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'Add New Customer';

// Initialize variables
$customerData = [
    'full_name' => '',
    'phone_number' => '',
    'email' => '',
    'address' => '',
    'notes' => ''
];
$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $customerData = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'phone_number' => trim($_POST['phone_number'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'notes' => trim($_POST['notes'] ?? '')
    ];

    // Validate form data
    $errors = validateCustomerForm($customerData);

    // If validation passes, attempt to add customer
    if (empty($errors)) {
        $newCustomerId = addCustomer($customerData);
        if ($newCustomerId) {
            // Success! Optionally redirect or clear form
            // For simplicity, we'll show a success message and clear the form data
            $successMessage = "Customer added successfully! (ID: " . $newCustomerId . ")";
            // Clear form data after successful submission
            $customerData = [
                'full_name' => '',
                'phone_number' => '',
                'email' => '',
                'address' => '',
                'notes' => ''
            ]; 
            // Optionally redirect: header('Location: customers.php?status=added'); exit;
        } else {
            $errors['database'] = "Failed to add customer to the database. Please try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Add New Customer</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['database'])): ?>
                     <div class="alert alert-danger alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>

                <form action="add_customer.php" method="post" class="form-horizontal form-label-left">

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

                    <div class="ln_solid"></div>
                    <div class="form-group row">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <a href="customers.php" class="btn btn-primary">Cancel / View List</a>
                            <button type="submit" class="btn btn-success">Add Customer</button>
                        </div>
                    </div>

                </form>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 