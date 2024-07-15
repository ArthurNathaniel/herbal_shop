<?php
include 'db.php';
session_start();

// Redirect to login if cashier session is not set
if (!isset($_SESSION['cashier'])) {
    header("Location: cashier_login.php");
    exit();
}

// Get today's date
$today = date('Y-m-d');

// Query to get sales data for today
$sql_today_sales = "SELECT payment_method, SUM(total_amount) AS total_sales 
                    FROM sales 
                    WHERE DATE(payment_time) = '$today' 
                    GROUP BY payment_method";
$result_today_sales = $conn->query($sql_today_sales);

// Initialize sales data arrays
$cash_sales = 0;
$momo_sales = 0;

// Fetch sales data from the result
if ($result_today_sales && $result_today_sales->num_rows > 0) {
    while ($row = $result_today_sales->fetch_assoc()) {
        if ($row['payment_method'] == 'Cash') {
            $cash_sales = $row['total_sales'];
        } elseif ($row['payment_method'] == 'Mobile Money (MoMo)') {
            $momo_sales = $row['total_sales'];
        }
    }
}

// Calculate total sales
$total_sales = $cash_sales + $momo_sales;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php'; ?>
    <title>Daily Payment Methods</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
</head>

<body>
    <div class="dashboard_grid">
        <div class="side">
            <?php include 'sidebar.php'; ?>
        </div>
        <div class="logout">
            <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
            <p>Logout</p>
        </div>
    </div>
    <div class="all">
        <div class="title">
            <h1>Daily Payment Methods</h1>
        </div>

        <div class="content">
            <h2>Sales for Today (<?php echo $today; ?>)</h2>
            <table border="1">
                <tr>
                    <th>Payment Method</th>
                    <th>Total Sales (GH₵)</th>
                </tr>
                <tr>
                    <td>Cash</td>
                    <td>GH₵<?php echo number_format($cash_sales, 2); ?></td>
                </tr>
                <tr>
                    <td>Mobile Money (MoMo)</td>
                    <td>GH₵<?php echo number_format($momo_sales, 2); ?></td>
                </tr>
                <tr>
                    <th>Total</th>
                    <th>GH₵<?php echo number_format($total_sales, 2); ?></th>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>
