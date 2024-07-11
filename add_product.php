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
    $price = $_POST['price']; // Added price field handling

    // Insert new product into database
    $sql = "INSERT INTO products (product_name, quantity, price) VALUES ('$product_name', '$quantity', '$price')";
    if ($conn->query($sql) === TRUE) {
        echo "New product added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
</head>
<body>
    <h1>Add Product</h1>
    <form method="post" action="">
        <label>Product Name:</label>
        <input type="text" name="product_name" required>
        <br>
        <label>Quantity:</label>
        <input type="number" name="quantity" required>
        <br>
        <label>Price:</label>
        <input type="number" step="0.01" name="price" required> <!-- Added price input -->
        <br>
        <button type="submit" name="add_product">Add Product</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
