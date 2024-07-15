<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all products with their prices for dropdown
$sql_select_products = "SELECT id, product_name, price FROM products";
$result_products = $conn->query($sql_select_products);

// Handle form submission
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_price'])) {
    $product_id = $_POST['product_id'];
    $new_price = $_POST['new_price'];

    // Get product name for feedback message
    $sql_get_product_name = "SELECT product_name FROM products WHERE id = $product_id";
    $result_product_name = $conn->query($sql_get_product_name);
    if ($result_product_name->num_rows > 0) {
        $row = $result_product_name->fetch_assoc();
        $product_name = $row['product_name'];

        // Update product price
        $sql_update_price = "UPDATE products SET price = '$new_price' WHERE id = $product_id";
        if ($conn->query($sql_update_price) === TRUE) {
            $message = "Price updated successfully for product name: $product_name";
            $msg_type = "success";
        } else {
            $message = "Error updating price: " . $conn->error;
            $msg_type = "error";
        }
    } else {
        $message = "Product not found";
        $msg_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Update Product Price</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
   
</head>
<body>
<div class="dashboard_grid">
    <div class="side">
        <?php include 'sidebar.php' ?>
    </div>
    <div class="logout">
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
        <p> Logout</p>
    </div>
</div>
<div class="all">
    <div class="title">
        <h1>Update Product Price</h1>
    </div>
    <form method="post" action="">
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $msg_type; ?>">
                <?php echo $message; ?>
                <i class="fa-solid fa-circle-xmark" onclick="this.parentElement.style.display='none';"></i>
            </div>
        <?php endif; ?>

        <div class="forms">
            <label>Select Product:</label>
            <select name="product_id" required>
                <option value="">Select Product</option>
                <?php while ($row = $result_products->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name'] . ' - GH₵ ' . $row['price']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="forms">
            <label>New Price:</label>
            <input type="number" step="0.01" name="new_price" required>
        </div>

        <div class="forms">
            <button type="submit" name="update_price">Update Price</button>
        </div>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</div>
</body>
</html>
