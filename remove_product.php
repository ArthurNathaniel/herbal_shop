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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];
    $remove_quantity = $_POST['remove_quantity'];

    // Retrieve product name and current quantity
    $sql_select = "SELECT product_name, quantity FROM products WHERE id = $product_id";
    $result = $conn->query($sql_select);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_quantity = $row['quantity'];
        $product_name = $row['product_name'];

        if ($remove_quantity > $current_quantity) {
            $message = "Error: Cannot remove more than the current stock. Current stock: $current_quantity";
            $msg_type = "error";
        } else {
            // Update quantity
            $sql_update = "UPDATE products SET quantity = quantity - $remove_quantity WHERE id = $product_id";
            if ($conn->query($sql_update) === TRUE) {
                $_SESSION['message'] = "Product '{$product_name}' stock removed successfully. New quantity: " . ($current_quantity - $remove_quantity);
                $_SESSION['msg_type'] = "success";
                header("Location: view_stock.php");
                exit();
            } else {
                $message = "Error updating product quantity: " . $conn->error;
                $msg_type = "error";
            }
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
    <title>Remove Product Stock</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
    <style>
        .message {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            position: relative;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .fa-circle-xmark {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }
    </style>
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
            <h1>Remove Product Stock</h1>
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
                    <?php while ($row = $result_products->fetch_assoc()) : ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name'] . ' (Stock: ' . $row['quantity'] . ')'; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="forms">
                <label>Remove Quantity:</label>
                <input type="number" name="remove_quantity" required>
            </div>

            <div class="forms">
                <button type="submit" name="remove_product">Remove Product</button>
            </div>
        </form>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>
