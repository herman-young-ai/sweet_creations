<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Must include config and functions first
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Ensure user is logged in, otherwise redirect to login page
requireLogin();

// Set the page title for the header
$pageTitle = 'Dashboard'; 

// --- Fetch Dashboard Data --- //
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Get today's statistics
$todaysStats = getTodaysStats();
$dashboardStats = getDashboardStats();
$todaysOrders = getTodaysOrders(5);
$tomorrowsOrders = getTomorrowsOrders(5);

// Legacy data for existing sections
$todaysOrdersCount = $todaysStats['orders_count'];
$tomorrowsOrdersCount = getOrderCountForDate($tomorrow);
$totalCustomers = $dashboardStats['total_customers'] ?? getTotalCustomerCount();
$monthlyIncome = getCurrentMonthIncome();

// Include header
include 'includes/header.php';
?>

<!-- Quick Actions Panel -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="quick-actions-panel">
            <h3 class="quick-actions-title">Quick Actions</h3>
            <div class="quick-actions-grid">
                <a href="<?php echo BASE_URL; ?>orders/add_order.php" class="quick-action-btn quick-action-primary">
                    <i class="fa fa-plus-circle"></i>
                    <span>New Order</span>
                </a>
                <a href="<?php echo BASE_URL; ?>customers/add_customer.php" class="quick-action-btn quick-action-success">
                    <i class="fa fa-user-plus"></i>
                    <span>Add Customer</span>
                </a>
                <a href="<?php echo BASE_URL; ?>orders/orders.php" class="quick-action-btn quick-action-info">
                    <i class="fa fa-search"></i>
                    <span>View Orders</span>
                </a>
                <a href="<?php echo BASE_URL; ?>products/products.php" class="quick-action-btn quick-action-warning">
                    <i class="fa fa-birthday-cake"></i>
                    <span>Manage Products</span>
                </a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <a href="<?php echo BASE_URL; ?>admin/manage_users.php" class="quick-action-btn quick-action-danger">
                    <i class="fa fa-users"></i>
                    <span>Manage Staff</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>reports/reports.php" class="quick-action-btn quick-action-secondary">
                    <i class="fa fa-bar-chart"></i>
                    <span>Reports</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Today's Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="summary-card summary-card-orders">
            <div class="summary-card-content">
                <div class="summary-card-number"><?php echo $todaysStats['orders_count']; ?></div>
                <div class="summary-card-label">Today's Orders</div>
                <div class="summary-card-sublabel">For delivery today</div>
            </div>
            <div class="summary-card-icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card summary-card-pending">
            <div class="summary-card-content">
                <div class="summary-card-number"><?php echo $todaysStats['pending_count']; ?></div>
                <div class="summary-card-label">Pending Orders</div>
                <div class="summary-card-sublabel">Need preparation</div>
            </div>
            <div class="summary-card-icon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card summary-card-ready">
            <div class="summary-card-content">
                <div class="summary-card-number"><?php echo $todaysStats['ready_count']; ?></div>
                <div class="summary-card-label">Ready Orders</div>
                <div class="summary-card-sublabel">Ready for pickup</div>
            </div>
            <div class="summary-card-icon">
                <i class="fa fa-check-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="summary-card summary-card-revenue">
            <div class="summary-card-content">
                <div class="summary-card-number"><?php echo formatCurrency($todaysStats['revenue']); ?></div>
                <div class="summary-card-label">Today's Revenue</div>
                <div class="summary-card-sublabel">From today's orders</div>
            </div>
            <div class="summary-card-icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
</div>

<!-- Today's Orders Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="sweet-card">
            <h2 class="sweet-card-title">
                Today's Orders Schedule
                <a href="<?php echo BASE_URL; ?>orders/orders.php?search=<?php echo urlencode(date('d M')); ?>" class="view-all-link">View All</a>
            </h2>
            <?php if (!empty($todaysOrders)): ?>
            <table class="table table-striped table-orders">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Delivery Time</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($todaysOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any time'; ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <?php 
                            $statusClass = 'badge-secondary';
                            switch(strtolower($order['order_status'])) {
                                case 'new': $statusClass = 'badge-primary'; break;
                                case 'in_progress': $statusClass = 'badge-warning'; break;
                                case 'ready': $statusClass = 'badge-success'; break;
                                case 'delivered': $statusClass = 'badge-info'; break;
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($order['order_status'])); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">No orders scheduled for delivery today.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Tomorrow's Orders Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="sweet-card">
            <h2 class="sweet-card-title">
                Tomorrow's Orders Schedule
                <a href="<?php echo BASE_URL; ?>orders/orders.php?search=<?php echo urlencode(date('d M', strtotime('+1 day'))); ?>" class="view-all-link">View All</a>
            </h2>
            <?php if (!empty($tomorrowsOrders)): ?>
            <table class="table table-striped table-orders">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Delivery Time</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tomorrowsOrders as $order): ?>
                    <tr>
                        <td>#<?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any time'; ?></td>
                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                        <td>
                            <?php 
                            $statusClass = 'badge-secondary';
                            switch(strtolower($order['order_status'])) {
                                case 'new': $statusClass = 'badge-primary'; break;
                                case 'in_progress': $statusClass = 'badge-warning'; break;
                                case 'ready': $statusClass = 'badge-success'; break;
                                case 'delivered': $statusClass = 'badge-info'; break;
                            }
                            ?>
                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst(htmlspecialchars($order['order_status'])); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-muted">No orders scheduled for delivery tomorrow.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row">
    <!-- Column for Orders and Customers -->
    <div class="col-md-8">

        <!-- Recent Customers Card -->
        <div class="sweet-card">
             <h2 class="sweet-card-title">
                Recent Customers
                <a href="<?php echo BASE_URL; ?>customers/customers.php" class="view-all-link">View All</a>
            </h2>
            <?php 
            // --- Fetch Recent Customer Data (Example) --- //
            // This is placeholder data. You'll need to implement a function like getRecentCustomers(3)
            $recentCustomers = [
                ['name' => 'Priya Ramgoolam', 'order_count' => 3],
                ['name' => 'Vikash Dookhun', 'order_count' => 1],
                ['name' => 'Natacha Lam', 'order_count' => 2],
            ]; 
            // Replace above with: $recentCustomers = getRecentCustomers(3);
            ?>
            <?php if (empty($recentCustomers)): ?>
                <p>No recent customer data available.</p>
            <?php else: ?>
                <ul class="list-recent-customers">
                    <?php foreach($recentCustomers as $customer): ?>
                        <li><?php echo htmlspecialchars($customer['name']); ?> - <?php echo $customer['order_count']; ?> order(s)</li>
                    <?php endforeach; ?>
                </ul>
             <?php endif; ?>
                        </div>

                </div>
                
    <!-- Column for Monthly Sales -->
    <div class="col-md-4">
        <!-- Monthly Sales Card -->
        <div class="sweet-card">
            <h2 class="sweet-card-title">Monthly Sales</h2>
            <!-- Display text instead of chart -->
            <div class="monthly-sales-value">
                <?php echo formatCurrency($monthlyIncome); ?>
            </div>
            <p style="text-align: center; margin-top: 10px; font-size: 0.9em; color: #999;">
                Total income for <?php echo date('F Y'); ?>
            </p>
        </div>
    </div>
</div>

<?php 
// Include footer
include 'includes/footer.php'; 
?> 