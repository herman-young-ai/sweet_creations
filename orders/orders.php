<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Set page title
$pageTitle = 'Manage Orders';

// Handle status messages from redirects
$statusMessage = '';
$messageType = 'info'; // Default type
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $statusMessage = 'Order successfully deleted.';
            $messageType = 'success';
            break;
        case 'error':
            $statusMessage = 'An error occurred during the operation.';
            $messageType = 'danger';
            break;
         case 'not_found':
            $statusMessage = 'The requested order was not found.';
            $messageType = 'warning';
            break;
        case 'invalid_id':
            $statusMessage = 'Invalid order ID specified.';
            $messageType = 'warning';
            break;
        // Add cases for added/updated if needed
    }
}

// TODO: Add Filtering/Search later

// Fetch all orders (joining with customer name by default)
$orders = getAllOrders(); // Default order: order_date DESC

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Order List</h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="add_order.php" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add New Order</a></li>
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

                <?php if (empty($orders)): ?>
                    <div class="alert alert-info">No orders found yet. <a href="add_order.php">Create the first one!</a></div>
                <?php else: ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?> (ID: <?php echo $order['customer_id']; ?>)</td>
                                <td><?php echo formatDate($order['order_date'], 'd M Y H:i'); ?></td>
                                <td><?php echo formatDate($order['delivery_date'], 'd M Y'); ?></td>
                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        // Basic status coloring (can be improved)
                                        switch(strtolower($order['order_status'])) {
                                            case 'new': echo 'info'; break;
                                            case 'in progress': echo 'warning'; break;
                                            case 'ready': echo 'primary'; break;
                                            case 'delivered': echo 'success'; break;
                                            case 'cancelled': echo 'danger'; break;
                                            default: echo 'secondary';
                                        }
                                    ?>">
                                        <?php echo htmlspecialchars($order['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a>
                                    <a href="edit_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a>
                                    <!-- Simple Delete might be too dangerous for orders, maybe just allow cancellation via Edit page -->
                                    <!-- <a href="delete_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure? This also deletes associated items.');"><i class="fa fa-trash"></i> Delete</a> -->
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
include '../includes/footer.php'; 
?> 