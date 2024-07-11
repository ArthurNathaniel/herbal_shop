<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Stock</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>View Stock</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Price</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['product_name']; ?></td>
                <td><?php echo $row['quantity']; ?></td>
                <td><?php echo $row['price']; ?></td> <!-- Display price -->
            </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
