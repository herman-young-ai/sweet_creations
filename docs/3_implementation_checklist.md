# Sweet Creations Project Implementation Checklist

This checklist guides the development of the Sweet Creations project, focusing on simplicity, use of a `.env` file, and maintaining documentation.

**Phase 0: Project Setup & Configuration**

*   [ ] Initialize Git repository in the project root (`sweet_creations/`).
*   [ ] Create the basic directory structure as defined in `docs/1_technical_design_document.md`:
    *   [ ] `assets/css/`
    *   [ ] `assets/js/`
    *   [ ] `assets/images/`
    *   [ ] `assets/gentelella/`
    *   [ ] `includes/`
    *   [ ] `modules/`
    *   [ ] `modules/customers/`
    *   [ ] `modules/orders/`
    *   [ ] `modules/products/`
    *   [ ] `modules/reports/`
    *   [ ] `uploads/`
*   [ ] Create the root `.env` file.
*   [ ] Define database connection variables (host, dbname, user, password) in `.env`.
*   [ ] Create `includes/config.php` to load environment variables from `.env` and store configuration settings (e.g., database credentials). **Note:** Avoid directly placing credentials in `config.php`. Use a library or simple `getenv()` calls to read from `.env`.
*   [ ] Create `includes/functions.php` (initially empty or with basic DB connection function).
*   [ ] Implement the database connection function (`connectDB()`) in `includes/functions.php`, reading credentials via `includes/config.php`.
*   [ ] Create the MySQL database (`sweet_creations`).
*   [ ] Create database tables based on `docs/1_technical_design_document.md` (USERS, CUSTOMERS, PRODUCTS, ORDERS, ORDER_ITEMS). Define primary keys, foreign keys, and data types. (Consider creating a `database.sql` file for this).
*   [ ] Populate initial data:
    *   [ ] Create default admin user (Username: `admin`, Password: `admin123` - hash this!).
    *   [ ] Add sample products.
    *   [ ] Add sample customers.
*   [ ] Update `INSTALL.md` with initial database setup and `.env` configuration instructions.
*   [ ] Update `README.md` with a brief project overview and setup status.

**Phase 1: Core System & Authentication**

*   **Layout & Base Templates:**
    *   [ ] Download and place Bootstrap and Gentelella Admin Theme files into `assets/`.
    *   [ ] Create `includes/header.php` with HTML head, CSS links, and opening body/container tags.
    *   [ ] Create `includes/footer.php` with closing container tags, JS links, and footer content.
    *   [ ] Create `includes/sidebar.php` with basic navigation structure (links can be placeholders for now).
*   **Authentication:**
    *   [ ] Create `login.php` page with HTML form (username, password).
    *   [ ] Implement `hashPassword()` and `verifyPassword()` functions in `includes/functions.php` (using SHA-256 + salt as specified).
    *   [ ] Implement login logic in `login.php` or a separate processing script:
        *   [ ] Validate input.
        *   [ ] Check username and hashed password against the `USERS` table.
        *   [ ] Start session (`session_start()` needed at the top of pages using sessions).
        *   [ ] Store `user_id`, `username`, `role` in `$_SESSION`.
        *   [ ] Update `last_login` in the `USERS` table.
        *   [ ] Redirect to `index.php` on success, show error on failure.
    *   [ ] Create `logout.php` to destroy the session and redirect to `login.php`.
    *   [ ] Implement `isLoggedIn()` and `requireLogin()` helper functions in `includes/functions.php`.
    *   [ ] Create `index.php` (main dashboard page). Use `requireLogin()`. Include header, sidebar, and footer. Display a simple welcome message.
*   **User Roles:**
    *   [ ] Add basic role check (e.g., in `sidebar.php` or specific pages) to show/hide elements based on `$_SESSION['role']` ('Admin'/'Staff').
*   **Documentation:**
    *   [ ] Update `README.md` and `USAGE.md` with login/logout instructions.
    *   [ ] Update `INSTALL.md` regarding the default admin user.

**Phase 2: Module Implementation (CRUD Operations)**

*   **Customer Management (`modules/customers/`)**
    *   [ ] Create `customers.php` to list all customers.
        *   [ ] Use `requireLogin()`.
        *   [ ] Include header/footer/sidebar.
        *   [ ] Implement `getAllCustomers()` in `includes/functions.php`.
        *   [ ] Display customers in an HTML table (use `htmlspecialchars`).
        *   [ ] Add links/buttons for View, Edit, Delete.
    *   [ ] Create `add_customer.php` page with the customer form.
        *   [ ] Include header/footer/sidebar.
        *   [ ] Implement form processing logic (POST request handling).
        *   [ ] Implement `validateCustomerForm()` (including phone format) in `includes/functions.php`.
        *   [ ] Implement `addCustomer()` in `includes/functions.php` using PDO prepared statements.
        *   [ ] Show success/error messages.
        *   [ ] Redirect to `customers.php` on success.
    *   [ ] Create `edit_customer.php` page.
        *   [ ] Get customer ID from URL (`$_GET['id']`).
        *   [ ] Implement `getCustomerById()` in `includes/functions.php`.
        *   [ ] Pre-populate the form with existing customer data.
        *   [ ] Implement update logic (`updateCustomer()`).
        *   [ ] Validate input.
        *   [ ] Redirect to `customers.php` on success.
    *   [ ] Implement delete functionality (e.g., `delete_customer.php` or handle in `customers.php`).
        *   [ ] Get customer ID.
        *   [ ] Implement `deleteCustomer()` in `includes/functions.php` (include check for existing orders).
        *   [ ] Add confirmation prompt (JavaScript `confirm()`).
        *   [ ] Redirect back to `customers.php`.
    *   [ ] Implement search functionality on `customers.php`.
        *   [ ] Add search form (GET method).
        *   [ ] Implement `searchCustomers()` in `includes/functions.php`.
        *   [ ] Modify `customers.php` to call `searchCustomers()` if a search term exists.
    *   [ ] Create `view_customer.php` page.
        *   [ ] Display customer details.
        *   [ ] Implement logic to fetch and display related orders (placeholder for now, requires Order module).
*   **Product Management (`modules/products/`)**
    *   [ ] Create `products.php` to list all products.
        *   [ ] Similar structure to `customers.php`.
        *   [ ] Implement `getAllProducts()`.
        *   [ ] Display in a table.
        *   [ ] Add links for Add, Edit, Delete.
    *   [ ] Create `add_product.php`.
        *   [ ] Form for cake name, base price, category, description, custom available, size options.
        *   [ ] Implement validation.
        *   [ ] Implement `addProduct()`.
    *   [ ] Create `edit_product.php`.
        *   [ ] Implement `getProductById()`.
        *   [ ] Pre-populate form.
        *   [ ] Implement `updateProduct()`.
    *   [ ] Implement delete functionality (`deleteProduct()`).
        *   **Consider:** Check if the product is used in any `ORDER_ITEMS` before deleting. Decide on deletion strategy (disallow or mark as inactive).
*   **Order Management (`modules/orders/`)**
    *   [ ] Create `orders.php` to list all orders.
        *   [ ] Implement `getAllOrders()` (consider joining with `CUSTOMERS` for display).
        *   [ ] Display in a table with key info (Order ID, Customer, Order Date, Delivery Date, Status, Total).
        *   [ ] Add filters (by status, date range).
        *   [ ] Add links for View, Edit, Add New.
    *   [ ] Create `add_order.php` (Order Creation Wizard).
        *   [ ] Step 1: Select Customer (Search/dropdown).
        *   [ ] Step 2: Select Products (List products, allow adding multiple items with quantity, size, customization notes).
        *   [ ] Step 3: Delivery Details (Date picker, time input, address - prefill from customer?).
        *   [ ] Step 4: Review and Confirm (Calculate total, add special requirements).
        *   [ ] Implement `addOrder()` and `addOrderItem()` functions. Use a transaction to ensure atomicity.
    *   [ ] Create `edit_order.php`.
        *   [ ] Implement `getOrderById()` and `getOrderItemsById()`.
        *   [ ] Allow editing details (status, delivery info, potentially items if status permits).
        *   [ ] Implement `updateOrder()` and potentially `updateOrderItem()`, `deleteOrderItem()`. Handle total recalculation.
    *   [ ] Create `view_order.php`.
        *   [ ] Display full order details including customer info, all items, delivery details, status history (optional).
    *   [ ] Implement Order Status updates (logic within `edit_order.php` or separate actions).
    *   [ ] Implement helper functions for currency (`formatCurrency()`) and date (`formatDate()`) display in `includes/functions.php`.
*   **Documentation:**
    *   [ ] Update `USAGE.md` for each module as it's completed (how to add a customer, product, order, etc.).
    *   [ ] Refine `README.md` with completed features.

**Phase 3: Reporting & Dashboard**

*   **Dashboard (`index.php`)**
    *   [ ] Implement data fetching for dashboard widgets:
        *   [ ] Count of tomorrow's orders (`getTomorrowsOrdersCount()`).
        *   [ ] Total customers count (`getTotalCustomersCount()`).
        *   [ ] Simple monthly income calculation (`getCurrentMonthIncome()`).
        *   [ ] List of tomorrow's orders (`getTomorrowsOrders()` - basic details).
    *   [ ] Display fetched data in the dashboard layout using Gentelella widgets/panels.
*   **Reporting (`modules/reports/`)**
    *   [ ] Create `reports.php` selection interface.
        *   [ ] Links/buttons for different reports (Daily Production, Delivery Schedule, Monthly Sales).
    *   [ ] Implement Daily Production Report:
        *   [ ] Data function `getOrdersByDate()` (or similar).
        *   [ ] Display logic (HTML table).
        *   [ ] PDF export option.
    *   [ ] Implement Delivery Schedule Report:
        *   [ ] Data function `getDeliveriesByDateRange()`.
        *   [ ] Display logic.
        *   [ ] PDF export option.
    *   [ ] Implement Monthly Sales Report:
        *   [ ] Data function `getMonthlySales()` (group by day or product).
        *   [ ] Display logic.
        *   [ ] PDF export option.
    *   [ ] Integrate PDF library (FPDF or TCPDF):
        *   [ ] Place library files (e.g., in a `libs/` folder).
        *   [ ] Create helper functions (e.g., `generateOrdersPDF()`) to generate PDFs based on report data. Add `require_once` for the library.
*   **Documentation:**
    *   [ ] Update `USAGE.md` explaining how to use the dashboard and generate reports.

**Phase 4: Security, Refinement & Testing**

*   **Security:**
    *   [ ] Review all database queries for proper PDO prepared statement usage.
    *   [ ] Review all form inputs for server-side validation (`validate...Form()` functions).
    *   [ ] Ensure `htmlspecialchars()` is used when outputting any user-generated content to prevent XSS.
    *   [ ] Implement basic CSRF protection on forms (e.g., generate a token, store in session, verify on POST).
    *   [ ] Ensure `requireLogin()` is used on all pages except `login.php`.
    *   [ ] Add role checks where necessary (e.g., only Admins can access user management if added later).
    *   [ ] Check file permissions (especially `uploads/` if used).
    *   [ ] Disable directory listing (e.g., via `.htaccess` if using Apache).
*   **Error Handling:**
    *   [ ] Ensure graceful error messages are shown to users.
    *   [ ] Implement consistent error logging (using `logError()` or similar) for database and critical errors. Check PHP error log configuration.
*   **UI Refinements:**
    *   [ ] Ensure consistent look and feel across all pages using the Gentelella theme.
    *   [ ] Improve usability of forms and tables.
    *   [ ] Test responsiveness on different screen sizes (basic check).
*   **Testing:**
    *   [ ] Test all CRUD operations for each module.
    *   [ ] Test login, logout, session timeout (if implemented).
    *   [ ] Test form validations (required fields, formats like phone/email).
    *   [ ] Test report generation and PDF export.
    *   [ ] Test role-based access control.
    *   [ ] Test search and filtering functionalities.
    *   [ ] Test edge cases (e.g., deleting a customer with orders, empty search results).
*   **Final Documentation:**
    *   [ ] Perform a final review and update of `README.md`, `INSTALL.md`, and `USAGE.md`. Ensure they are complete and accurate.
    *   [ ] Add comments to code where logic is complex or non-obvious.

**Post-Development**

*   [ ] Final code cleanup (remove unused code, debug statements).
*   [ ] Create `database.sql` export of the final schema and initial data.
*   [ ] Prepare final project archive. 