<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

// Set page title
$pageTitle = 'Manage Products';

// Handle status messages from redirects (e.g., after delete)
$statusMessage = '';
$messageType = 'info'; // Default type
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'deleted':
            $statusMessage = 'Product successfully deleted.';
            $messageType = 'success';
            break;
        case 'in_use':
            $statusMessage = 'Cannot delete product because it is used in existing orders.';
            $messageType = 'warning';
            break;
        case 'error':
            $statusMessage = 'An error occurred during the operation.';
            $messageType = 'danger';
            break;
         case 'not_found':
            $statusMessage = 'The requested product was not found.';
            $messageType = 'warning';
            break;
        case 'invalid_id':
            $statusMessage = 'Invalid product ID specified.';
            $messageType = 'warning';
            break;
        // Add cases for added/updated if using redirects from add/edit
    }
}

// Handle Search
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$isSearch = !empty($searchTerm);

// Fetch products - either all or based on search
if ($isSearch) {
    $products = searchProducts($searchTerm);
} else {
    $products = getAllProducts('product_id', 'ASC'); // Order by ID
}

// Include header
include '../includes/header.php'; 
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Product List</h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a href="add_product.php" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Add New Product</a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <?php if (!empty($statusMessage)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $statusMessage; ?>
                    </div>
                <?php endif; ?>

                <!-- Search Form -->
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                         <form action="products.php" method="get" class="form-inline float-right">
                            <div class="form-group mr-2 mb-2">
                                <input type="text" name="search" class="form-control" placeholder="Search Cake Name/Category/Description..." title="Search across product names, categories, and descriptions" value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary mb-2"><i class="fa fa-search"></i> Search</button>
                            <?php if ($isSearch): ?>
                                <a href="products.php" class="btn btn-secondary mb-2 ml-1">Clear Search</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                <!-- /Search Form -->

                <?php if ($isSearch && empty($products)): ?>
                     <div class="alert alert-warning mt-3">No products found matching your search term: "<?php echo htmlspecialchars($searchTerm); ?>". <a href="products.php">Show all products.</a></div>
                <?php elseif (!$isSearch && empty($products)): ?>
                    <div class="alert alert-info">No products found. <a href="add_product.php">Add the first one!</a></div>
                <?php else: ?>
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cake Name</th>
                                <th>Category</th>
                                <th>Base Price</th>
                                <th>Customizable?</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['cake_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['category'] ?: 'N/A'); ?></td>
                                <td><?php echo formatCurrency($product['base_price']); ?></td>
                                <td><?php echo $product['custom_available'] ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <!-- <a href="view_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> View</a> -->
                                    <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-primary btn-xs"><i class="fa fa-pencil"></i> Edit</a> <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this product? This cannot be undone.');"><i class="fa fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php 
// Include footer
include '../includes/footer.php'; 
?> 