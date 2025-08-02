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
        case 'delivered_order':
            $statusMessage = 'Cannot delete order because it has been delivered.';
            $messageType = 'warning';
            break;
        // Add cases for added/updated if needed
    }
}

// Handle Search
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearch = !empty($searchTerm);

// Fetch orders - either all or based on search
if ($isSearch) {
    $orders = searchOrders($searchTerm);
} else {
    $orders = getAllOrders(); // Default order: order_date DESC
}

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

                <!-- Search Form -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                         <form action="orders.php" method="get" class="form-inline float-right">
                            <div class="form-group mr-2 mb-2">
                                <input type="text" name="search" class="form-control" placeholder="ID, Customer, Status, DeliveryDate" title="Search across order details, customer names, order status, delivery addresses, and delivery dates (YYYY-MM-DD, DD/MM/YYYY, or DD MMM YYYY)" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
                            <?php if ($isSearch): ?>
                                <a href="orders.php" class="btn btn-secondary mb-2 ml-1">Clear Search</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <!-- /Search Form -->

                <?php if ($isSearch && empty($orders)): ?>
                     <div class="alert alert-warning mt-3">No orders found matching your search term: "<?php echo htmlspecialchars($searchTerm); ?>". <a href="orders.php">Show all orders.</a></div>
                <?php elseif (!$isSearch && empty($orders)): ?>
                    <div class="alert alert-info">No orders found yet. <a href="add_order.php">Create the first one!</a></div>
                <?php else: ?>
                    <table class="table table-striped table-bordered orders-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer Name</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th style="width: 20%;">Actions</th>
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
                                        // Status coloring based on combined status
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
                                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a> <a href="edit_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a>
                                    <?php 
                                    // Show delete button only if order is not "Delivered"
                                    if (strtolower($order['order_status']) != 'delivered'): 
                                    ?>
                                        <a href="delete_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this order? This cannot be undone and will also delete all associated items.');"><i class="fa fa-trash"></i> Delete</a>
                                    <?php endif; ?>
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