# Sweet Creations Installation Guide

This guide explains how to set up the Sweet Creations project on your local development machine using XAMPP.

## Requirements

*   **XAMPP:** Latest stable version (includes Apache, MySQL, PHP). Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
*   **PHP:** Version 8.0 or higher (comes with recent XAMPP versions).
*   **MySQL:** Version 8.0 or higher (comes with recent XAMPP versions).
*   **Web Browser:** A modern browser like Chrome, Firefox, Edge, or Brave.
*   **Git:** (Optional, but recommended for version control).

## Installation Steps

1.  **Install XAMPP:**
    *   Download and install XAMPP for your operating system (Windows, macOS, Linux).
    *   Follow the installation wizard instructions.

2.  **Start XAMPP Services:**
    *   Open the XAMPP Control Panel.
    *   Start the **Apache** and **MySQL** modules.

3.  **Clone or Download Project Files:**
    *   **Using Git (Recommended):** Open a terminal or command prompt, navigate to your desired parent directory (e.g., `C:\xampp\htdocs\` on Windows, `/Applications/XAMPP/htdocs/` on macOS), and run:
        ```bash
        git clone <repository_url> sweet_creations
        ```
        (Replace `<repository_url>` with the actual URL if you are using a remote repository. If working locally, ensure the `source_code` folder containing the project files is named `sweet_creations` or copy its contents into a `sweet_creations` folder inside `htdocs`).
    *   **Manual Download:** If you have the project files as a ZIP archive, extract them into a folder named `sweet_creations` inside your XAMPP `htdocs` directory.

4.  **Set Up the Database:**
    *   Open your web browser and navigate to **phpMyAdmin** (usually `http://localhost/phpmyadmin`).
    *   You might be prompted for a username and password. By default, XAMPP often uses `root` with no password.
    *   **Import the Database Schema:**
        *   Click on the **Import** tab in phpMyAdmin.
        *   Click **Choose File** and select the `database.sql` file located in the root of the `sweet_creations` project directory.
        *   Ensure the character set is set to `utf8`.
        *   Click the **Go** button at the bottom. This will create the `sweet_creations` database, the necessary tables, and insert initial data (including the admin user).

5.  **Configure Environment Variables:**
    *   In the root of the `sweet_creations` project directory, find the `.env` file.
    *   Open it with a text editor.
    *   Verify the database credentials. If your MySQL setup uses a different username or password than the default (`root` with no password), update the following lines accordingly:
        ```dotenv
        DB_HOST=localhost
        DB_NAME=sweet_creations
        DB_USER=your_mysql_username
        DB_PASS=your_mysql_password
        ```
    *   Save the `.env` file.

6.  **Access the Application:**
    *   Open your web browser and navigate to:
        `http://localhost/sweet_creations/`
    *   You should see the login page.

## Default Login Credentials

*   **Username:** `admin`
*   **Password:** `admin123`

## Troubleshooting

*   **Database Connection Errors:** Double-check the `DB_USER` and `DB_PASS` values in your `.env` file match your MySQL setup. Ensure the MySQL service is running in XAMPP.
*   **Page Not Found (404):** Verify that the project files are correctly placed inside the `htdocs/sweet_creations` directory and that Apache is running.
*   **Permission Issues:** On macOS or Linux, you might need to adjust folder permissions if Apache cannot read project files or write to the `uploads` directory later.
*   **`.env` Not Loading:** Ensure the `.env` file exists in the project root and is readable by the web server process.
