# Sweet Creations Technical Design Document

## 1. Development Environment

### Technology Stack
- **PHP:** Latest stable version (8.1+)
- **MySQL:** Latest stable version (8.0+)
- **Web Server:** Apache (via XAMPP)
- **Development Environment:** XAMPP on Windows 10/11 and macOS
- **Browsers:** Chrome-based browsers (Chrome, Edge, Brave)

### Tools & Utilities
- **Version Control:** Git
- **Database Administration:** phpMyAdmin (included in XAMPP)
- **PDF Generation:** TCPDF or FPDF library
- **UI Framework:** Bootstrap with Gentelella Admin Theme

## 2. System Architecture

### Simple Directory Structure
```
sweet_creations/
├── assets/              # Static assets (CSS, JS, images)
│   ├── css/             # CSS files and Bootstrap
│   ├── js/              # Minimal JavaScript files
│   ├── images/          # System images
│   └── gentelella/      # Gentelella theme files
├── includes/            # Reusable PHP components
│   ├── config.php       # Database configuration
│   ├── functions.php    # Common functions
│   ├── header.php       # Page header
│   ├── footer.php       # Page footer
│   └── sidebar.php      # Navigation sidebar
├── modules/             # Application modules
│   ├── customers/       # Customer management
│   ├── orders/          # Order management
│   ├── products/        # Product management
│   └── reports/         # Reports generation
├── uploads/             # For any uploaded content
├── index.php            # Entry point
├── login.php            # Login page
└── logout.php           # Logout script
```

### Database Connection
- Simple PDO-based connection class
- Configuration stored in includes/config.php
- Connection parameters easily configurable for different environments

### Session Management
- PHP's built-in session management
- Session timeout after 30 minutes of inactivity
- Session variables for storing user data during navigation

## 3. Database Design

### Physical Database Structure
Based on the ERD created earlier, with the following tables:

1. **USERS**
   - user_id (AUTO_INCREMENT, PRIMARY KEY)
   - username (VARCHAR)
   - password (VARCHAR, hashed)
   - full_name (VARCHAR)
   - email (VARCHAR)
   - role (VARCHAR, 'Admin'/'Staff')
   - last_login (DATETIME)

2. **CUSTOMERS**
   - customer_id (AUTO_INCREMENT, PRIMARY KEY)
   - full_name (VARCHAR)
   - phone_number (VARCHAR)
   - email (VARCHAR)
   - address (TEXT)
   - date_added (DATETIME)
   - notes (TEXT)

3. **PRODUCTS**
   - product_id (AUTO_INCREMENT, PRIMARY KEY)
   - cake_name (VARCHAR)
   - base_price (DECIMAL)
   - category (VARCHAR)
   - description (TEXT)
   - custom_available (BOOLEAN)
   - size_options (VARCHAR)

4. **ORDERS**
   - order_id (AUTO_INCREMENT, PRIMARY KEY)
   - customer_id (INTEGER, FOREIGN KEY)
   - user_id (INTEGER, FOREIGN KEY)
   - order_date (DATETIME)
   - delivery_date (DATE)
   - delivery_time (VARCHAR)
   - delivery_address (TEXT)
   - order_status (VARCHAR)
   - total_amount (DECIMAL)
   - is_paid (BOOLEAN)
   - special_requirements (TEXT)

5. **ORDER_ITEMS**
   - item_id (AUTO_INCREMENT, PRIMARY KEY)
   - order_id (INTEGER, FOREIGN KEY)
   - product_id (INTEGER, FOREIGN KEY)
   - quantity (INTEGER)
   - price (DECIMAL)
   - size (VARCHAR)
   - customization (TEXT)

### Initial Data
- Admin user created during installation
- Sample products for testing
- Sample customers for demo purposes

## 4. Module Specifications

### User Authentication
- Simple username/password authentication
- Password stored using SHA-256 hashing with a salt
- Session-based authentication after login
- Role-based access with two roles: Admin and Staff

### Customer Management
- Add, view, edit, delete customer records
- Search functionality by name, phone, or email
- Customer history view showing past orders
- Validation for phone numbers (Mauritius format: +230 XXXX XXXX)

### Product Management
- Add, view, edit, delete cake products
- Categorization of products
- Option to set available sizes and customization options
- Price management

### Order Management
- Create new orders by selecting customer and products
- Calendar-based delivery date/time selection
- Order status tracking (New, In Progress, Ready, Delivered)
- Order editing and cancellation functionality
- Special requirements and notes section

### Reporting
- Daily order list for production planning
- Delivery schedule reports
- Monthly sales and income reports
- PDF export functionality

## 5. User Interface Design

### Theme Implementation
- Implementation of Bootstrap with Gentelella Admin Theme
- Simplified navigation menu
- Dashboard with key metrics and upcoming orders
- Consistent header, footer, and sidebar across pages

### Key Screens
1. **Login Screen**
   - Username and password inputs
   - Error messaging for failed logins

2. **Dashboard**
   - Summary of tomorrow's orders
   - Recent customers
   - Monthly sales chart (minimal JavaScript)

3. **Customer Screens**
   - Customer listing with search
   - Add/edit customer forms
   - Customer detail view with order history

4. **Order Screens**
   - Order listing with filters
   - Order creation wizard
   - Order details view

5. **Product Screens**
   - Product catalog
   - Add/edit product forms

6. **Report Screens**
   - Report selection interface
   - Report parameter inputs
   - Report display with PDF export option

## 6. Security Considerations

### Authentication
- Simple SHA-256 password hashing with salt
- Parameterized SQL queries for all database operations
- Session expiration after inactivity

### Input Validation
- Basic server-side validation for all inputs
- Special character escaping for database inputs
- CSRF token protection for forms

### Error Handling
- Graceful error messages to users
- Detailed error logging for administrators
- Prevention of sensitive information disclosure

## 7. Data Formats

### Date & Time
- Date stored in MySQL format: YYYY-MM-DD
- Time stored as VARCHAR in HH:MM format (24-hour)
- Date display customizable in the UI

### Currency
- Stored as DECIMAL(10,2) in database
- Displayed in Mauritian Rupee format: MUR 1,000,000.00
- Currency conversion not required (single currency system)

### Phone Numbers
- Mauritius format: +230 XXXX XXXX
- Basic validation to ensure correct format

## 8. Integration Points

### PDF Generation
- Integration with TCPDF or FPDF for generating PDF reports
- Standard templates for order invoices and reports
- Downloadable PDFs for delivery schedules

### Email (Optional Future Enhancement)
- Potential for email notification integration
- Order confirmations and delivery reminders

## 9. Development Approach

### Phase 1: Core System
- Database setup
- User authentication
- Basic CRUD operations for all modules

### Phase 2: Advanced Features
- Reporting functionality
- PDF exports
- Dashboard visualization

### Phase 3: Testing & Refinement
- Data validation
- UI refinements
- Performance optimization

## 10. Deployment Guidelines

### Local Installation
- XAMPP setup on target machine (Windows 10/11 or macOS)
- Database import via phpMyAdmin
- File system permissions configuration
- Initial admin user setup

### Backup Procedure
- Manual database export via phpMyAdmin
- File system backup of the application directory

## 11. Resource Requirements

### Development Resources
- XAMPP installation
- Text editor or simple IDE (VSCode, Sublime Text, etc.)
- Git for version control
- Chrome-based browser for testing

### Skills Required
- Basic PHP programming
- SQL fundamentals
- HTML/CSS understanding
- Basic familiarity with Bootstrap
- Minimal JavaScript knowledge