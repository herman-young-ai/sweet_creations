# Sweet Creations - Cake Shop Management System

Sweet Creations is a simple web-based application designed to help a local cake shop manage customer information, product details, and orders. It provides basic CRUD (Create, Read, Update, Delete) functionality for these core areas, along with simple reporting capabilities.

This project is developed as part of an IB Computer Science Internal Assessment, aiming for clarity and simplicity suitable for the specified level.

## Project Status

*   **Phase 0: Setup & Config - COMPLETE**
    *   [X] Project structure initialized.
    *   [X] Git repository set up.
    *   [X] `.env` file created for configuration.
    *   [X] Database connection configured.
    *   [X] Database schema and initial data created (`database.sql`).
    *   [X] Installation instructions drafted (`INSTALL.md`).
*   **Phase 1: Core System & Auth - COMPLETE**
    *   [X] Base layout/templates created (`header`, `footer`, `sidebar`).
    *   [X] Assets integrated (Bootstrap, Gentelella).
    *   [X] User login functionality implemented.
    *   [X] User logout functionality implemented.
    *   [X] Session management and page protection (`requireLogin`).
*   **Phase 2: Modules (CRUD) - COMPLETE**
    *   [X] Customer Management (Add, List, View, Edit, Delete, Search).
    *   [X] Product Management (Add, List, Edit, Delete).
    *   [X] Order Management (Add Wizard, List, View, Edit - status/delivery).
*   **Phase 3: Dashboard & Reporting - COMPLETE (Basic)**
    *   [X] Dashboard widgets implemented (Counts, Income).
    *   [X] Upcoming orders list on dashboard.
    *   [X] Reporting menu created.
    *   [X] Daily Production report implemented (HTML view).
    *   [X] Delivery Schedule report implemented (HTML view).
    *   [X] Monthly Sales report implemented (HTML view).
*   **Phase 4: Security, Refinement & Testing - IN PROGRESS**
    *   [ ] CSRF Protection implementation.
    *   [ ] Input sanitization/validation review.
    *   [ ] Error handling review.
    *   [ ] UI/UX refinements.
    *   [X] Initial manual testing performed.
*   **Future / Optional:**
    *   [ ] PDF export for reports.
    *   [ ] More detailed reporting options.
    *   [ ] User management (Admin role).
    *   [ ] AJAX for smoother interactions (e.g., adding order items).

## Technology Stack

*   **PHP:** 8.0+
*   **MySQL:** 8.0+
*   **Web Server:** Apache (via XAMPP)
*   **Frontend:** HTML, CSS, Basic JavaScript, Bootstrap (via Gentelella Admin Theme)

## Getting Started

Please refer to the [Installation Guide](INSTALL.md) for detailed setup instructions.

## Usage

Basic usage instructions will be added to [USAGE.md](USAGE.md) as features are developed.

## Documentation

*   [Technical Design Document](docs/1_technical_design_document.md)
*   [Coding Requirements](docs/2_coding_requirements.md)
*   [Implementation Checklist](docs/3_implementation_checklist.md)
