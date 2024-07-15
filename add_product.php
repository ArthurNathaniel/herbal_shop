<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    // Check for duplicate product
    $check_sql = "SELECT * FROM products WHERE product_name = '$product_name' AND price = '$price'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $_SESSION['message'] = "A product with the same name and price already exists.";
        $_SESSION['msg_type'] = "error";
    } else {
        // Insert new product into database
        $sql = "INSERT INTO products (product_name, quantity, price) VALUES ('$product_name', '$quantity', '$price')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "New product added successfully";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $sql . "<br>" . $conn->error;
            $_SESSION['msg_type'] = "error";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Add Product</title>
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
        <h1>Add Product</h1>
    </div>
    <form method="post" action="">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message <?php echo $_SESSION['msg_type']; ?>">
                <?php echo $_SESSION['message']; ?>
                <i class="fa-solid fa-circle-xmark" onclick="this.parentElement.style.display='none';"></i>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['msg_type']); ?>
        <?php endif; ?>

        <div class="forms">
            <label>Product Name:</label>
            <input type="text" name="product_name" required>
        </div>

        <div class="forms">
            <label>Quantity:</label>
            <input type="number" name="quantity" required>
        </div>
        
        <div class="forms">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" required>
        </div>
        
        <div class="forms">
            <button type="submit" name="add_product">Add Product</button>
        </div>
    </form>
</div>
</body>
</html>
