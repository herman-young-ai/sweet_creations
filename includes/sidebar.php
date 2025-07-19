<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse sweet-sidebar">
    <div class="position-sticky pt-3">
        <!-- Sidebar Title Area (REMOVED - Title is in top navbar) -->
        <!-- 
         <div class="sweet-sidebar-title">
            <a href="<?php echo BASE_URL; ?>index.php" class="sweet-sidebar-brand"><i class="fa fa-birthday-cake"></i> <span>Sweet Creations</span></a>
        </div>
        -->

        <!-- sidebar menu -->
        <div id="sidebar-menu">
            <div class="menu_section">
                <h6>General</h6> <!-- Use h6 for smaller heading -->
                <ul class="side-menu nav flex-column"> <!-- Added Bootstrap nav classes -->
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo BASE_URL; ?>index.php">
                            <i class="fa fa-home"></i> Dashboard
                         </a>
                    </li>
                    <li class="nav-item">
                         <a class="nav-link" href="<?php echo BASE_URL; ?>orders/orders.php"> <!-- Link directly to list page -->
                            <i class="fa fa-edit"></i> Orders
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>customers/customers.php"> <!-- Link directly to list page -->
                             <i class="fa fa-users"></i> Customers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>products/products.php"> <!-- Link directly to list page -->
                             <i class="fa fa-cubes"></i> Products
                        </a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>reports/reports.php"> <!-- Link directly to list page -->
                             <i class="fa fa-bar-chart-o"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->

        <!-- Removed Footer Buttons -->
    </div>
</nav> 