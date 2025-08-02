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
$pageTitle = 'Monthly Sales Summary Report';

// Get month from form, default to current month
$reportMonth = isset($_GET['report_month']) ? $_GET['report_month'] : date('Y-m');
$summary = null;
$analytics = null;
$errorMessage = '';

// Validate month format if provided
if (isset($_GET['report_month'])) {
    // Validate YYYY-MM format
    if (!preg_match('/^\d{4}-\d{2}$/', $reportMonth) || strtotime($reportMonth . '-01') === false) {
        $errorMessage = "Invalid month format provided. Please use YYYY-MM.";
        $reportMonth = date('Y-m'); // Reset to default
    } else {
        // Fetch summary for the selected month
        $summary = getMonthlySalesSummary($reportMonth);
        $analytics = getDetailedMonthlySalesAnalytics($reportMonth);
    }
}

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Monthly Sales Summary</h2>
                 <ul class="nav navbar-right panel_toolbox">
                    <li><a href="reports.php" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to Reports</a></li>
                    <?php if ($summary !== null && $analytics !== null && !$errorMessage): ?>
                    <li><button onclick="printMonthlySales()" class="btn btn-info btn-sm"><i class="fa fa-print"></i> Print Report</button></li>
                    <?php endif; ?>
                 </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <!-- Month Selection Form -->
                <form action="report_monthly_sales.php" method="get" class="form-inline mb-4">
                    <div class="form-group mr-2">
                        <label for="report_month">Select Month:</label>
                        <input type="month" id="report_month" name="report_month" class="form-control ml-2" value="<?php echo htmlspecialchars($reportMonth); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </form>
                <!-- /Month Selection Form -->

                <?php if ($errorMessage): ?>
                     <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
                <?php endif; ?>

                <?php if ($summary !== null && $analytics !== null && !$errorMessage): ?>
                    <h3 class="mt-4">Detailed Sales Analytics for: <?php echo date('F Y', strtotime($reportMonth . '-01')); ?></h3>
                    
                    <!-- Key Performance Indicators -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="sweet-card summary-card-orders">
                                <div class="summary-card-content">
                                    <div class="summary-card-number"><?php echo (int)$analytics['summary']['total_orders']; ?></div>
                                    <div class="summary-card-label">Total Orders</div>
                                    <div class="summary-card-sublabel">Excluding cancelled</div>
                                </div>
                                <div class="summary-card-icon">
                                    <i class="fa fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sweet-card summary-card-revenue">
                                <div class="summary-card-content">
                                    <div class="summary-card-number"><?php echo formatCurrency($analytics['summary']['total_revenue']); ?></div>
                                    <div class="summary-card-label">Total Revenue</div>
                                    <div class="summary-card-sublabel">Month total</div>
                                </div>
                                <div class="summary-card-icon">
                                    <i class="fa fa-money"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sweet-card summary-card-pending">
                                <div class="summary-card-content">
                                    <div class="summary-card-number"><?php echo formatCurrency($analytics['summary']['avg_order_value']); ?></div>
                                    <div class="summary-card-label">Avg Order Value</div>
                                    <div class="summary-card-sublabel">Per order</div>
                                </div>
                                <div class="summary-card-icon">
                                    <i class="fa fa-calculator"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="sweet-card summary-card-ready">
                                <div class="summary-card-content">
                                    <div class="summary-card-number"><?php echo (int)$analytics['summary']['unique_customers']; ?></div>
                                    <div class="summary-card-label">Unique Customers</div>
                                    <div class="summary-card-sublabel">Different customers</div>
                                </div>
                                <div class="summary-card-icon">
                                    <i class="fa fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Month-over-Month Comparison -->
                    <?php if (!empty($analytics['previous_month']['prev_revenue'])): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="sweet-card">
                                <h4 class="sweet-card-title">Month-over-Month Comparison</h4>
                                <?php 
                                $currentRevenue = (float)$analytics['summary']['total_revenue'];
                                $prevRevenue = (float)$analytics['previous_month']['prev_revenue'];
                                $revenueChange = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
                                $revenueChangeClass = $revenueChange >= 0 ? 'text-success' : 'text-danger';
                                $revenueIcon = $revenueChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                
                                $currentOrders = (int)$analytics['summary']['total_orders'];
                                $prevOrders = (int)$analytics['previous_month']['prev_orders'];
                                $ordersChange = $prevOrders > 0 ? (($currentOrders - $prevOrders) / $prevOrders) * 100 : 0;
                                $ordersChangeClass = $ordersChange >= 0 ? 'text-success' : 'text-danger';
                                $ordersIcon = $ordersChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Revenue Change:</strong> 
                                        <span class="<?php echo $revenueChangeClass; ?>">
                                            <i class="fa <?php echo $revenueIcon; ?>"></i>
                                            <?php echo number_format(abs($revenueChange), 1); ?>%
                                        </span>
                                        </p>
                                        <small>Previous month: <?php echo formatCurrency($prevRevenue); ?></small>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Orders Change:</strong> 
                                        <span class="<?php echo $ordersChangeClass; ?>">
                                            <i class="fa <?php echo $ordersIcon; ?>"></i>
                                            <?php echo number_format(abs($ordersChange), 1); ?>%
                                        </span>
                                        </p>
                                        <small>Previous month: <?php echo $prevOrders; ?> orders</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Order Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="sweet-card">
                                <h4 class="sweet-card-title">Order Statistics</h4>
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Highest Order Value:</strong></td>
                                        <td><?php echo formatCurrency($analytics['summary']['max_order_value']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lowest Order Value:</strong></td>
                                        <td><?php echo formatCurrency($analytics['summary']['min_order_value']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Average Order Value:</strong></td>
                                        <td><?php echo formatCurrency($analytics['summary']['avg_order_value']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Orders per Customer:</strong></td>
                                        <td><?php echo number_format($analytics['summary']['total_orders'] / max(1, $analytics['summary']['unique_customers']), 1); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sweet-card">
                                <h4 class="sweet-card-title">Order Status Breakdown</h4>
                                <?php if (!empty($analytics['status_breakdown'])): ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($analytics['status_breakdown'] as $status): ?>
                                        <tr>
                                            <td>
                                                <?php 
                                                $statusClass = 'badge-secondary';
                                                switch(strtolower($status['order_status'])) {
                                                    case 'new': $statusClass = 'badge-primary'; break;
                                                    case 'in_progress': $statusClass = 'badge-warning'; break;
                                                    case 'ready': $statusClass = 'badge-success'; break;
                                                    case 'delivered': $statusClass = 'badge-info'; break;
                                                    case 'cancelled': $statusClass = 'badge-danger'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($status['order_status'])); ?></span>
                                            </td>
                                            <td><?php echo (int)$status['count']; ?></td>
                                            <td><?php echo formatCurrency($status['revenue']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <?php else: ?>
                                <p class="text-muted">No order status data available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Top Customers -->
                    <?php if (!empty($analytics['top_customers'])): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="sweet-card">
                                <h4 class="sweet-card-title">Top Customers This Month</h4>
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Customer Name</th>
                                            <th>Orders</th>
                                            <th>Total Spent</th>
                                            <th>Avg per Order</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($analytics['top_customers'] as $customer): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                            <td><?php echo (int)$customer['order_count']; ?></td>
                                            <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                                            <td><?php echo formatCurrency($customer['total_spent'] / $customer['order_count']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Daily Sales Trend -->
                    <?php if (!empty($analytics['daily_trend'])): ?>
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="sweet-card">
                                <h4 class="sweet-card-title">Daily Sales Trend</h4>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Orders</th>
                                                <th>Revenue</th>
                                                <th>Avg per Order</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($analytics['daily_trend'] as $day): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($day['order_date'])); ?></td>
                                                <td><?php echo (int)$day['orders']; ?></td>
                                                <td><?php echo formatCurrency($day['revenue']); ?></td>
                                                <td><?php echo formatCurrency($day['revenue'] / max(1, $day['orders'])); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                <?php elseif (isset($_GET['report_month']) && !$errorMessage): ?>
                     <div class="alert alert-info mt-3">No sales data found for the selected month.</div>
                <?php endif; ?>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php if ($summary !== null && $analytics !== null && !$errorMessage): ?>
<script>
function printMonthlySales() {
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    
    // Build the print content
    var printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Monthly Sales Summary - <?php echo date('F Y', strtotime($reportMonth . '-01')); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                .kpi-section { display: flex; justify-content: space-around; margin-bottom: 30px; }
                .kpi-card { text-align: center; border: 1px solid #ddd; padding: 15px; margin: 0 10px; flex: 1; }
                .kpi-number { font-size: 24px; font-weight: bold; color: #333; }
                .kpi-label { font-size: 14px; color: #666; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .section { margin-bottom: 30px; }
                .section h3 { border-bottom: 1px solid #ddd; padding-bottom: 5px; }
                @media print { 
                    body { margin: 0; font-size: 12px; } 
                    .no-print { display: none; }
                    table { font-size: 11px; }
                    .kpi-section { flex-wrap: wrap; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Sweet Creations - Monthly Sales Summary</h1>
                <h2><?php echo date('F Y', strtotime($reportMonth . '-01')); ?></h2>
                <p>Generated on: <?php echo date('F j, Y g:i A'); ?></p>
            </div>
            
            <!-- Key Performance Indicators -->
            <div class="kpi-section">
                <div class="kpi-card">
                    <div class="kpi-number"><?php echo (int)$analytics['summary']['total_orders']; ?></div>
                    <div class="kpi-label">Total Orders</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-number"><?php echo formatCurrency($analytics['summary']['total_revenue']); ?></div>
                    <div class="kpi-label">Total Revenue</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-number"><?php echo formatCurrency($analytics['summary']['avg_order_value']); ?></div>
                    <div class="kpi-label">Avg Order Value</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-number"><?php echo (int)$analytics['summary']['unique_customers']; ?></div>
                    <div class="kpi-label">Unique Customers</div>
                </div>
            </div>
            
            <!-- Order Statistics -->
            <div class="section">
                <h3>Order Statistics</h3>
                <table>
                    <tr>
                        <td><strong>Highest Order Value:</strong></td>
                        <td><?php echo formatCurrency($analytics['summary']['max_order_value']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Lowest Order Value:</strong></td>
                        <td><?php echo formatCurrency($analytics['summary']['min_order_value']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Average Order Value:</strong></td>
                        <td><?php echo formatCurrency($analytics['summary']['avg_order_value']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Orders per Customer:</strong></td>
                        <td><?php echo number_format($analytics['summary']['total_orders'] / max(1, $analytics['summary']['unique_customers']), 1); ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Order Status Breakdown -->
            <?php if (!empty($analytics['status_breakdown'])): ?>
            <div class="section">
                <h3>Order Status Breakdown</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Count</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['status_breakdown'] as $status): ?>
                        <tr>
                            <td><?php echo ucfirst(htmlspecialchars($status['order_status'])); ?></td>
                            <td><?php echo (int)$status['count']; ?></td>
                            <td><?php echo formatCurrency($status['revenue']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <!-- Top Customers -->
            <?php if (!empty($analytics['top_customers'])): ?>
            <div class="section">
                <h3>Top Customers This Month</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Avg per Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($analytics['top_customers'] as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                            <td><?php echo (int)$customer['order_count']; ?></td>
                            <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                            <td><?php echo formatCurrency($customer['total_spent'] / $customer['order_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
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