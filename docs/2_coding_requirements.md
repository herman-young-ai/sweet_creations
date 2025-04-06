# Sweet Creations Coding Requirements Document

This document outlines specific coding requirements and guidelines for implementing the Sweet Creations cake shop management system. It is designed to be followed by an AI coding assistant to produce code that meets project requirements while remaining accessible to high school students.

## 1. General Coding Guidelines

### Simplicity First
- Write straightforward, easy-to-understand code
- Avoid complex design patterns or advanced techniques
- Prioritize readability over clever optimizations
- Include comments explaining code functionality

### PHP Coding Style
- Use descriptive variable and function names
- Indent with 4 spaces for readability
- Place opening braces on the same line as control structures
- Include semicolons at the end of each statement
- Use camelCase for functions and variables (e.g., `getUserData()`)

### File Organization
- Separate PHP logic from presentation where possible
- Keep files focused on a single responsibility
- Use include/require for common elements (header, footer, etc.)
- Begin each PHP file with `<?php` tag

## 2. Database Interaction

### Connection Management
```php
// Example database connection function
function connectDB() {
    try {
        $conn = new PDO("mysql:host=localhost;dbname=sweet_creations", "username", "password");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
        exit;
    }
}
```

### Query Execution
- Use PDO with prepared statements for all database operations
- Parameterize all user inputs to prevent SQL injection
- Use try-catch blocks for error handling
- Close database connections when done

```php
// Example query function
function getCustomerById($id) {
    $conn = connectDB();
    try {
        $stmt = $conn->prepare("SELECT * FROM CUSTOMERS WHERE customer_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
        return false;
    } finally {
        $conn = null;
    }
}
```

## 3. Authentication System

### User Login
```php
// Example login function
function loginUser($username, $password) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("SELECT * FROM USERS WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Start session and store user data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Update last login time
            updateLastLogin($user['user_id']);
            
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}
```

### Password Handling
```php
// Password hashing function (using SHA-256 with salt for simplicity)
function hashPassword($password) {
    $salt = bin2hex(random_bytes(16));
    $hash = hash('sha256', $password . $salt);
    return $salt . ':' . $hash;
}

// Password verification function
function verifyPassword($password, $storedHash) {
    list($salt, $hash) = explode(':', $storedHash);
    $calculatedHash = hash('sha256', $password . $salt);
    return $calculatedHash === $hash;
}
```

### Session Management
```php
// Session initialization (place at the top of pages requiring login)
session_start();

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}
```

## 4. Form Handling

### Input Validation
```php
// Example validation function
function validateCustomerForm($data) {
    $errors = [];
    
    // Name validation
    if (empty($data['full_name'])) {
        $errors['full_name'] = "Name is required";
    }
    
    // Phone validation (Mauritius format)
    if (empty($data['phone_number'])) {
        $errors['phone_number'] = "Phone number is required";
    } elseif (!preg_match('/^\+230 \d{4} \d{4}$/', $data['phone_number'])) {
        $errors['phone_number'] = "Phone number must be in format: +230 XXXX XXXX";
    }
    
    // Email validation
    if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    
    return $errors;
}
```

### Form Submission
```php
// Example form processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customerData = [
        'full_name' => trim($_POST['full_name']),
        'phone_number' => trim($_POST['phone_number']),
        'email' => trim($_POST['email']),
        'address' => trim($_POST['address']),
        'notes' => trim($_POST['notes'])
    ];
    
    $errors = validateCustomerForm($customerData);
    
    if (empty($errors)) {
        if (addCustomer($customerData)) {
            $successMessage = "Customer added successfully!";
        } else {
            $errorMessage = "Error adding customer.";
        }
    }
}
```

## 5. Page Layout and Templating

### Header Include
```php
// header.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Sweet Creations' : 'Sweet Creations'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Gentelella Theme -->
    <link href="assets/gentelella/css/custom.min.css" rel="stylesheet">
</head>
<body class="nav-md">
    <div class="container body">
        <div class="main_container">
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- top navigation -->
            <div class="top_nav">
                <!-- top navigation content -->
            </div>
            <!-- /top navigation -->
            
            <!-- page content -->
            <div class="right_col" role="main">
```

### Footer Include
```php
// footer.php
            </div>
            <!-- /page content -->
            
            <!-- footer content -->
            <footer>
                <div class="pull-right">
                    Sweet Creations Order Management System
                </div>
                <div class="clearfix"></div>
            </footer>
            <!-- /footer content -->
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="assets/js/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Gentelella -->
    <script src="assets/gentelella/js/custom.min.js"></script>
</body>
</html>
```

### Page Template Usage
```php
// Example usage in a page
<?php
session_start();
require_once 'includes/functions.php';
requireLogin();

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Dashboard</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <!-- Dashboard content here -->
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
```

## 6. CRUD Operations

### Create Record
```php
// Example customer creation
function addCustomer($data) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO CUSTOMERS (full_name, phone_number, email, address, notes, date_added) 
            VALUES (:full_name, :phone_number, :email, :address, :notes, NOW())
        ");
        
        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Add customer error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}
```

### Read Records
```php
// Example function to get all customers
function getAllCustomers() {
    $conn = connectDB();
    
    try {
        $stmt = $conn->query("SELECT * FROM CUSTOMERS ORDER BY full_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get customers error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

// Example search function
function searchCustomers($searchTerm) {
    $conn = connectDB();
    $searchTerm = "%$searchTerm%";
    
    try {
        $stmt = $conn->prepare("
            SELECT * FROM CUSTOMERS 
            WHERE full_name LIKE :term 
            OR phone_number LIKE :term 
            OR email LIKE :term
            ORDER BY full_name
        ");
        $stmt->bindParam(':term', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Search customers error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}
```

### Update Record
```php
// Example update function
function updateCustomer($id, $data) {
    $conn = connectDB();
    
    try {
        $stmt = $conn->prepare("
            UPDATE CUSTOMERS 
            SET full_name = :full_name,
                phone_number = :phone_number,
                email = :email,
                address = :address,
                notes = :notes
            WHERE customer_id = :id
        ");
        
        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Update customer error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}
```

### Delete Record
```php
// Example delete function
function deleteCustomer($id) {
    $conn = connectDB();
    
    try {
        // First check if customer has orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ORDERS WHERE customer_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            // Customer has orders, don't delete
            return false;
        }
        
        // Safe to delete
        $stmt = $conn->prepare("DELETE FROM CUSTOMERS WHERE customer_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Delete customer error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}
```

## 7. Report Generation

### Data Retrieval for Reports
```php
// Example report query
function getTomorrowsOrders() {
    $conn = connectDB();
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    
    try {
        $stmt = $conn->prepare("
            SELECT o.order_id, c.full_name, c.phone_number, 
                   o.delivery_time, o.delivery_address, o.order_status,
                   p.cake_name, oi.quantity, oi.size, oi.customization
            FROM ORDERS o
            JOIN CUSTOMERS c ON o.customer_id = c.customer_id
            JOIN ORDER_ITEMS oi ON o.order_id = oi.order_id
            JOIN PRODUCTS p ON oi.product_id = p.product_id
            WHERE o.delivery_date = :tomorrow
            ORDER BY o.delivery_time
        ");
        $stmt->bindParam(':tomorrow', $tomorrow, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get tomorrow's orders error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}
```

### PDF Generation
```php
// Example PDF generation using FPDF (simplified)
function generateOrdersPDF($orders) {
    require_once('libs/fpdf/fpdf.php');
    
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Sweet Creations - Orders Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Add table headers
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(20, 10, 'Order', 1);
    $pdf->Cell(60, 10, 'Customer', 1);
    $pdf->Cell(40, 10, 'Cake', 1);
    $pdf->Cell(30, 10, 'Time', 1);
    $pdf->Cell(40, 10, 'Status', 1);
    $pdf->Ln();
    
    // Add data rows
    $pdf->SetFont('Arial', '', 12);
    foreach ($orders as $order) {
        $pdf->Cell(20, 10, $order['order_id'], 1);
        $pdf->Cell(60, 10, $order['full_name'], 1);
        $pdf->Cell(40, 10, $order['cake_name'], 1);
        $pdf->Cell(30, 10, $order['delivery_time'], 1);
        $pdf->Cell(40, 10, $order['order_status'], 1);
        $pdf->Ln();
    }
    
    // Output PDF
    $pdf->Output('D', 'orders_report.pdf');
}
```

## 8. Displaying Data

### Table Display
```php
<!-- Example customer table -->
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($customers as $customer): ?>
        <tr>
            <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
            <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
            <td><?php echo htmlspecialchars($customer['email']); ?></td>
            <td>
                <a href="view_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-info btn-xs">View</a>
                <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
                <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
```

### Form Display
```php
<!-- Example customer form -->
<form method="post" action="">
    <div class="form-group">
        <label for="full_name">Name *</label>
        <input type="text" id="full_name" name="full_name" class="form-control" value="<?php echo isset($customer) ? htmlspecialchars($customer['full_name']) : ''; ?>" required>
        <?php if (isset($errors['full_name'])): ?>
            <span class="text-danger"><?php echo $errors['full_name']; ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="phone_number">Phone Number *</label>
        <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="+230 XXXX XXXX" value="<?php echo isset($customer) ? htmlspecialchars($customer['phone_number']) : ''; ?>" required>
        <?php if (isset($errors['phone_number'])): ?>
            <span class="text-danger"><?php echo $errors['phone_number']; ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($customer) ? htmlspecialchars($customer['email']) : ''; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="text-danger"><?php echo $errors['email']; ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" class="form-control"><?php echo isset($customer) ? htmlspecialchars($customer['address']) : ''; ?></textarea>
    </div>
    
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" class="form-control"><?php echo isset($customer) ? htmlspecialchars($customer['notes']) : ''; ?></textarea>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Save</button>
        <a href="customers.php" class="btn btn-default">Cancel</a>
    </div>
</form>
```

## 9. Currency Formatting

```php
// Format currency in MUR
function formatCurrency($amount) {
    return 'MUR ' . number_format($amount, 2, '.', ',');
}

// Example usage
$price = 1000000.50;
echo formatCurrency($price); // Outputs: MUR 1,000,000.50
```

## 10. Date Handling

```php
// Format date for display
function formatDate($mysqlDate) {
    $date = new DateTime($mysqlDate);
    return $date->format('d M Y'); // Example: 25 Dec 2023
}

// Format date for MySQL
function formatDateForMySQL($displayDate) {
    $date = DateTime::createFromFormat('d/m/Y', $displayDate);
    return $date ? $date->format('Y-m-d') : null;
}
```

## 11. Search Functionality

```php
// HTML search form
<form method="get" action="">
    <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search customers..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Search</button>
        </span>
    </div>
</form>

// PHP search handling
<?php
$customers = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $customers = searchCustomers($searchTerm);
} else {
    $customers = getAllCustomers();
}
?>
```

## 12. Error Handling

```php
// Display error/success messages
<?php if (isset($errorMessage)): ?>
    <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
    </div>
<?php endif; ?>

<?php if (isset($successMessage)): ?>
    <div class="alert alert-success">
        <?php echo $successMessage; ?>
    </div>
<?php endif; ?>

// Function for error logging
function logError($message, $file = 'error.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    error_log($logMessage, 3, $file);
}
```

## 13. Dashboard Implementation

```php
<!-- Dashboard example -->
<div class="row tile_count">
    <div class="col-md-4 tile_stats_count">
        <span class="count_top"><i class="fa fa-clock-o"></i> Today's Orders</span>
        <div class="count"><?php echo $todayOrdersCount; ?></div>
    </div>
    <div class="col-md-4 tile_stats_count">
        <span class="count_top"><i class="fa fa-user"></i> Total Customers</span>
        <div class="count"><?php echo $totalCustomers; ?></div>
    </div>
    <div class="col-md-4 tile_stats_count">
        <span class="count_top"><i class="fa fa-money"></i> Monthly Income</span>
        <div class="count"><?php echo formatCurrency($monthlyIncome); ?></div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Tomorrow's Orders</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <!-- Tomorrow's orders table goes here -->
            </div>
        </div>
    </div>
</div>
```

## 14. Installation Instructions

These should be included in a README.md file:

```markdown
# Sweet Creations Installation

## Requirements
- XAMPP (latest version)
- PHP 8.0+
- MySQL 8.0+
- Chrome-based browser

## Installation Steps
1. Install XAMPP on your computer (Windows or macOS)
2. Start Apache and MySQL services in XAMPP control panel
3. Navigate to phpMyAdmin (http://localhost/phpmyadmin)
4. Create a new database named 'sweet_creations'
5. Import the 'database.sql' file provided in the project
6. Copy all project files to the 'htdocs' folder in your XAMPP installation
7. Open your browser and navigate to http://localhost/sweet_creations
8. Log in with default credentials:
   - Username: admin
   - Password: admin123

## Default Access
- An admin user is created by default
- Sample products are pre-loaded
- Test customers are available for demonstration

## Troubleshooting
- If you encounter "Access denied" errors, check your MySQL user permissions
- For file permission issues, ensure the XAMPP has appropriate access to the project folder
- If pages don't load, verify that Apache and MySQL services are running
```