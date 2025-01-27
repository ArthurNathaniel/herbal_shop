<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all products for dropdown
$sql_select_products = "SELECT id, product_name, quantity FROM products";
$result_products = $conn->query($sql_select_products);

// Handle form submission
$message = '';
$msg_type = '';

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
            $message = "Product '{$product_name}' refilled successfully. New quantity: " . ($current_quantity + $refill_quantity);
            $msg_type = "success";
        } else {
            $message = "Error updating product quantity: " . $conn->error;
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
    <title>Refill Product</title>
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
            <h1>Refill Product</h1>
        </div>
        <form method="post" action="">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $msg_type; ?>">
                    <?php echo $message; ?>
                    <i class="fa-solid fa-circle-xmark " onclick="this.parentElement.style.display='none';"></i>
                </div>
            <?php endif; ?>
            <div class="forms">
                <label>Select Product:</label>
                <select name="product_id" required>
                    <option value="">Select Product</option>
                    <?php while ($row = $result_products->fetch_assoc()) : ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name']; ?> - Quantity -  <?php echo $row['quantity']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="forms">
                <label>Refill Quantity:</label>
                <input type="number" name="refill_quantity" required>
            </div>

            <div class="forms">
                <button type="submit" name="refill_product">Refill Product</button>
            </div>
        </form>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>
