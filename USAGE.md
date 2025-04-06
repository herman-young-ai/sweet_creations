# Sweet Creations Usage Guide

This guide explains how to use the basic features of the Sweet Creations management system.

## Logging In

1.  Navigate to the application URL in your browser (e.g., `http://localhost/sweet_creations/`).
2.  You will be presented with the login screen.
3.  Enter the username and password provided during setup.
    *   Default Username: `admin`
    *   Default Password: `admin123`
4.  Click the "Log in" button.
5.  Upon successful login, you will be redirected to the Dashboard.

## Logging Out

1.  While logged in, locate your username in the top-right corner of the navigation bar.
2.  Click on your username to open the dropdown menu.
3.  Click the "Log Out" option.
4.  Alternatively, click the power-off icon in the bottom-left sidebar footer.
5.  You will be logged out and redirected back to the login screen.

## Dashboard

The Dashboard (`index.php`) is the main landing page after logging in. It provides a quick overview of:
*   Orders scheduled for today and tomorrow.
*   Total number of customers.
*   Total sales income for the current month.
*   A list of the next few upcoming orders.

## Customer Management

Navigate using the "Customers" menu in the left sidebar.

*   **View Customers:** Select "View Customers" to see a list of all customers. You can search by name, phone, or email using the search bar.
*   **Add Customer:** From the "View Customers" page, click the "Add New Customer" button. Fill in the required details (Name, Phone) and optional details (Email, Address, Notes), then click "Add Customer".
*   **View Details:** Click the "View" button next to a customer in the list to see their full details (order history is currently a placeholder).
*   **Edit Customer:** Click the "Edit" button next to a customer (either from the list or the view page). Modify the details and click "Save Changes".
*   **Delete Customer:** Click the "Delete" button next to a customer in the list. Confirm the action in the popup. Note: Customers with existing orders cannot be deleted.

## Product Management

Navigate using the "Products" menu in the left sidebar.

*   **View Products:** Select "View Products" to see a list of all available cake products.
*   **Add Product:** From the "View Products" page, click "Add New Product". Fill in the required details (Cake Name, Base Price) and optional details (Category, Description, Customization availability, Size options). Click "Add Product".
*   **Edit Product:** Click the "Edit" button next to a product. Modify the details and click "Save Changes".
*   **Delete Product:** Click the "Delete" button next to a product. Confirm the action. Note: Products used in existing orders cannot be deleted.

## Order Management

Navigate using the "Orders" menu in the left sidebar.

*   **View Orders:** Select "View Orders" to see a list of all past and present orders, sorted by order date (newest first).
*   **Add New Order (Wizard):**
    1.  Select "Add New Order".
    2.  **Step 1:** Choose an existing customer from the dropdown. Click "Next".
    3.  **Step 2:** Select a product, enter quantity, and optionally size/customization notes. Click "Add Item". Repeat for all items. Review the items list and total. Click "Next".
    4.  **Step 3:** Enter the required Delivery Date and Address. Optionally add Delivery Time and Special Requirements. Click "Next".
    5.  **Step 4:** Review all order details (Customer, Items, Delivery, Total). Click "Confirm & Save Order". You will be redirected to the orders list on success.
    *   You can use the "Back" buttons to navigate to previous steps or "Cancel & Reset" (top right) to abandon the order.
*   **View Order Details:** Click the "View" button next to an order in the list.
*   **Edit Order:** Click the "Edit" button next to an order. You can modify the Order Status, Payment Status, Delivery Details, and Special Requirements. Click "Save Order Changes". Note: Editing line items is not supported on this page.

## Reporting

Navigate using the "Reports" menu in the left sidebar.

*   Select "Generate Reports" to see available report options.
*   **Daily Production List:** Click the link, select a date, and click "Generate Report" to see all orders (and their items) scheduled for delivery on that day.
*   **Delivery Schedule:** Click the link, select a start and end date, and click "Generate Report" to see a list of orders scheduled for delivery within that range, including contact and address details.
*   **Monthly Sales Summary:** Click the link, select a month and year, and click "Generate Report" to see the total number of orders and total sales amount for that month (based on order date).

*(More sections will be added as features like Customer, Product, and Order management are implemented.)*
