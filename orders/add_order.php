<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// --- Form Processing --- //
$errors = [];
$successMessage = null;
$formData = [
    'customer_id' => '',
    'phone' => '', // Placeholder, fetch dynamically later?
    'product_id' => '',
    'size' => '',
    'quantity' => 1,
    'customization' => '',
    'delivery_date' => '',
    'delivery_time' => '',
    'payment_status' => 'Pending', // Default
    'delivery_address' => '', // Add this field
    'special_requirements' => '' // Add this field
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Retrieve form data --- 
    $formData['customer_id'] = trim($_POST['customer_id'] ?? '');
    $formData['product_id'] = trim($_POST['product_id'] ?? '');
    $formData['size'] = trim($_POST['size'] ?? '');
    $formData['quantity'] = trim($_POST['quantity'] ?? 1);
    $formData['customization'] = trim($_POST['customization'] ?? '');
    $formData['delivery_date'] = trim($_POST['delivery_date'] ?? '');
    $formData['delivery_time'] = trim($_POST['delivery_time'] ?? '');
    $formData['payment_status'] = trim($_POST['payment_status'] ?? 'Pending');
    // Need delivery address and special requirements from form
    $formData['delivery_address'] = trim($_POST['delivery_address'] ?? '');
    $formData['special_requirements'] = trim($_POST['special_requirements'] ?? '');
    

    // --- Validation --- //
    if (empty($formData['customer_id'])) {
        $errors['customer_id'] = "Customer is required.";
    }
    if (empty($formData['product_id'])) {
        $errors['product_id'] = "Product Type is required.";
    }
    if (!filter_var($formData['quantity'], FILTER_VALIDATE_INT) || $formData['quantity'] < 1) {
        $errors['quantity'] = "Quantity must be at least 1.";
    }
     if (empty($formData['delivery_date'])) {
        $errors['delivery_date'] = "Delivery Date is required.";
    } elseif (strtotime($formData['delivery_date']) === false) {
        $errors['delivery_date'] = "Invalid Delivery Date format.";
    }
     // Add validation for delivery_address if required
     if (empty($formData['delivery_address'])) {
        $errors['delivery_address'] = "Delivery Address is required.";
    }

    // --- If No Errors, Process Order --- //
    if (empty($errors)) {
        $conn = connectDB();
        if ($conn) {
            try {
                $conn->beginTransaction();

                // Fetch product details (for price)
                $product = getProductById($formData['product_id']);
                if (!$product) throw new Exception("Invalid product selected.");
                $itemPrice = $product['base_price'];
                $totalAmount = $itemPrice * $formData['quantity'];
                
                // Prepare Order Header Data
                $orderHeaderData = [
                    'customer_id' => $formData['customer_id'],
                    'user_id' => $_SESSION['user_id'], 
                    'delivery_date' => $formData['delivery_date'],
                    'delivery_time' => !empty($formData['delivery_time']) ? $formData['delivery_time'] : null,
                    'delivery_address' => $formData['delivery_address'], 
                    'total_amount' => $totalAmount,
                    'special_requirements' => $formData['special_requirements'], 
                    'order_status' => $formData['payment_status'], // Using payment_status for order_status initially?
                    'is_paid' => ($formData['payment_status'] == 'Paid') ? 1 : 0 // Example mapping
                ];

                $newOrderId = addOrderHeader($conn, $orderHeaderData);

                if ($newOrderId) {
                    // Prepare Order Item Data (only one item in this version)
                    $itemData = [
                        'product_id' => $formData['product_id'],
                        'quantity' => $formData['quantity'],
                        'price' => $itemPrice,
                        'size' => $formData['size'],
                        'customization' => $formData['customization']
                    ];

                    if (addOrderItem($conn, $newOrderId, $itemData)) {
                        $conn->commit();
                        header('Location: orders.php?status=added&id=' . $newOrderId);
                        exit;
                    } else {
                        $conn->rollBack();
                        $errors['database'] = "Error adding order item. Order was not saved.";
                    }
                } else {
                    $conn->rollBack();
                    $errors['database'] = "Error saving order header. Order was not saved.";
                }

            } catch (Exception $e) {
                if ($conn->inTransaction()) $conn->rollBack();
                error_log("Save order error: " . $e->getMessage());
                $errors['database'] = "A database error occurred: " . $e->getMessage();
            } finally {
                 $conn = null;
            }
        } else {
             $errors['database'] = "Database connection failed. Cannot save order.";
        }
    }
}

// --- Page Setup & Data Loading --- //
$pageTitle = 'Create New Order';
$customers = getAllCustomers('full_name', 'ASC'); // Order by name for dropdown
$products = getAllProducts('cake_name', 'ASC'); 

include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        
        <form action="add_order.php" method="post" id="add_order_form" novalidate>
            <div class="sweet-card">
                <h2 class="sweet-card-title">Create New Order</h2>

                <?php if (!empty($errors['database'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($errors['database']); ?></div>
                <?php endif; ?>

                <!-- Customer Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Customer Information</h3>
                    <div class="row">
                        <div class="col-md-5 form-group <?php echo isset($errors['customer_id']) ? 'has-error' : ''; ?>">
                            <label for="customer_id">Customer: *</label>
                            <select name="customer_id" id="customer_id" class="form-control" required>
                                <option value="">-- Search or Select Customer --</option>
                                <?php foreach ($customers as $customer): ?>
                                    <option value="<?php echo $customer['customer_id']; ?>" <?php echo ($formData['customer_id'] == $customer['customer_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($customer['full_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                             <?php if (isset($errors['customer_id'])): ?><span class="help-block"><?php echo $errors['customer_id']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>&nbsp;</label> <!-- Spacer for alignment -->
                            <a href="../customers/add_customer.php" target="_blank" class="btn btn-add-new form-control">Add New</a>
                        </div>
                        <div class="col-md-5 form-group">
                            <label for="phone">Phone:</label>
                            <input type="text" name="phone" id="phone" class="form-control" readonly placeholder="(Select customer to view)" value="<?php echo htmlspecialchars($formData['phone']); ?>">
                            <!-- Phone should be loaded via JS after customer selection -->
                        </div>
                    </div>
                </div>

                <!-- Order Details Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Order Details</h3>
                    <div class="row">
                        <div class="col-md-4 form-group <?php echo isset($errors['product_id']) ? 'has-error' : ''; ?>">
                            <label for="product_id">Product Type: *</label>
                            <select name="product_id" id="product_id" class="form-control" required>
                                <option value="">-- Select Product --</option>
                                 <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>" <?php echo ($formData['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($product['cake_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                             <?php if (isset($errors['product_id'])): ?><span class="help-block"><?php echo $errors['product_id']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="size">Size:</label>
                            <select name="size" id="size" class="form-control">
                                <option value="" <?php echo ($formData['size'] == '') ? 'selected' : ''; ?>>Standard</option>
                                <option value="6 Inch" <?php echo ($formData['size'] == '6 Inch') ? 'selected' : ''; ?>>6 Inch</option>
                                <option value="8 Inch" <?php echo ($formData['size'] == '8 Inch') ? 'selected' : ''; ?>>8 Inch</option>
                                <option value="10 Inch" <?php echo ($formData['size'] == '10 Inch') ? 'selected' : ''; ?>>10 Inch</option>
                                <!-- Add more sizes as needed, maybe dynamically from product later -->
                            </select>
                        </div>
                        <div class="col-md-2 form-group <?php echo isset($errors['quantity']) ? 'has-error' : ''; ?>">
                            <label for="quantity">Quantity: *</label>
                            <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($formData['quantity']); ?>" min="1" class="form-control" required>
                            <?php if (isset($errors['quantity'])): ?><span class="help-block"><?php echo $errors['quantity']; ?></span><?php endif; ?>
                        </div>
                         <div class="col-md-4 form-group">
                            <label for="customization">Customization:</label>
                            <input type="text" name="customization" id="customization" placeholder="(Optional)" class="form-control" value="<?php echo htmlspecialchars($formData['customization']); ?>">
                        </div>
                    </div>
                     <!-- Add row for Special Requirements -->
                    <div class="row">
                         <div class="col-md-12 form-group">
                            <label for="special_requirements">Special Requirements / Notes:</label>
                            <textarea name="special_requirements" id="special_requirements" class="form-control" rows="2" placeholder="(Optional, e.g., message on cake, allergies)"><?php echo htmlspecialchars($formData['special_requirements']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information Section -->
                <div class="form-section">
                    <h3 class="form-section-title">Delivery Information</h3>
                    <div class="row">
                        <div class="col-md-4 form-group <?php echo isset($errors['delivery_date']) ? 'has-error' : ''; ?>">
                            <label for="delivery_date">Delivery Date: *</label>
                             <input type="date" name="delivery_date" id="delivery_date" class="form-control" required value="<?php echo htmlspecialchars($formData['delivery_date']); ?>">
                             <?php if (isset($errors['delivery_date'])): ?><span class="help-block"><?php echo $errors['delivery_date']; ?></span><?php endif; ?>
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="delivery_time">Delivery Time:</label>
                            <input type="time" name="delivery_time" id="delivery_time" class="form-control" value="<?php echo htmlspecialchars($formData['delivery_time']); ?>">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="payment_status">Payment Status:</label>
                             <select name="payment_status" id="payment_status" class="form-control">
                                <option value="Pending" <?php echo ($formData['payment_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Paid" <?php echo ($formData['payment_status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                <!-- Add other relevant statuses -->
                            </select>
                        </div>
                    </div>
                    <!-- Add row for Delivery Address -->
                     <div class="row">
                         <div class="col-md-12 form-group <?php echo isset($errors['delivery_address']) ? 'has-error' : ''; ?>">
                            <label for="delivery_address">Delivery Address: *</label>
                            <textarea name="delivery_address" id="delivery_address" class="form-control" rows="3" required placeholder="Enter full delivery address"><?php echo htmlspecialchars($formData['delivery_address']); ?></textarea>
                            <?php if (isset($errors['delivery_address'])): ?><span class="help-block"><?php echo $errors['delivery_address']; ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="orders.php" class="btn btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-save-order">Save Order</button>
                </div>

            </div> <!-- /sweet-card -->
        </form>

    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 