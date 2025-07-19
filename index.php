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

$todaysOrdersCount = getOrderCountForDate($today);
$tomorrowsOrdersCount = getOrderCountForDate($tomorrow); // For the widget tile
$totalCustomers = getTotalCustomerCount();
$monthlyIncome = getCurrentMonthIncome();
$upcomingOrders = getUpcomingOrders(5); // Get next 5 upcoming orders

// Include header
include 'includes/header.php';
?>

<div class="row">
    <!-- Column for Orders and Customers -->
    <div class="col-md-8">

        <!-- Tomorrow's Orders Card -->
        <div class="sweet-card">
            <h2 class="sweet-card-title">
                Tomorrow's Orders 
                <a href="<?php echo BASE_URL; ?>orders/orders.php?filter=tomorrow" class="view-all-link">View All</a>
            </h2>
            <?php if (empty($upcomingOrders)): // Using upcomingOrders for demo, adjust query if needed ?>
                <p>No orders scheduled for tomorrow.</p>
                                <?php else: ?>
                <table class="table table-striped table-orders">
                                        <thead>
                                            <tr>
                            <th>Order ID</th>
                                                <th>Customer</th>
                            <th>Cake Type</th> 
                            <th>Delivery Time</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($upcomingOrders as $order): ?>
                                            <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo isset($order['items'][0]['product_name']) ? htmlspecialchars($order['items'][0]['product_name']) : 'N/A'; // Example: Get first item name ?></td> 
                            <td><?php echo !empty($order['delivery_time']) ? date("g:i A", strtotime($order['delivery_time'])) : 'Any'; ?></td>
                            <td>
                                <?php 
                                // Example status mapping - Adjust based on your actual statuses
                                $statusClass = 'badge-pending'; // Default
                                if (strtolower($order['order_status']) == 'ready') $statusClass = 'badge-ready';
                                if (strtolower($order['order_status']) == 'urgent') $statusClass = 'badge-urgent'; // Add an 'Urgent' status?
                                // Add more cases as needed
                                ?>
                                <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                            </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>

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