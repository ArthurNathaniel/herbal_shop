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
            echo "Price updated successfully for product name: $product_name";
        } else {
            echo "Error updating price: " . $conn->error;
        }
    } else {
        echo "Product not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Product Price</title>
</head>
<body>
    <h1>Update Product Price</h1>
    <form method="post" action="">
        <label>Select Product:</label>
        <select name="product_id" required>
            <option value="">Select Product</option>
            <?php while ($row = $result_products->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name'] . ' - GH₵ ' . $row['price']; ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label>New Price:</label>
        <input type="number" step="0.01" name="new_price" required>
        <br>
        <button type="submit" name="update_price">Update Price</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
