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

// Fetch customer's order history
$customerOrders = getOrdersByCustomer($customerId);

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
                         <div class="alert alert-info">
                             <i class="fa fa-info-circle"></i> No orders found for this customer yet.
                             <a href="../orders/add_order.php" class="btn btn-sm btn-primary ml-2">
                                 <i class="fa fa-plus"></i> Create First Order
                             </a>
                         </div>
                     <?php else: ?>
                         <table class="table table-bordered">
                             <thead>
                                 <tr>
                                     <th>Order ID</th>
                                     <th>Order Date</th>
                                     <th>Delivery Date</th>
                                     <th>Total</th>
                                     <th>Status</th>
                                     <th>Actions</th>
                                 </tr>
                             </thead>
                             <tbody>
                                 <?php foreach ($customerOrders as $order): ?>
                                 <tr>
                                     <td><?php echo $order['order_id']; ?></td>
                                     <td><?php echo formatDate($order['order_date'], 'd M Y'); ?></td>
                                     <td><?php echo formatDate($order['delivery_date'], 'd M Y'); ?></td>
                                     <td><?php echo formatCurrency($order['total_amount']); ?></td> 
                                     <td>
                                         <span class="badge badge-<?php 
                                             echo $order['order_status'] === 'delivered' ? 'success' : 
                                                  ($order['order_status'] === 'in_progress' ? 'warning' : 
                                                   ($order['order_status'] === 'ready' ? 'info' : 'secondary')); 
                                         ?>">
                                             <?php echo ucfirst(htmlspecialchars($order['order_status'])); ?>
                                         </span>
                                     </td>
                                     <td>
                                         <a href="../orders/view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-xs">View</a>
                                     </td>
                                 </tr>
                                 <?php endforeach; ?>
                             </tbody>
                         </table>
                     <?php endif; ?>
                 </div>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 