<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; // Adjust path: go up one directory
require_once '../includes/functions.php'; // Adjust path: go up one directory

// Ensure user is logged in
requireLogin();

// Set page title
$pageTitle = 'Manage Customers';

// Handle status messages from redirects (e.g., after delete)
$statusMessage = '';
$messageType = 'info'; // Default type
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $statusMessage = 'Customer successfully deleted.';
            $messageType = 'success';
            break;
        case 'has_orders':
            $statusMessage = 'Cannot delete customer because they have existing orders.';
            $messageType = 'warning';
            break;
        case 'error':
            $statusMessage = 'An error occurred during the operation.';
            $messageType = 'danger';
            break;
        case 'not_found':
            $statusMessage = 'The requested customer was not found.';
            $messageType = 'warning';
            break;
        case 'invalid_id':
            $statusMessage = 'Invalid customer ID specified.';
            $messageType = 'warning';
            break;
        // Add cases for added/updated if using redirects from add/edit
    }
}

// Handle Search
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearch = !empty($searchTerm);

// Fetch customers - either all or based on search
if ($isSearch) {
    $customers = searchCustomers($searchTerm);
} else {
    $customers = getAllCustomers(); // Using default order (by full_name ASC)
}

// Include header
include '../includes/header.php'; // Adjust path: go up one directory
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customer List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a href="add_customer.php" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add New Customer</a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($statusMessage)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $statusMessage; ?>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                         <form action="customers.php" method="get" class="form-inline float-right">
                            <div class="form-group mr-2 mb-2">
                                <input type="text" name="search" class="form-control" placeholder="Search Name/Phone/Email/Address..." title="Search across all customer fields. Use multiple words for more specific results." value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
                            <?php if ($isSearch): ?>
                                <a href="customers.php" class="btn btn-secondary mb-2 ml-1">Clear Search</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <!-- /Search Form -->

                <?php if ($isSearch && empty($customers)): ?>
                     <div class="alert alert-warning mt-3">No customers found matching your search term: "<?php echo htmlspecialchars($searchTerm); ?>". <a href="customers.php">Show all customers.</a></div>
                <?php elseif (!$isSearch && empty($customers)): ?>
                    <div class="alert alert-info">No customers found. <a href="add_customer.php">Add the first one!</a></div>
                <?php else: ?>
                    <table class="table table-striped table-bordered mt-3 customers-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>Date Added</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo date('d M Y', strtotime($customer['date_added'])); // Simple date format ?></td>
                                <td>
                                    <a href="view_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a> <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a> <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this customer? This cannot be undone.');"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php 
// Include footer
include '../includes/footer.php'; // Adjust path: go up one directory
?> 