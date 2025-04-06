<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'View Customer';

// Get customer ID from URL
$customerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($customerId <= 0) {
    // Redirect if ID is invalid or missing
    header('Location: customers.php?status=invalid_id');
    exit;
}

// Fetch customer data
$customer = getCustomerById($customerId);

// Check if customer exists
if (!$customer) {
    header('Location: customers.php?status=not_found');
    exit;
}

// TODO: Fetch customer's order history here later
$customerOrders = []; // Placeholder

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Customer Details: <?php echo htmlspecialchars($customer['full_name']); ?></h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="customers.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a></li>
                    <li><a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Edit Customer</a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="col-md-6 col-sm-6">
                    <h4>Contact Information</h4>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width:30%;">Customer ID:</th>
                                <td><?php echo $customer['customer_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Full Name:</th>
                                <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            </tr>
                             <tr>
                                <th>Phone Number:</th>
                                <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                            </tr>
                             <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($customer['email'] ?: 'N/A'); ?></td>
                            </tr>
                             <tr>
                                <th>Address:</th>
                                <td><?php echo nl2br(htmlspecialchars($customer['address'] ?: 'N/A')); ?></td>
                            </tr>
                             <tr>
                                <th>Date Added:</th>
                                <td><?php echo date('d M Y H:i', strtotime($customer['date_added'])); ?></td>
                            </tr>
                            <tr>
                                <th>Notes:</th>
                                <td><?php echo nl2br(htmlspecialchars($customer['notes'] ?: 'N/A')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6 col-sm-6">
                     <h4>Order History</h4>
                     <?php if (empty($customerOrders)): ?>
                         <p>No orders found for this customer yet.</p>
                         <!-- Add link to create new order for this customer? -->
                     <?php else: ?>
                         <table class="table table-bordered">
                             <thead>
                                 <tr>
                                     <th>Order ID</th>
                                     <th>Order Date</th>
                                     <th>Total Amount</th>
                                     <th>Status</th>
                                     <th>Actions</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach ($customerOrders as $order): ?>
                                 <tr>
                                     <td><?php echo $order['order_id']; // Example ?></td>
                                     <td><?php echo date('d M Y', strtotime($order['order_date'])); // Example ?></td>
                                     <td><?php /* echo formatCurrency($order['total_amount']); */ // Example ?></td> 
                                     <td><?php echo htmlspecialchars($order['order_status']); // Example ?></td>
                                     <td>
                                         <a href="../orders/view_order.php?id=<?php echo $order['order_id']; // Example ?>" class="btn btn-info btn-xs">View Order</a>
                                     </td>
                                 </tr>
                                 <?php endforeach; ?>
                             </tbody>
                         </table>
                     <?php endif; ?>
                      <p class="text-muted">(Order history functionality will be implemented later)</p>
                 </div>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 