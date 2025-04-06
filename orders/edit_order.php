<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'Edit Order';

// Get order ID from URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: orders.php?status=invalid_id');
    exit;
}

// Initialize variables
$orderData = getOrderById($orderId); // Includes customer info
$originalData = $orderData; 
$errors = [];
$successMessage = '';

// Check if order exists
if (!$orderData) {
    header('Location: orders.php?status=not_found');
    exit;
}

// Get order items (read-only for this edit page)
$orderItems = getOrderItems($orderId);

// Define possible order statuses
$orderStatuses = ['New', 'In Progress', 'Ready', 'Delivered', 'Cancelled'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve editable form data
    $submittedData = [
        'delivery_date' => trim($_POST['delivery_date'] ?? ''),
        'delivery_time' => trim($_POST['delivery_time'] ?? ''), // Allow empty string
        'delivery_address' => trim($_POST['delivery_address'] ?? ''),
        'order_status' => trim($_POST['order_status'] ?? ''),
        'is_paid' => isset($_POST['is_paid']) ? 1 : 0,
        'special_requirements' => trim($_POST['special_requirements'] ?? '')
    ];

    // Basic Validation (can be expanded)
    if(empty($submittedData['delivery_date'])) $errors['delivery_date'] = "Delivery date is required.";
    elseif(strtotime($submittedData['delivery_date']) === false) $errors['delivery_date'] = "Invalid date format.";
    if(empty($submittedData['delivery_address'])) $errors['delivery_address'] = "Delivery address is required.";
    if(!in_array($submittedData['order_status'], $orderStatuses)) $errors['order_status'] = "Invalid order status selected.";

    // If validation passes, attempt to update order
    if (empty($errors)) {
        if (updateOrder($orderId, $submittedData)) {
            // Success!
            $successMessage = "Order updated successfully!";
            // Refresh order data to show updated values
            $orderData = getOrderById($orderId);
            $originalData = $orderData; // Update original as well after save
            // Optionally redirect: header('Location: orders.php?status=updated'); exit;
        } else {
            $errors['database'] = "Failed to update order in the database. Please try again.";
            // Keep submitted data in form view (merge with non-editable data)
            $orderData = array_merge($orderData, $submittedData);
        }
    } else {
         // Keep submitted data in form view if validation fails
         $orderData = array_merge($orderData, $submittedData);
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Edit Order #<?php echo $orderId; ?></h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="orders.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a></li>
                    <li><a href="view_order.php?id=<?php echo $orderId; ?>" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> View Order</a></li>
                </ul>
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

                <form action="edit_order.php?id=<?php echo $orderId; ?>" method="post" class="form-horizontal form-label-left">
                    
                    <div class="row">
                         <div class="col-md-6">
                             <h4>Customer & Order Info (Read-Only)</h4>
                             <p>
                                <strong>Customer:</strong> <?php echo htmlspecialchars($orderData['customer_name']); ?> (ID: <?php echo $orderData['customer_id']; ?>)<br>
                                <strong>Phone:</strong> <?php echo htmlspecialchars($orderData['customer_phone'] ?: 'N/A'); ?><br>
                                <strong>Order Date:</strong> <?php echo formatDate($orderData['order_date'], 'd M Y H:i'); ?><br>
                                <strong>Total Amount:</strong> <?php echo formatCurrency($orderData['total_amount']); ?><br>
                                <strong>Placed By:</strong> <?php echo htmlspecialchars($orderData['user_name'] ?: 'N/A'); ?>
                             </p>
                         </div>
                         <div class="col-md-6">
                            <h4>Order Status & Payment</h4>
                             <div class="form-group row">
                                <label class="control-label col-md-3 col-sm-3" for="order_status">Order Status *</label>
                                <div class="col-md-9 col-sm-9">
                                    <select name="order_status" id="order_status" class="form-control" required>
                                        <?php foreach ($orderStatuses as $status): ?>
                                        <option value="<?php echo $status; ?>" <?php echo ($orderData['order_status'] == $status) ? 'selected' : ''; ?>><?php echo $status; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                     <?php if (!empty($errors['order_status'])): ?><span class="text-danger"><?php echo $errors['order_status']; ?></span><?php endif; ?>
                                </div>
                            </div>
                             <div class="form-group row">
                                <label class="control-label col-md-3 col-sm-3">Payment</label>
                                <div class="col-md-9 col-sm-9">
                                    <div class="checkbox">
                                        <label>
                                             <input type="checkbox" name="is_paid" value="1" <?php echo !empty($orderData['is_paid']) ? 'checked' : ''; ?>> Mark as Paid
                                        </label>
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>
                    
                    <div class="ln_solid"></div>

                    <h4>Delivery Details (Editable)</h4>
                     <div class="form-group row">
                        <label for="delivery_date" class="control-label col-md-2">Delivery Date *</label>
                        <div class="col-md-4">
                            <input type="date" name="delivery_date" id="delivery_date" class="form-control" required value="<?php echo htmlspecialchars($orderData['delivery_date'] ?? ''); ?>">
                             <?php if (!empty($errors['delivery_date'])): ?><span class="text-danger"><?php echo $errors['delivery_date']; ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="delivery_time" class="control-label col-md-2">Delivery Time</label>
                        <div class="col-md-4">
                            <input type="time" name="delivery_time" id="delivery_time" class="form-control" value="<?php echo htmlspecialchars($orderData['delivery_time'] ?? ''); ?>">
                        </div>
                    </div>
                     <div class="form-group row">
                        <label for="delivery_address" class="control-label col-md-2">Delivery Address *</label>
                        <div class="col-md-10">
                            <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3" required><?php echo htmlspecialchars($orderData['delivery_address'] ?? ''); ?></textarea>
                             <?php if (!empty($errors['delivery_address'])): ?><span class="text-danger"><?php echo $errors['delivery_address']; ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="special_requirements" class="control-label col-md-2">Special Requirements</label>
                        <div class="col-md-10">
                            <textarea name="special_requirements" id="special_requirements" class="form-control" rows="3"><?php echo htmlspecialchars($orderData['special_requirements'] ?? ''); ?></textarea>
                        </div>
                    </div>

                     <div class="ln_solid"></div>

                    <h4>Order Items (Read-Only)</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr><th>Qty</th><th>Product</th><th>Size</th><th>Customization</th><th>Price Each</th><th>Subtotal</th></tr>
                        </thead>
                        <tbody>
                             <?php if (empty($orderItems)): ?>
                                <tr><td colspan="6">No items found for this order.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($item['cake_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['size'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['customization'] ?: 'N/A'); ?></td>
                                    <td><?php echo formatCurrency($item['price']); ?></td>
                                    <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                             <?php endif; ?>
                        </tbody>
                         <tfoot>
                             <tr>
                                 <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                                 <td><strong><?php echo formatCurrency($orderData['total_amount']); ?></strong></td>
                             </tr>
                        </tfoot>
                    </table>
                    <p class="text-muted">(Editing order items is not supported on this page. If changes are needed, consider cancelling and creating a new order.)</p>

                    <div class="ln_solid"></div>
                    <div class="form-group row">
                        <div class="col-md-9 offset-md-2">
                             <a href="orders.php" class="btn btn-primary">Cancel / View List</a>
                            <button type="submit" class="btn btn-success">Save Order Changes</button>
                        </div>
                    </div>

                </form>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 