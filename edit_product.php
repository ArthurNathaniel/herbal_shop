<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all products for the dropdown
$sql_select_products = "SELECT id, product_name FROM products";
$result_products = $conn->query($sql_select_products);

// Handle form submission
$message = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'];
    $new_product_name = $_POST['new_product_name'];

    // Update product name
    $sql_update = "UPDATE products SET product_name = '$new_product_name' WHERE id = $product_id";
    if ($conn->query($sql_update) === TRUE) {
        $message = "Product name updated successfully.";
        $msg_type = "success";
    } else {
        $message = "Error updating product name: " . $conn->error;
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
    <title>Edit Product Name</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
    <style>
        .message {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
            font-size: 1em;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .close-icon {
            float: right;
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
            <h1>Edit Product Name</h1>
        </div>
        <form method="post" action="">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $msg_type; ?>">
                    <?php echo $message; ?>
                    <i class="fa-solid fa-circle-xmark close-icon" onclick="this.parentElement.style.display='none';"></i>
                </div>
            <?php endif; ?>
            <div class="forms">
                <label>Select Product:</label>
                <select name="product_id" required>
                    <option value="">Select Product</option>
                    <?php while ($row = $result_products->fetch_assoc()) : ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['product_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="forms">
                <label>New Product Name:</label>
                <input type="text" name="new_product_name" required>
            </div>

            <div class="forms">
                <button type="submit" name="edit_product">Update Product Name</button>
            </div>
        </form>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>
