<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all products for dropdown
$sql_select_products = "SELECT id, product_name FROM products";
$result_products = $conn->query($sql_select_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refill Product</title>
</head>
<body>
    <h1>Refill Product</h1>
    <form method="post" action="">
        <label>Select Product:</label>
        <select name="product_id" required>
            <option value="">Select Product</option>
            <?php while ($row = $result_products->fetch_assoc()): ?>
                <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name']; ?></option>
            <?php endwhile; ?>
        </select>
        <br>
        <label>Refill Quantity:</label>
        <input type="number" name="refill_quantity" required>
        <br>
        <button type="submit" name="refill_product">Refill Product</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['refill_product'])) {
        $product_id = $_POST['product_id'];
        $refill_quantity = $_POST['refill_quantity'];

        // Retrieve product name and current quantity
        $sql_select = "SELECT product_name, quantity FROM products WHERE id = $product_id";
        $result = $conn->query($sql_select);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_quantity = $row['quantity'];
            $product_name = $row['product_name'];

            // Update quantity
            $sql_update = "UPDATE products SET quantity = quantity + $refill_quantity WHERE id = $product_id";
            if ($conn->query($sql_update) === TRUE) {
                echo "<p>Product '{$product_name}' refilled successfully. New quantity: " . ($current_quantity + $refill_quantity) . "</p>";
            } else {
                echo "Error updating product quantity: " . $conn->error;
            }
        } else {
            echo "<p>Product not found</p>";
        }
    }
    ?>
</body>
</html>
