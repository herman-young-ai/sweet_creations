<?php

// Common functions will be added here.

require_once 'config.php';

/**
 * Establishes a connection to the database using PDO.
 *
 * @return PDO|null Returns a PDO connection object on success, or null on failure.
 */
function connectDB() {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // In a real application, log this error and show a user-friendly message.
        // For simplicity here, we'll just output the error.
        error_log("Database Connection Error: " . $e->getMessage()); // Log the error
        // echo "Database connection failed: " . $e->getMessage(); // Avoid echoing detailed errors in production
        echo "Error connecting to the database. Please check configuration or contact support.";
        exit; // Stop script execution on connection failure
    }
}

// --- Authentication Functions --- //

/**
 * Updates the last login timestamp for a user.
 *
 * @param int $userId The ID of the user.
 * @return bool True on success, false on failure.
 */
function updateLastLogin($userId) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare("UPDATE USERS SET last_login = NOW() WHERE user_id = :id");
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Update last login error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null; // Close connection
    }
}

/**
 * Attempts to log in a user by comparing plain text passwords.
 *
 * @param string $username The username provided.
 * @param string $password The password provided.
 * @return bool True on successful login, false otherwise.
 */
function loginUser($username, $password) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        // Prepare statement to find user by username
        $stmt = $conn->prepare("SELECT user_id, username, password, full_name, role FROM USERS WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and password is correct (plain text comparison)
        if ($user && $password === $user['password']) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            // Store user data in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time(); // Optional: store login time for inactivity timeout

            // Update last login time in the database
            updateLastLogin($user['user_id']);

            return true; // Login successful
        }
        
        return false; // User not found or password incorrect
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null; // Close connection
    }
}

/**
 * Checks if the current user is logged in.
 *
 * @return bool True if logged in, false otherwise.
 */
function isLoggedIn() {
    // Check if the user_id session variable is set
    return isset($_SESSION['user_id']);
}

/**
 * Redirects the user to the login page if they are not logged in.
 * Ensures BASE_URL is defined before using it for redirection.
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Define BASE_URL if not already defined (necessary if called before header.php)
        if (!defined('BASE_URL')) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $scriptName = $_SERVER['SCRIPT_NAME'];
            $projectName = 'sweet_creations'; 
            $basePath = substr($scriptName, 0, strpos($scriptName, $projectName) + strlen($projectName));
            if(empty($basePath) || strpos($scriptName, $projectName) === false){
                $basePath = '/' . $projectName; 
            }
            define('BASE_URL', $protocol . '://' . $host . $basePath . '/');
        }
        header('Location: ' . BASE_URL . 'login.php');
        exit; // Stop script execution after redirect
    }
    // Optional: Add inactivity timeout check here
}


// --- Other Helper Functions can go below --- //

/**
 * Formats a number as currency in Mauritian Rupees (MUR).
 *
 * @param float|null $amount The amount to format.
 * @param string $default The string to return if amount is null or not numeric (default: 'N/A').
 * @return string The formatted currency string (e.g., "MUR 1,200.50").
 */
function formatCurrency($amount, $default = 'N/A') {
    if ($amount === null || !is_numeric($amount)) {
        return $default;
    }
    return 'MUR ' . number_format((float)$amount, 2, '.', ',');
}

/**
 * Formats a MySQL date/datetime string for display.
 *
 * @param string|null $mysqlDateTime The date string from database (YYYY-MM-DD or YYYY-MM-DD HH:MM:SS).
 * @param string $format The desired output format (default: 'd M Y').
 * @param string $default The string to return if date is null or invalid (default: 'N/A').
 * @return string The formatted date string.
 */
function formatDate($mysqlDateTime, $format = 'd M Y', $default = 'N/A') {
    if (empty($mysqlDateTime) || $mysqlDateTime === '0000-00-00' || $mysqlDateTime === '0000-00-00 00:00:00') {
        return $default;
    }
    try {
        $date = new DateTime($mysqlDateTime);
        return $date->format($format);
    } catch (Exception $e) {
        // Log error if needed
        // error_log("Format date error: " . $e->getMessage());
        return $default; // Return default on formatting error
    }
}

// --- Customer Management Functions --- //

/**
 * Fetches all customers from the database.
 *
 * @param string $orderBy Column to order results by (default: full_name).
 * @param string $orderDir Order direction ('ASC' or 'DESC', default: 'ASC').
 * @return array An array of customer records, or an empty array on failure/no results.
 */
function getAllCustomers($orderBy = 'full_name', $orderDir = 'ASC') {
    $conn = connectDB();
    if (!$conn) return [];

    // Basic validation for order parameters
    $validColumns = ['customer_id', 'full_name', 'phone_number', 'email', 'date_added'];
    $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'full_name';
    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

    try {
        $sql = "SELECT customer_id, full_name, phone_number, email, date_added FROM CUSTOMERS ORDER BY " . $orderBy . " " . $orderDir;
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get all customers error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Validates customer form data.
 *
 * @param array $data Array containing customer data (e.g., from $_POST).
 * @return array An array of error messages. Empty if validation passes.
 */
function validateCustomerForm($data) {
    $errors = [];

    // Name validation (Required with enhanced validation)
    $full_name = trim($data['full_name']);
    if (empty($full_name)) {
        $errors['full_name'] = "Full Name is required.";
    } elseif (strlen($full_name) < 2) {
        $errors['full_name'] = "Full Name must be at least 2 characters long.";
    } elseif (strlen($full_name) > 100) {
        $errors['full_name'] = "Full Name must not exceed 100 characters.";
    } elseif (!preg_match('/^[a-zA-Z\s\-\'\.]+$/', $full_name)) {
        $errors['full_name'] = "Full Name can only contain letters, spaces, hyphens, apostrophes, and periods.";
    } elseif (preg_match('/^\s+|\s+$/', $data['full_name'])) {
        // Check for leading/trailing spaces in original data (before trim)
        $errors['full_name'] = "Full Name cannot start or end with spaces.";
    } elseif (preg_match('/\s{2,}/', $full_name)) {
        $errors['full_name'] = "Full Name cannot contain multiple consecutive spaces.";
    }

    // Phone validation (Required, Mauritius format)
    $phone = trim($data['phone_number']);
    if (empty($phone)) {
        $errors['phone_number'] = "Phone Number is required.";
    } elseif (strlen($phone) < 14 || strlen($phone) > 14) {
        // Mauritius format should be exactly 14 characters: +230 XXXX XXXX  
        $errors['phone_number'] = "Phone Number must be exactly 14 characters in format +230 XXXX XXXX";
    } elseif (!preg_match('/^\+230\s\d{4}\s\d{4}$/', $phone)) {
        // Regex: Starts with +230, space, 4 digits, space, 4 digits, ends.
        $errors['phone_number'] = "Phone Number must be in the format: +230 XXXX XXXX (e.g., +230 5123 4567)";
    } elseif (preg_match('/^\s+|\s+$/', $data['phone_number'])) {
        // Check for leading/trailing spaces in original data (before trim)
        $errors['phone_number'] = "Phone Number cannot start or end with spaces.";
    } elseif (!preg_match('/^\+230\s[5-9]\d{3}\s\d{4}$/', $phone)) {
        // Validate that it starts with 5, 6, 7, 8, or 9 (valid Mauritius mobile prefixes)
        $errors['phone_number'] = "Phone Number must start with 5, 6, 7, 8, or 9 after +230 (mobile numbers only)";
    }

    // Email validation (Optional, but must be valid if provided)
    $email = trim($data['email']);
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid Email format.";
    }
    
    // Address validation (Optional - no specific rules here)
    // Notes validation (Optional - no specific rules here)

    return $errors;
}

/**
 * Adds a new customer to the database.
 *
 * @param array $data Array containing validated customer data.
 * @return int|false The ID of the newly inserted customer on success, or false on failure.
 */
function addCustomer($data) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "INSERT INTO CUSTOMERS (full_name, phone_number, email, address, notes, date_added)
                VALUES (:full_name, :phone_number, :email, :address, :notes, NOW())";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $conn->lastInsertId(); // Return the ID of the new customer
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Add customer error: " . $e->getMessage());
        // Check for duplicate entry errors (e.g., if phone or email were unique)
        // if ($e->errorInfo[1] == 1062) { /* Handle duplicate entry */ }
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Fetches a single customer record by their ID.
 *
 * @param int $id The ID of the customer to fetch.
 * @return array|false An associative array of the customer's data, or false if not found or error.
 */
function getCustomerById($id) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "SELECT * FROM CUSTOMERS WHERE customer_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns false if no record found
    } catch (PDOException $e) {
        error_log("Get customer by ID error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Updates an existing customer record in the database.
 *
 * @param int $id The ID of the customer to update.
 * @param array $data Array containing validated customer data.
 * @return bool True on success, false on failure.
 */
function updateCustomer($id, $data) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "UPDATE CUSTOMERS SET 
                    full_name = :full_name, 
                    phone_number = :phone_number, 
                    email = :email, 
                    address = :address, 
                    notes = :notes 
                WHERE customer_id = :id";
        $stmt = $conn->prepare($sql);

        // Bind parameters
        $stmt->bindParam(':full_name', $data['full_name'], PDO::PARAM_STR);
        $stmt->bindParam(':phone_number', $data['phone_number'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $data['email'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $data['address'], PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Update customer error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Deletes a customer from the database after checking for related orders.
 *
 * @param int $id The ID of the customer to delete.
 * @return string Returns 'deleted' on success, 'has_orders' if deletion is blocked due to existing orders, or 'error' on failure.
 */
function deleteCustomer($id) {
    $conn = connectDB();
    if (!$conn) return 'error';

    try {
        // 1. Check if the customer has any orders
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM ORDERS WHERE customer_id = :id");
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $orderCount = $stmtCheck->fetchColumn();

        if ($orderCount > 0) {
            // Customer has orders, cannot delete
            return 'has_orders';
        }

        // 2. If no orders, proceed with deletion
        $stmtDelete = $conn->prepare("DELETE FROM CUSTOMERS WHERE customer_id = :id");
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmtDelete->execute()) {
             return 'deleted'; // Deletion successful
        } else {
            return 'error'; // Deletion failed
        }

    } catch (PDOException $e) {
        error_log("Delete customer error: " . $e->getMessage());
        return 'error';
    } finally {
        $conn = null;
    }
}

/**
 * Searches for customers based on a search term.
 * Matches against full_name, phone_number, or email.
 *
 * @param string $searchTerm The term to search for.
 * @return array An array of matching customer records, or empty array on failure/no results.
 */
function searchCustomers($searchTerm) {
    $conn = connectDB();
    if (!$conn) return [];

    // Trim and prepare search term
    $searchTerm = trim($searchTerm);
    if (empty($searchTerm)) return [];
    
    // Add wildcards for LIKE search
    $searchPattern = '%' . strtolower($searchTerm) . '%';

    try {
        $sql = "SELECT customer_id, full_name, phone_number, email, date_added 
                FROM CUSTOMERS 
                WHERE LOWER(full_name) LIKE ? 
                   OR LOWER(phone_number) LIKE ? 
                   OR LOWER(email) LIKE ?
                   OR LOWER(address) LIKE ?
                ORDER BY full_name ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Search customers error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}


// --- Product Management Functions --- //

/**
 * Fetches all products from the database.
 *
 * @param string $orderBy Column to order results by (default: cake_name).
 * @param string $orderDir Order direction ('ASC' or 'DESC', default: 'ASC').
 * @return array An array of product records, or an empty array on failure/no results.
 */
function getAllProducts($orderBy = 'cake_name', $orderDir = 'ASC') {
    $conn = connectDB();
    if (!$conn) return [];

    // Basic validation for order parameters
    $validColumns = ['product_id', 'cake_name', 'base_price', 'category'];
    $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'cake_name';
    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

    try {
        $sql = "SELECT product_id, cake_name, base_price, category, custom_available FROM PRODUCTS ORDER BY " . $orderBy . " " . $orderDir;
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get all products error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Searches for products based on a search term.
 * Searches across cake name, category, and description.
 *
 * @param string $searchTerm The search term to look for.
 * @return array An array of product records matching the search, or an empty array on failure/no results.
 */
function searchProducts($searchTerm) {
    $conn = connectDB();
    if (!$conn) return [];

    // Trim and prepare search term
    $searchTerm = trim($searchTerm);
    if (empty($searchTerm)) return [];
    
    // Add wildcards for LIKE search
    $searchPattern = '%' . strtolower($searchTerm) . '%';

    try {
        $sql = "SELECT product_id, cake_name, base_price, category, custom_available, description
                FROM PRODUCTS 
                WHERE LOWER(cake_name) LIKE ? 
                   OR LOWER(category) LIKE ? 
                   OR LOWER(description) LIKE ?
                ORDER BY product_id ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Search products error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Validates product form data.
 *
 * @param array $data Array containing product data (e.g., from $_POST).
 * @return array An array of error messages. Empty if validation passes.
 */
function validateProductForm($data) {
    $errors = [];

    if (empty(trim($data['cake_name']))) {
        $errors['cake_name'] = "Cake Name is required.";
    }

    if (!isset($data['base_price']) || $data['base_price'] === '') {
        $errors['base_price'] = "Base Price is required.";
    } elseif (!is_numeric($data['base_price']) || $data['base_price'] < 0) {
        $errors['base_price'] = "Base Price must be a valid positive number.";
    }

    // Optional fields validation (e.g., length limits)
    if (isset($data['category']) && strlen($data['category']) > 50) {
         $errors['category'] = "Category cannot exceed 50 characters.";
    }
    if (isset($data['size_options']) && strlen($data['size_options']) > 255) {
         $errors['size_options'] = "Size Options cannot exceed 255 characters.";
    }

    return $errors;
}

/**
 * Adds a new product to the database.
 *
 * @param array $data Array containing validated product data.
 * @return int|false The ID of the newly inserted product on success, or false on failure.
 */
function addProduct($data) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "INSERT INTO PRODUCTS (cake_name, base_price, category, description, custom_available, size_options)
                VALUES (:cake_name, :base_price, :category, :description, :custom_available, :size_options)";
        $stmt = $conn->prepare($sql);

        // Ensure boolean value is correctly handled
        $customAvailable = isset($data['custom_available']) && $data['custom_available'] ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':cake_name', $data['cake_name'], PDO::PARAM_STR);
        $stmt->bindParam(':base_price', $data['base_price']); // PDO detects type (should be string for decimal)
        $stmt->bindParam(':category', $data['category'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':custom_available', $customAvailable, PDO::PARAM_INT); 
        $stmt->bindParam(':size_options', $data['size_options'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $conn->lastInsertId();
        } else {
            return false;
        }
    } catch (PDOException $e) {
        error_log("Add product error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Fetches a single product record by its ID.
 *
 * @param int $id The ID of the product to fetch.
 * @return array|false An associative array of the product's data, or false if not found or error.
 */
function getProductById($id) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "SELECT * FROM PRODUCTS WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns false if no record found
    } catch (PDOException $e) {
        error_log("Get product by ID error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Updates an existing product record in the database.
 *
 * @param int $id The ID of the product to update.
 * @param array $data Array containing validated product data.
 * @return bool True on success, false on failure.
 */
function updateProduct($id, $data) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "UPDATE PRODUCTS SET 
                    cake_name = :cake_name, 
                    base_price = :base_price, 
                    category = :category, 
                    description = :description, 
                    custom_available = :custom_available, 
                    size_options = :size_options 
                WHERE product_id = :id";
        $stmt = $conn->prepare($sql);

        // Ensure boolean value is correctly handled
        $customAvailable = isset($data['custom_available']) && $data['custom_available'] ? 1 : 0;

        // Bind parameters
        $stmt->bindParam(':cake_name', $data['cake_name'], PDO::PARAM_STR);
        $stmt->bindParam(':base_price', $data['base_price']);
        $stmt->bindParam(':category', $data['category'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':custom_available', $customAvailable, PDO::PARAM_INT); 
        $stmt->bindParam(':size_options', $data['size_options'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Update product error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Deletes a product from the database after checking if it's used in orders.
 *
 * @param int $id The ID of the product to delete.
 * @return string Returns 'deleted' on success, 'in_use' if deletion is blocked, or 'error' on failure.
 */
function deleteProduct($id) {
    $conn = connectDB();
    if (!$conn) return 'error';

    try {
        // 1. Check if the product is used in any ORDER_ITEMS
        $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM ORDER_ITEMS WHERE product_id = :id");
        $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $itemCount = $stmtCheck->fetchColumn();

        if ($itemCount > 0) {
            // Product is in use, cannot delete
            return 'in_use'; 
        }

        // 2. If not in use, proceed with deletion
        $stmtDelete = $conn->prepare("DELETE FROM PRODUCTS WHERE product_id = :id");
        $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmtDelete->execute()) {
             return 'deleted'; // Deletion successful
        } else {
            return 'error'; // Deletion failed
        }

    } catch (PDOException $e) {
        error_log("Delete product error: " . $e->getMessage());
        return 'error';
    } finally {
        $conn = null;
    }
}


// --- Order Management Functions --- //

/**
 * Fetches all orders, optionally joining with customer names.
 *
 * @param string $orderBy Column to order results by (default: order_date).
 * @param string $orderDir Order direction ('ASC' or 'DESC', default: 'DESC').
 * @param bool $joinCustomer Whether to join with the CUSTOMERS table (default: true).
 * @return array An array of order records, or an empty array on failure/no results.
 */
function getAllOrders($orderBy = 'order_date', $orderDir = 'DESC', $joinCustomer = true) {
    $conn = connectDB();
    if (!$conn) return [];

    // Basic validation for order parameters
    $validColumns = ['o.order_id', 'o.order_date', 'o.delivery_date', 'o.total_amount', 'o.order_status', 'c.full_name']; // Alias tables
    $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'o.order_date'; // Default to order_date
    $orderDir = strtoupper($orderDir) === 'ASC' ? 'ASC' : 'DESC'; // Default to DESC

    try {
        $selectFields = "o.order_id, o.customer_id, o.user_id, o.order_date, o.delivery_date, o.delivery_time, o.delivery_address, o.order_status, o.total_amount, o.is_paid";
        $fromClause = "FROM ORDERS o";
        $joinClause = "";
        
        if ($joinCustomer) {
            $selectFields .= ", c.full_name as customer_name";
            $joinClause = " JOIN CUSTOMERS c ON o.customer_id = c.customer_id";
        }
        
        $sql = "SELECT " . $selectFields . " " . $fromClause . $joinClause . " ORDER BY " . $orderBy . " " . $orderDir;
        
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get all orders error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets all orders for a specific customer.
 *
 * @param int $customerId The customer ID to get orders for.
 * @return array An array of order records for the customer, or an empty array on failure/no results.
 */
function getOrdersByCustomer($customerId) {
    $conn = connectDB();
    if (!$conn) return [];

    try {
        $sql = "SELECT o.order_id, o.customer_id, o.user_id, o.order_date, o.delivery_date, 
                       o.delivery_time, o.delivery_address, o.order_status, o.total_amount, o.is_paid
                FROM ORDERS o 
                WHERE o.customer_id = ?
                ORDER BY o.order_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get orders by customer error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets all users from the database.
 *
 * @param string $orderBy Column to order by (default: 'username').
 * @param string $orderDir Order direction ('ASC' or 'DESC', default: 'ASC').
 * @return array An array of user records, or an empty array on failure/no results.
 */
function getAllUsers($orderBy = 'username', $orderDir = 'ASC') {
    $conn = connectDB();
    if (!$conn) return [];

    // Basic validation for order parameters
    $validColumns = ['user_id', 'username', 'full_name', 'email', 'role', 'last_login'];
    $orderBy = in_array($orderBy, $validColumns) ? $orderBy : 'username';
    $orderDir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';

    try {
        $sql = "SELECT user_id, username, full_name, email, role, last_login FROM USERS ORDER BY " . $orderBy . " " . $orderDir;
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get all users error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets a user by their ID.
 *
 * @param int $userId The user ID to retrieve.
 * @return array|false The user record as an associative array, or false if not found.
 */
function getUserById($userId) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare("SELECT user_id, username, full_name, email, role, last_login FROM USERS WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get user by ID error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Adds a new user to the database.
 *
 * @param array $userData Associative array containing user data.
 * @return int|false The ID of the newly inserted user on success, or false on failure.
 */
function addUser($userData) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $sql = "INSERT INTO USERS (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $userData['username'],
            $userData['password'], // Plain text password as per current system
            $userData['full_name'],
            $userData['email'],
            $userData['role']
        ]);
        
        return $result ? $conn->lastInsertId() : false;
    } catch (PDOException $e) {
        error_log("Add user error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Updates a user in the database.
 *
 * @param int $userId The user ID to update.
 * @param array $userData Associative array containing user data.
 * @return bool True on success, false on failure.
 */
function updateUser($userId, $userData) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        // Build dynamic update query based on provided data
        $fields = [];
        $values = [];
        
        if (isset($userData['username'])) {
            $fields[] = 'username = ?';
            $values[] = $userData['username'];
        }
        if (isset($userData['password'])) {
            $fields[] = 'password = ?';
            $values[] = $userData['password'];
        }
        if (isset($userData['full_name'])) {
            $fields[] = 'full_name = ?';
            $values[] = $userData['full_name'];
        }
        if (isset($userData['email'])) {
            $fields[] = 'email = ?';
            $values[] = $userData['email'];
        }
        if (isset($userData['role'])) {
            $fields[] = 'role = ?';
            $values[] = $userData['role'];
        }
        
        if (empty($fields)) return false;
        
        $values[] = $userId; // Add user ID for WHERE clause
        $sql = "UPDATE USERS SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute($values);
    } catch (PDOException $e) {
        error_log("Update user error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Deletes a user from the database.
 *
 * @param int $userId The user ID to delete.
 * @return bool True on success, false on failure.
 */
function deleteUser($userId) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        $stmt = $conn->prepare("DELETE FROM USERS WHERE user_id = ?");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log("Delete user error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Validates user form data.
 *
 * @param array $data Array containing user data (e.g., from $_POST).
 * @param bool $isEdit Whether this is an edit operation (password not required).
 * @return array An array of error messages. Empty if validation passes.
 */
function validateUserForm($data, $isEdit = false) {
    $errors = [];

    // Username validation
    $username = trim($data['username'] ?? '');
    if (empty($username)) {
        $errors['username'] = "Username is required.";
    } elseif (strlen($username) < 3) {
        $errors['username'] = "Username must be at least 3 characters long.";
    } elseif (strlen($username) > 50) {
        $errors['username'] = "Username must not exceed 50 characters.";
    } elseif (!preg_match('/^[a-zA-Z0-9._-]+$/', $username)) {
        $errors['username'] = "Username can only contain letters, numbers, dots, hyphens, and underscores.";
    }

    // Password validation (required for new users, optional for edits)
    $password = trim($data['password'] ?? '');
    if (!$isEdit && empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    } elseif (!empty($password) && strlen($password) > 255) {
        $errors['password'] = "Password must not exceed 255 characters.";
    }

    // Full name validation
    $fullName = trim($data['full_name'] ?? '');
    if (empty($fullName)) {
        $errors['full_name'] = "Full Name is required.";
    } elseif (strlen($fullName) < 2) {
        $errors['full_name'] = "Full Name must be at least 2 characters long.";
    } elseif (strlen($fullName) > 100) {
        $errors['full_name'] = "Full Name must not exceed 100 characters.";
    } elseif (!preg_match('/^[a-zA-Z\s\-\'.]+$/', $fullName)) {
        $errors['full_name'] = "Full Name can only contain letters, spaces, hyphens, apostrophes, and periods.";
    }

    // Email validation (optional)
    $email = trim($data['email'] ?? '');
    if (!empty($email)) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address.";
        } elseif (strlen($email) > 100) {
            $errors['email'] = "Email must not exceed 100 characters.";
        }
    }

    // Role validation
    $role = trim($data['role'] ?? '');
    if (empty($role)) {
        $errors['role'] = "Role is required.";
    } elseif (!in_array($role, ['Admin', 'Staff'])) {
        $errors['role'] = "Role must be either 'Admin' or 'Staff'.";
    }

    return $errors;
}

/**
 * Searches for orders based on a search term.
 * Searches across customer name, order ID, order status, and delivery address.
 *
 * @param string $searchTerm The search term to look for.
 * @return array An array of order records matching the search, or an empty array on failure/no results.
 */
function searchOrders($searchTerm) {
    $conn = connectDB();
    if (!$conn) return [];

    // Trim and prepare search term
    $searchTerm = trim($searchTerm);
    if (empty($searchTerm)) return [];
    
    // Add wildcards for LIKE search
    $searchPattern = '%' . strtolower($searchTerm) . '%';

    try {
        $sql = "SELECT o.order_id, o.customer_id, o.user_id, o.order_date, o.delivery_date, 
                       o.delivery_time, o.delivery_address, o.order_status, o.total_amount, 
                       o.is_paid, c.full_name as customer_name
                FROM ORDERS o 
                JOIN CUSTOMERS c ON o.customer_id = c.customer_id
                WHERE LOWER(c.full_name) LIKE ? 
                   OR LOWER(o.order_status) LIKE ? 
                   OR LOWER(o.delivery_address) LIKE ?
                   OR CAST(o.order_id AS CHAR) LIKE ?
                   OR DATE_FORMAT(o.delivery_date, '%Y-%m-%d') LIKE ?
                   OR DATE_FORMAT(o.delivery_date, '%d/%m/%Y') LIKE ?
                   OR DATE_FORMAT(o.delivery_date, '%d %b %Y') LIKE ?
                ORDER BY o.order_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Search orders error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Adds a new order header to the ORDERS table.
 * Typically called within a transaction context.
 *
 * @param PDO $conn The database connection object.
 * @param array $orderData Associative array containing order header details 
 *                        (customer_id, user_id, delivery_date, delivery_time, 
 *                         delivery_address, total_amount, special_requirements, etc.)
 * @return int|false The ID of the newly inserted order on success, or false on failure.
 */
function addOrderHeader(PDO $conn, $orderData) {
    // Assume basic validation happens before calling this
    $sql = "INSERT INTO ORDERS (customer_id, user_id, delivery_date, delivery_time, delivery_address, order_status, total_amount, is_paid, special_requirements, order_date)
            VALUES (:customer_id, :user_id, :delivery_date, :delivery_time, :delivery_address, :order_status, :total_amount, :is_paid, :special_requirements, NOW())";
    
    $stmt = $conn->prepare($sql);

    // Set defaults if not provided
    $status = $orderData['order_status'] ?? 'New';
    $isPaid = isset($orderData['is_paid']) ? (int)$orderData['is_paid'] : 0;
    $deliveryTime = !empty($orderData['delivery_time']) ? $orderData['delivery_time'] : null;

    $stmt->bindParam(':customer_id', $orderData['customer_id'], PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $orderData['user_id'], PDO::PARAM_INT); // Assuming user_id is stored in session
    $stmt->bindParam(':delivery_date', $orderData['delivery_date'], PDO::PARAM_STR);
    $stmt->bindParam(':delivery_time', $deliveryTime, PDO::PARAM_STR);
    $stmt->bindParam(':delivery_address', $orderData['delivery_address'], PDO::PARAM_STR);
    $stmt->bindParam(':order_status', $status, PDO::PARAM_STR);
    $stmt->bindParam(':total_amount', $orderData['total_amount']); // Let PDO handle type
    $stmt->bindParam(':is_paid', $isPaid, PDO::PARAM_INT);
    $stmt->bindParam(':special_requirements', $orderData['special_requirements'], PDO::PARAM_STR);

    if ($stmt->execute()) {
        return $conn->lastInsertId();
    } else {
        return false;
    }
    // Note: No try-catch here, assumes it's handled by the calling transaction context
}

/**
 * Adds a single item to an order in the ORDER_ITEMS table.
 * Typically called within a transaction context.
 *
 * @param PDO $conn The database connection object.
 * @param int $orderId The ID of the order this item belongs to.
 * @param array $itemData Associative array containing item details 
 *                        (product_id, quantity, price, size, customization).
 * @return bool True on success, false on failure.
 */
function addOrderItem(PDO $conn, $orderId, $itemData) {
    // Assume basic validation happens before calling this
    $sql = "INSERT INTO ORDER_ITEMS (order_id, product_id, quantity, price, size, customization)
            VALUES (:order_id, :product_id, :quantity, :price, :size, :customization)";
            
    $stmt = $conn->prepare($sql);
    
    $quantity = $itemData['quantity'] ?? 1;
    $size = $itemData['size'] ?? null;
    $customization = $itemData['customization'] ?? null;
    
    $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
    $stmt->bindParam(':product_id', $itemData['product_id'], PDO::PARAM_INT);
    $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':price', $itemData['price']); // Let PDO handle type
    $stmt->bindParam(':size', $size, PDO::PARAM_STR);
    $stmt->bindParam(':customization', $customization, PDO::PARAM_STR);
    
    return $stmt->execute();
    // Note: No try-catch here, assumes it's handled by the calling transaction context
}

/**
 * Fetches a single order record by its ID, optionally joining customer/user info.
 *
 * @param int $id The ID of the order to fetch.
 * @return array|false An associative array of the order's data, or false if not found or error.
 */
function getOrderById($id) {
    $conn = connectDB();
    if (!$conn) return false;

    try {
        // Join with customers and users to get names
        $sql = "SELECT o.*, c.full_name as customer_name, c.phone_number as customer_phone, c.email as customer_email, u.full_name as user_name
                FROM ORDERS o 
                JOIN CUSTOMERS c ON o.customer_id = c.customer_id
                LEFT JOIN USERS u ON o.user_id = u.user_id
                WHERE o.order_id = :id";
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // Returns false if no record found
    } catch (PDOException $e) {
        error_log("Get order by ID error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

/**
 * Fetches all items associated with a specific order ID.
 *
 * @param int $orderId The ID of the order.
 * @return array An array of order item records (joined with product name), or empty array on failure.
 */
function getOrderItems($orderId) {
    $conn = connectDB();
    if (!$conn) return [];

    try {
        // Join with PRODUCTS table to get the cake name
        $sql = "SELECT oi.*, p.cake_name 
                FROM ORDER_ITEMS oi
                JOIN PRODUCTS p ON oi.product_id = p.product_id
                WHERE oi.order_id = :order_id
                ORDER BY oi.item_id ASC"; // Order items consistently
                
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get order items error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Updates the main details of an existing order (status, payment, delivery, etc.).
 * Does NOT handle updating line items or recalculating total.
 *
 * @param int $orderId The ID of the order to update.
 * @param array $data Associative array containing the fields to update 
 *                    (e.g., order_status, is_paid, delivery_date, delivery_time, 
 *                     delivery_address, special_requirements).
 * @return bool True on success, false on failure.
 */
function updateOrder($orderId, $data) {
    $conn = connectDB();
    if (!$conn) return false;

    // Define allowed fields to update to prevent arbitrary updates
    $allowedFields = [
        'order_status', 
        'is_paid', 
        'delivery_date', 
        'delivery_time', 
        'delivery_address', 
        'special_requirements'
    ];

    $setClauses = [];
    $params = [':order_id' => $orderId]; // Start params with the ID for WHERE clause

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $setClauses[] = "`" . $field . "` = :" . $field;
            // Handle null for delivery_time if empty string provided
            if ($field === 'delivery_time' && $data[$field] === '') {
                 $params[':' . $field] = null;
            } else {
                 $params[':' . $field] = $data[$field];
            }
        }
    }

    // Only proceed if there are fields to update
    if (empty($setClauses)) {
        return false; // Or true, arguably no update needed is success?
    }

    try {
        $sql = "UPDATE ORDERS SET " . implode(", ", $setClauses) . " WHERE order_id = :order_id";
        $stmt = $conn->prepare($sql);
        
        // Bind parameters dynamically
        foreach ($params as $key => &$value) { // Use reference for binding
            $type = PDO::PARAM_STR; // Default to string
            if ($key === ':order_id' || $key === ':is_paid') { // Specific integer types
                $type = PDO::PARAM_INT;
            }
            // Handle null binding
             if ($value === null) {
                $type = PDO::PARAM_NULL;
            }
            $stmt->bindParam($key, $value, $type);
        }
        unset($value); // Unset reference

        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Update order error: " . $e->getMessage());
        return false;
    } finally {
        $conn = null;
    }
}

// --- Dashboard & Reporting Functions --- //

/**
 * Gets the count of orders for a specific date.
 *
 * @param string $date YYYY-MM-DD format.
 * @return int Count of orders.
 */
function getOrderCountForDate($date) {
    $conn = connectDB();
    if (!$conn) return 0;
    try {
        $sql = "SELECT COUNT(*) FROM ORDERS WHERE delivery_date = :date AND order_status != 'Cancelled'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Get order count for date error: " . $e->getMessage());
        return 0;
    } finally {
        $conn = null;
    }
}

/**
 * Gets the total count of customers.
 *
 * @return int Count of customers.
 */
function getTotalCustomerCount() {
    $conn = connectDB();
    if (!$conn) return 0;
    try {
        $sql = "SELECT COUNT(*) FROM CUSTOMERS";
        $stmt = $conn->query($sql);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Get total customer count error: " . $e->getMessage());
        return 0;
    } finally {
        $conn = null;
    }
}

/**
 * Gets the total income for the current month (based on order date).
 *
 * @return float Total income.
 */
function getCurrentMonthIncome() {
    $conn = connectDB();
    if (!$conn) return 0.00;
    try {
        $currentMonth = date('Y-m');
        $sql = "SELECT SUM(total_amount) FROM ORDERS WHERE DATE_FORMAT(order_date, '%Y-%m') = :month AND order_status != 'Cancelled'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':month', $currentMonth, PDO::PARAM_STR);
        $stmt->execute();
        return (float)$stmt->fetchColumn(); // Returns 0.00 if no orders or error
    } catch (PDOException $e) {
        error_log("Get current month income error: " . $e->getMessage());
        return 0.00;
    } finally {
        $conn = null;
    }
}

/**
 * Gets a list of upcoming orders (delivery date >= today).
 *
 * @param int $limit Max number of orders to return (default: 10).
 * @return array Array of upcoming orders (joined with customer name).
 */
function getUpcomingOrders($limit = 10) {
    $conn = connectDB();
    if (!$conn) return [];
    try {
        $today = date('Y-m-d');
        $sql = "SELECT o.order_id, o.delivery_date, o.delivery_time, o.order_status, o.total_amount, c.full_name as customer_name
                FROM ORDERS o
                JOIN CUSTOMERS c ON o.customer_id = c.customer_id
                WHERE o.delivery_date >= :today AND o.order_status NOT IN ('Delivered', 'Cancelled')
                ORDER BY o.delivery_date ASC, o.delivery_time ASC
                LIMIT :limit";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':today', $today, PDO::PARAM_STR);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get upcoming orders error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets order details for a specific delivery date, intended for production planning.
 * Includes customer info and detailed item info.
 *
 * @param string $date YYYY-MM-DD format.
 * @return array Array of orders with items, or empty array on failure.
 */
function getOrdersForDeliveryDate($date) {
    $conn = connectDB();
    if (!$conn) return [];
    try {
        // Fetch orders for the date
        $sqlOrders = "SELECT o.*, c.full_name as customer_name, c.phone_number as customer_phone
                      FROM ORDERS o
                      JOIN CUSTOMERS c ON o.customer_id = c.customer_id
                      WHERE o.delivery_date = :date AND o.order_status NOT IN ('Delivered', 'Cancelled')
                      ORDER BY o.delivery_time ASC, o.order_id ASC";
        $stmtOrders = $conn->prepare($sqlOrders);
        $stmtOrders->bindParam(':date', $date, PDO::PARAM_STR);
        $stmtOrders->execute();
        $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

        // If no orders, return early
        if (empty($orders)) {
            return [];
        }

        // Get all order IDs for fetching items efficiently
        $orderIds = array_column($orders, 'order_id');
        if(empty($orderIds)) return $orders; // Return orders without items if somehow IDs are empty
        
        $placeholders = rtrim(str_repeat('?,', count($orderIds)), ',');

        // Fetch all items for these orders
        $sqlItems = "SELECT oi.*, p.cake_name
                     FROM ORDER_ITEMS oi
                     JOIN PRODUCTS p ON oi.product_id = p.product_id
                     WHERE oi.order_id IN (" . $placeholders . ")
                     ORDER BY oi.order_id, oi.item_id"; // Ensure items are grouped by order
        $stmtItems = $conn->prepare($sqlItems);
        $stmtItems->execute($orderIds);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        // Map items to their respective orders
        $itemsByOrderId = [];
        foreach ($items as $item) {
            $itemsByOrderId[$item['order_id']][] = $item;
        }

        // Add items to each order
        foreach ($orders as &$order) { // Use reference to modify original array
            $order['items'] = $itemsByOrderId[$order['order_id']] ?? [];
        }
        unset($order); // Unset reference

        return $orders;

    } catch (PDOException $e) {
        error_log("Get orders for delivery date error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets orders within a specific delivery date range.
 *
 * @param string $startDate Start date (YYYY-MM-DD).
 * @param string $endDate End date (YYYY-MM-DD).
 * @return array Array of orders within the range (joined with customer name).
 */
function getOrdersForDeliveryDateRange($startDate, $endDate) {
    $conn = connectDB();
    if (!$conn) return [];
    try {
        // Fetch orders within the date range
        $sql = "SELECT o.*, c.full_name as customer_name
                FROM ORDERS o
                JOIN CUSTOMERS c ON o.customer_id = c.customer_id
                WHERE o.delivery_date BETWEEN :startDate AND :endDate AND o.order_status != 'Cancelled'
                ORDER BY o.delivery_date ASC, o.delivery_time ASC, o.order_id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get orders for delivery date range error: " . $e->getMessage());
        return [];
    } finally {
        $conn = null;
    }
}

/**
 * Gets the sales summary for a specific month and year.
 *
 * @param string $yearMonth Year and month in 'YYYY-MM' format.
 * @return array Associative array containing 'total_sales' and 'order_count'.
 */
function getMonthlySalesSummary($yearMonth) {
    $conn = connectDB();
    if (!$conn) return ['total_sales' => 0.00, 'order_count' => 0];
    try {
        // Fetch total sales and order count for the month (based on order_date)
        $sql = "SELECT SUM(total_amount) as total_sales, COUNT(*) as order_count
                FROM ORDERS
                WHERE DATE_FORMAT(order_date, '%Y-%m') = :yearMonth 
                      AND order_status != 'Cancelled'"; 
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':yearMonth', $yearMonth, PDO::PARAM_STR);
        $stmt->execute();
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Ensure we return numbers even if no results
        return [
            'total_sales' => (float)($summary['total_sales'] ?? 0.00),
            'order_count' => (int)($summary['order_count'] ?? 0)
        ];
    } catch (PDOException $e) {
        error_log("Get monthly sales summary error: " . $e->getMessage());
        return ['total_sales' => 0.00, 'order_count' => 0];
    } finally {
        $conn = null;
    }
}

?> 