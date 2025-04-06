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
$pageTitle = 'Generate Reports';

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Select a Report to Generate</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <p>Choose one of the available reports below.</p>
                
                <div class="list-group">
                    <a href="report_daily_production.php" class="list-group-item list-group-item-action">
                        <i class="fa fa-calendar"></i> Daily Production List
                        <small class="d-block text-muted">View orders scheduled for delivery on a specific date.</small>
                    </a>
                    <a href="report_delivery_schedule.php" class="list-group-item list-group-item-action">
                         <i class="fa fa-truck"></i> Delivery Schedule
                         <small class="d-block text-muted">View orders within a specified delivery date range.</small>
                    </a>
                    <a href="report_monthly_sales.php" class="list-group-item list-group-item-action">
                         <i class="fa fa-line-chart"></i> Monthly Sales Summary
                         <small class="d-block text-muted">View total sales amount for a selected month.</small>
                    </a>
                     <!-- Add more report links here as needed -->
                </div>
                
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php 
// Include footer
include '../includes/footer.php'; 
?> 