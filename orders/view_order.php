<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'View Order Details';

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: orders.php?status=invalid_id');
    exit;
}

// Fetch order data (includes customer and user info)
$order = getOrderById($orderId);

// Check if order exists
if (!$order) {
    header('Location: orders.php?status=not_found');
    exit;
}

// Fetch order items (includes product name)
$orderItems = getOrderItems($orderId);

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Order #<?php echo $order['order_id']; ?> Details</h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="orders.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a></li>
                    <li><a href="edit_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Edit Order</a></li>
                    <!-- Add Print/PDF button later? -->
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                
                <section class="content invoice">
                    <!-- Order Info row -->
                    <div class="row invoice-info">
                        <div class="col-sm-4 invoice-col">
                            <strong>Customer Details:</strong>
                            <address>
                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong> (ID: <?php echo $order['customer_id']; ?>)<br>
                                Phone: <?php echo htmlspecialchars($order['customer_phone'] ?: 'N/A'); ?><br>
                                Email: <?php echo htmlspecialchars($order['customer_email'] ?: 'N/A'); ?>
                            </address>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-4 invoice-col">
                            <strong>Delivery Details:</strong>
                            <address>
                                <strong>Address:</strong><br>
                                <?php echo nl2br(htmlspecialchars($order['delivery_address'] ?: 'N/A')); ?><br>
                                <strong>Date:</strong> <?php echo formatDate($order['delivery_date']); ?><br>
                                <strong>Time:</strong> <?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any time'; ?>
                            </address>
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-4 invoice-col">
                            <b>Order ID:</b> #<?php echo $order['order_id']; ?><br>
                            <b>Order Date:</b> <?php echo formatDate($order['order_date'], 'd M Y H:i'); ?><br>
                            <b>Status:</b> <?php echo htmlspecialchars($order['order_status']); ?><br>
                            <b>Payment:</b> <?php echo $order['is_paid'] ? 'Paid' : 'Not Paid'; ?><br>
                            <b>Placed By:</b> <?php echo htmlspecialchars($order['user_name'] ?: 'N/A'); ?> (ID: <?php echo $order['user_id']; ?>)
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->

                     <!-- Special Requirements row -->
                    <?php if(!empty($order['special_requirements'])): ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <p class="lead">Special Requirements:</p>
                            <p class="text-muted well well-sm no-shadow" style="margin-top: 10px;">
                                <?php echo nl2br(htmlspecialchars($order['special_requirements'])); ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <!-- /.row -->

                    <!-- Table row -->
                    <div class="row">
                        <div class="col-xs-12 table">
                            <p class="lead">Order Items:</p>
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Qty</th>
                                        <th>Product (ID)</th>
                                        <th>Size</th>
                                        <th>Customization</th>
                                        <th>Price Each</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($orderItems)): ?>
                                        <tr><td colspan="6">No items found for this order.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo htmlspecialchars($item['cake_name']); ?> (<?php echo $item['product_id']; ?>)</td>
                                            <td><?php echo htmlspecialchars($item['size'] ?: 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['customization'] ?: 'N/A'); ?></td>
                                            <td><?php echo formatCurrency($item['price']); ?></td>
                                            <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->

                    <div class="row">
                        <!-- accepted payments column -->
                        <div class="col-xs-6">
                            <!-- Payment notes or methods can go here -->
                        </div>
                        <!-- /.col -->
                        <div class="col-xs-6">
                            <p class="lead">Amount Due</p>
                            <div class="table-responsive">
                                <table class="table">
                                    <tbody>
                                        <!-- Add subtotal, tax, shipping later if needed -->
                                        <tr>
                                            <th style="width:50%">Total:</th>
                                            <td><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- /.col -->
                    </div>
                    <!-- /.row -->

                </section>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 