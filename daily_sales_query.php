<?php
include 'db.php';
session_start();

// Check if admin session is set
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php"); // Redirect if not logged in
    exit();
}

// Initialize variables
$sales_date = date('Y-m-d'); // Default to today's date
$sales_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query_sales'])) {
    $sales_date = $_POST['sales_date'];

    // Query to fetch sales for the specified date
    $sql_sales = "SELECT s.id AS sales_id, s.total_amount, s.payment_method, s.payment_time, si.product_id, p.product_name, si.quantity, si.price, si.total_price
                  FROM sales s
                  INNER JOIN sales_items si ON s.id = si.sales_id
                  INNER JOIN products p ON si.product_id = p.id
                  WHERE DATE(s.payment_time) = ?";

    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("s", $sales_date);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();

    if ($result_sales->num_rows > 0) {
        // Fetch sales data
        while ($row = $result_sales->fetch_assoc()) {
            $sales_data[] = $row;
        }
    } else {
        echo "No sales found for " . date('F j, Y', strtotime($sales_date));
    }

    // Close statement
    $stmt_sales->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Sales Query</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Sales Query</h2>
        <form method="post" action="">
            <label for="sales_date">Select Date:</label>
            <input type="date" id="sales_date" name="sales_date" value="<?php echo $sales_date; ?>" required>
            <button type="submit" name="query_sales">Query Sales</button>
        </form>

        <?php if (!empty($sales_data)): ?>
            <h3>Sales for <?php echo date('F j, Y', strtotime($sales_date)); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>Sales ID</th>
                        <th>Total Amount (GH₵)</th>
                        <th>Payment Method</th>
                        <th>Payment Time</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price (GH₵)</th>
                        <th>Total Price (GH₵)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales_data as $sale): ?>
                        <tr>
                            <td><?php echo $sale['sales_id']; ?></td>
                            <td><?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td><?php echo $sale['payment_method']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($sale['payment_time'])); ?></td>
                            <td><?php echo $sale['product_name']; ?></td>
                            <td><?php echo $sale['quantity']; ?></td>
                            <td>GH₵<?php echo number_format($sale['price'], 2); ?></td>
                            <td>GH₵<?php echo number_format($sale['total_price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

