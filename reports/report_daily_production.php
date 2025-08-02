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
$pageTitle = 'Daily Production Report';

// Get the date from the form, default to today
$reportDate = isset($_GET['report_date']) ? $_GET['report_date'] : date('Y-m-d');
$orders = [];
$errorMessage = '';

// Validate date format if provided
if (isset($_GET['report_date'])) {
    if (strtotime($reportDate) === false) {
        $errorMessage = "Invalid date format provided. Please use YYYY-MM-DD.";
        $reportDate = date('Y-m-d'); // Reset to default
    } else {
        // Fetch orders for the selected date
        $orders = getOrdersForDeliveryDate($reportDate);
    }
}

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Daily Production List</h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="reports.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a></li>
                    <?php if (isset($_GET['report_date']) && !$errorMessage && !empty($orders)): ?>
                    <li><button onclick="printReport()" class="btn btn-info btn-sm"><i class="fa fa-print"></i> Print Report</button></li>
                    <?php endif; ?>
                 </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <!-- Date Selection Form -->
                <form action="report_daily_production.php" method="get" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="report_date">Select Delivery Date:</label>
                        <input type="date" id="report_date" name="report_date" class="form-control ml-2" value="<?php echo htmlspecialchars($reportDate); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
                <!-- /Date Selection Form -->

                <?php if ($errorMessage): ?>
                     <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['report_date']) && !$errorMessage): // Only show report table if a date was submitted ?>
                    <h3 class="mt-4">Orders for Delivery on: <?php echo formatDate($reportDate, 'l, F j, Y'); ?></h3>
                    
                    <?php if (empty($orders)): ?>
                        <div class="alert alert-info mt-3">No orders found for delivery on this date.</div>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <strong>Order #<?php echo $order['order_id']; ?></strong> - 
                                    Customer: <?php echo htmlspecialchars($order['customer_name']); ?> 
                                    (<?php echo htmlspecialchars($order['customer_phone']); ?>) -
                                    Time: <?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any'; ?> -
                                    Status: <?php echo htmlspecialchars($order['order_status']); ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Items:</h5>
                                    <?php if (empty($order['items'])): ?>
                                        <p class="card-text">No items listed for this order.</p>
                                    <?php else: ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($order['items'] as $item): ?>
                                                <li class="list-group-item">
                                                    <strong><?php echo $item['quantity']; ?> x <?php echo htmlspecialchars($item['cake_name']); ?></strong>
                                                    <?php if (!empty($item['size'])): ?>
                                                        (Size: <?php echo htmlspecialchars($item['size']); ?>)
                                                    <?php endif; ?>
                                                    <?php if (!empty($item['customization'])): ?>
                                                        <br><small class="text-muted"><em>Notes: <?php echo htmlspecialchars($item['customization']); ?></em></small>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
                                     <?php if (!empty($order['special_requirements'])): ?>
                                        <h5 class="card-title mt-3">Special Requirements:</h5>
                                        <p class="card-text"><em><?php echo nl2br(htmlspecialchars($order['special_requirements'])); ?></em></p>
                                    <?php endif; ?>
                                    
                                </div>
                                <div class="card-footer text-muted">
                                    Delivery Address: <?php echo htmlspecialchars($order['delivery_address']); ?>
                                    <a href="../orders/view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-info float-right">View Full Order</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php if (isset($_GET['report_date']) && !$errorMessage && !empty($orders)): ?>
<script>
function printReport() {
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    
    // Build the print content
    var printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Daily Production List - <?php echo formatDate($reportDate, 'l, F j, Y'); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                .order-card { border: 1px solid #ddd; margin-bottom: 15px; padding: 15px; }
                .order-header { font-weight: bold; background-color: #f8f9fa; padding: 10px; margin: -15px -15px 10px -15px; }
                .items-list { margin: 10px 0; }
                .item { padding: 5px 0; border-bottom: 1px dotted #ccc; }
                .special-req { background-color: #fff3cd; padding: 10px; margin-top: 10px; border-left: 4px solid #ffc107; }
                @media print { 
                    body { margin: 0; } 
                    .no-print { display: none; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Sweet Creations - Daily Production List</h1>
                <h2><?php echo formatDate($reportDate, 'l, F j, Y'); ?></h2>
                <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
            </div>
            
            <?php foreach ($orders as $order): ?>
            <div class="order-card">
                <div class="order-header">
                    Order #<?php echo $order['order_id']; ?> - 
                    <?php echo htmlspecialchars($order['customer_name']); ?> 
                    (<?php echo htmlspecialchars($order['customer_phone']); ?>) -
                    Time: <?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any'; ?> -
                    Status: <?php echo htmlspecialchars($order['order_status']); ?>
                </div>
                
                <div class="items-list">
                    <strong>Items to Prepare:</strong>
                    <?php if (empty($order['items'])): ?>
                        <p>No items listed for this order.</p>
                    <?php else: ?>
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="item">
                            <strong><?php echo $item['quantity']; ?> x <?php echo htmlspecialchars($item['cake_name']); ?></strong>
                            <?php if (!empty($item['size'])): ?>
                                (Size: <?php echo htmlspecialchars($item['size']); ?>)
                            <?php endif; ?>
                            <?php if (!empty($item['customization'])): ?>
                                <br><em>Notes: <?php echo htmlspecialchars($item['customization']); ?></em>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($order['special_requirements'])): ?>
                <div class="special-req">
                    <strong>Special Requirements:</strong><br>
                    <?php echo nl2br(htmlspecialchars($order['special_requirements'])); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>
<?php endif; ?>

<?php 
// Include footer
include '../includes/footer.php'; 
?> 