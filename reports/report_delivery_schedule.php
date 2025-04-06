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
$pageTitle = 'Delivery Schedule Report';

// Get dates from form, default to today
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$orders = [];
$errorMessage = '';

// Validate dates if provided
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $validStart = strtotime($startDate);
    $validEnd = strtotime($endDate);

    if ($validStart === false || $validEnd === false) {
        $errorMessage = "Invalid date format provided. Please use YYYY-MM-DD for both dates.";
        // Reset dates to default if invalid
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d');
    } elseif ($validStart > $validEnd) {
        $errorMessage = "Start date cannot be after the end date.";
    } else {
        // Fetch orders for the selected date range
        $orders = getOrdersForDeliveryDateRange($startDate, $endDate);
    }
}

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Delivery Schedule</h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="reports.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a></li>
                 </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <!-- Date Range Selection Form -->
                <form action="report_delivery_schedule.php" method="get" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" class="form-control ml-2" value="<?php echo htmlspecialchars($startDate); ?>" required>
                    </div>
                     <div class="form-group mr-2">
                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" class="form-control ml-2" value="<?php echo htmlspecialchars($endDate); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
                <!-- /Date Range Selection Form -->

                <?php if ($errorMessage): ?>
                     <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['start_date']) && !$errorMessage): // Only show report table if dates were submitted ?>
                    <h3 class="mt-4">Orders for Delivery Between <?php echo formatDate($startDate); ?> and <?php echo formatDate($endDate); ?></h3>
                    
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info mt-3">No orders found for delivery in this date range.</div>
                    <?php else: ?>
                         <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Delivery Date</th>
                                    <th>Time</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo formatDate($order['delivery_date']); ?></td>
                                    <td><?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any'; ?></td>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars(getCustomerById($order['customer_id'])['phone_number']); // Fetch phone separately ?></td>
                                    <td><?php echo htmlspecialchars($order['delivery_address']); ?></td>
                                    <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                                    <td>
                                        <a href="../orders/view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-xs btn-outline-info">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php 
// Include footer
include '../includes/footer.php'; 
?> 