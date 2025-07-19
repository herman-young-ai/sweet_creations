# Sweet Creations - Cake Shop Management System

Sweet Creations is a simple web-based application designed to help a local cake shop manage customer information, product details, and orders.

This project was developed as part of an IB Computer Science Internal Assessment.

## Technology Stack

*   **PHP:** 8.0+
*   **MySQL:** 8.0+
*   **Web Server:** Apache (tested with XAMPP)
*   **Frontend:** HTML, CSS, Basic JavaScript, Bootstrap 4

## Installation

**Requirements:**
*   XAMPP (or equivalent Apache/MySQL/PHP stack)
*   Modern Web Browser

**Steps:**

1.  **Start Server:** Ensure Apache and MySQL services are running (e.g., via XAMPP Control Panel).
2.  **Place Files:** Clone or download the project files into a folder named `sweet_creations` inside your web server's document root (e.g., `htdocs` for XAMPP).
3.  **Database Setup:**
    *   Using phpMyAdmin (usually accessible at `http://localhost/phpmyadmin`) or another MySQL tool, import the `database.sql` file found in the project root. This creates the `sweet_creations` database, tables, and initial data (including users and sample data).
    *   Verify/update database credentials (`DB_USER`, `DB_PASS`) in the `.env` file located in the project root if your MySQL setup differs from the default (user `root`, no password).
4.  **Access:** Navigate to `http://localhost/sweet_creations/` in your browser.

## Usage

1.  **Login:** Access the site (`http://localhost/sweet_creations/`) and log in using one of the following credentials:

    **Business Owner (Administrator):**
    *   Username: `rashni.devi`
    *   Password: `admin123`
    
    **Staff Members:**
    *   Username: `amit.sharma` | Password: `staff123`
    *   Username: `nisha.patel` | Password: `staff123`
    *   Username: `kevin.wong` | Password: `staff123`
    *   Username: `anita.gopal` | Password: `staff123`
    *   Username: `yusuf.kader` | Password: `staff123`

2.  **Dashboard:** Provides an overview of upcoming deliveries and counts.
3.  **Sidebar Navigation:** Use the left sidebar to navigate between sections:
    *   **Orders:** View existing orders, add new orders.
    *   **Customers:** View, add, edit, or delete customer records (customers with orders cannot be deleted).
    *   **Products:** View, add, edit, or delete product details (products used in orders cannot be deleted).
    *   **Reports:** Generate simple reports for daily production, delivery schedule, or monthly sales.
4.  **Operations:** Within the Orders, Customers, and Products sections, use the provided buttons ("Add New...", "View", "Edit", "Delete") to manage data.
5.  **Logout:** Click your username in the top-right navbar and select "Log Out".

## User Roles

*   **Administrator (Business Owner):** Full access to all features including user management and system administration.
*   **Staff:** Can create and manage orders, customers, and products. Can view reports and delivery schedules.

## Sample Data

The database includes:
*   **15 customers** with authentic Mauritian names and contact details
*   **15 cake products** ranging from MUR 950 to MUR 1,500
*   **Sample orders** demonstrating the system workflow
*   **6 users** (1 admin + 5 staff) representing a realistic cake shop team

## Troubleshooting

*   **Database Errors:** Check `.env` credentials and ensure MySQL is running.
*   **404 Errors:** Verify project folder name (`sweet_creations`) and location (`htdocs`), and ensure Apache is running.
*   **Login Issues:** Ensure you're using the correct username format (e.g., `rashni.devi`, not `Rashni Devi`).
