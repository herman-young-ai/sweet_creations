<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php'; 
require_once '../includes/functions.php';

// Ensure user is logged in
requireLogin();

$pageTitle = 'Edit Product';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    header('Location: products.php?status=invalid_id');
    exit;
}

// Initialize variables
$productData = getProductById($productId);
$originalData = $productData; 
$errors = [];
$successMessage = '';

// Check if product exists
if (!$productData) {
    header('Location: products.php?status=not_found');
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $submittedData = [
        'cake_name' => trim($_POST['cake_name'] ?? ''),
        'base_price' => trim($_POST['base_price'] ?? ''),
        'category' => trim($_POST['category'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'custom_available' => isset($_POST['custom_available']) ? 1 : 0, // Checkbox value
        'size_options' => trim($_POST['size_options'] ?? '')
    ];

    // Validate form data
    $errors = validateProductForm($submittedData);

    // If validation passes, attempt to update product
    if (empty($errors)) {
        if (updateProduct($productId, $submittedData)) {
            // Success!
            $successMessage = "Product updated successfully!";
            $productData = $submittedData; // Update form fields to show saved data
             // Optionally redirect: header('Location: products.php?status=updated'); exit;
        } else {
            $errors['database'] = "Failed to update product in the database. Please try again.";
            $productData = $submittedData; // Keep submitted data in form on DB error
        }
    } else {
         $productData = $submittedData; // Keep submitted data in form if validation fails
    }
}

include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Edit Product: <?php echo htmlspecialchars($originalData['cake_name']); ?> (ID: <?php echo $productId; ?>)</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                 <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $successMessage; ?> <a href="products.php" class="alert-link">Back to List</a>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['database'])): ?>
                     <div class="alert alert-danger alert-dismissible " role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <?php echo $errors['database']; ?>
                    </div>
                <?php endif; ?>

                <form action="edit_product.php?id=<?php echo $productId; ?>" method="post" class="form-horizontal form-label-left">

                     <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 required" for="cake_name">Cake Name</label>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" id="cake_name" name="cake_name" required="required" class="form-control" value="<?php echo htmlspecialchars($productData['cake_name']); ?>">
                            <?php if (!empty($errors['cake_name'])): ?><span class="text-danger"><?php echo $errors['cake_name']; ?></span><?php endif; ?>
                        </div>
                    </div>

                     <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3 required" for="base_price">Base Price (MUR)</label>
                        <div class="col-md-6 col-sm-6">
                            <input type="number" step="0.01" min="0" id="base_price" name="base_price" required="required" class="form-control" value="<?php echo htmlspecialchars($productData['base_price']); ?>">
                             <?php if (!empty($errors['base_price'])): ?><span class="text-danger"><?php echo $errors['base_price']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3" for="category">Category</label>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" id="category" name="category" class="form-control" placeholder="Optional (e.g., Chocolate, Vanilla, Fruit)" value="<?php echo htmlspecialchars($productData['category']); ?>">
                             <?php if (!empty($errors['category'])): ?><span class="text-danger"><?php echo $errors['category']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3" for="description">Description</label>
                        <div class="col-md-6 col-sm-6">
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional"><?php echo htmlspecialchars($productData['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3">Options</label>
                        <div class="col-md-6 col-sm-6">
                             <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="custom_available" value="1" <?php echo !empty($productData['custom_available']) ? 'checked' : ''; ?>> Allow Customization?
                                </label>
                            </div>
                        </div>
                    </div>

                     <div class="form-group row">
                        <label class="control-label col-md-3 col-sm-3" for="size_options">Available Sizes</label>
                        <div class="col-md-6 col-sm-6">
                            <input type="text" id="size_options" name="size_options" class="form-control" placeholder="Optional (e.g., Small, Medium, Large)" value="<?php echo htmlspecialchars($productData['size_options']); ?>">
                             <?php if (!empty($errors['size_options'])): ?><span class="text-danger"><?php echo $errors['size_options']; ?></span><?php endif; ?>
                        </div>
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group row">
                        <div class="col-md-6 col-sm-6 offset-md-3">
                            <a href="products.php" class="btn btn-primary">Cancel / View List</a>
                            <button type="submit" class="btn btn-success">Save Changes</button>
                        </div>
                    </div>

                </form>
            </div> <!-- /x_content -->
        </div> <!-- /x_panel -->
    </div> <!-- /col-md-12 -->
</div> <!-- /row -->

<?php include '../includes/footer.php'; ?> 