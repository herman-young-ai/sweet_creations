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

                <?php if ($summary !== null && !$errorMessage): // Only show report if a month was submitted and valid ?>
                    <h3 class="mt-4">Sales Summary for: <?php echo date('F Y', strtotime($reportMonth . '-01')); ?></h3>
                    
                    <div class="row tile_count">
                        <div class="col-md-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-shopping-cart"></i> Total Orders (Completed/Shipped)</span>
                            <div class="count"><?php echo $summary['order_count']; ?></div>
                            <small>(Excludes cancelled orders)</small>
                        </div>
                         <div class="col-md-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-money"></i> Total Sales Amount</span>
                            <div class="count"><?php echo formatCurrency($summary['total_sales']); ?></div>
                             <small>(Based on order date, excludes cancelled)</small>
                        </div>
                    </div>
                    
                    <!-- Could add more details here later, e.g., list of orders -->
                    
                <?php elseif (isset($_GET['report_month']) && !$errorMessage): ?>
                     <div class="alert alert-info mt-3">No sales data found for the selected month.</div>
                <?php endif; ?>

            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php 
// Include footer
include '../includes/footer.php'; 
?> 