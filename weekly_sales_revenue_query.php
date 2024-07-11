<?php
include 'db.php';
session_start();

// Check if admin session is set
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php"); // Redirect if not logged in
    exit();
}

// Determine the start (Monday) and end (Sunday) dates of the current week
$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

// Query to fetch sales revenue for each product for each day of the current week
$sql_sales = "SELECT p.product_name, DATE(s.payment_time) AS sale_date, SUM(si.quantity) AS total_quantity, SUM(si.total_price) AS total_revenue
              FROM sales_items si
              INNER JOIN sales s ON si.sales_id = s.id
              INNER JOIN products p ON si.product_id = p.id
              WHERE DATE(s.payment_time) BETWEEN ? AND ?
              GROUP BY p.product_name, sale_date
              ORDER BY sale_date, p.product_name";

$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("ss", $monday, $sunday);
$stmt_sales->execute();
$result_sales = $stmt_sales->get_result();

$sales_data = [];
$total_revenue = 0;
if ($result_sales->num_rows > 0) {
    // Fetch sales data and organize by date
    while ($row = $result_sales->fetch_assoc()) {
        $sales_data[$row['sale_date']][] = $row;
        $total_revenue += $row['total_revenue']; // Sum the total revenue
    }
}

// Close statement
$stmt_sales->close();

// Create an array of all days in the week
$week_days = [];
for ($i = 0; $i < 7; $i++) {
    $current_date = date('Y-m-d', strtotime($monday . " + $i days"));
    $week_days[$current_date] = [
        'date' => $current_date,
        'day' => date('l', strtotime($current_date)),
        'sales' => isset($sales_data[$current_date]) ? $sales_data[$current_date] : []
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Admin Weekly Sales Revenue</title>
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
      <h2>Admin Weekly Sales Revenue</h2>
      <br>
        <h3>Week from <?php echo date('F j, Y', strtotime($monday)); ?> to <?php echo date('F j, Y', strtotime($sunday)); ?></h3>
        
      </div>
        <table>
            <thead>
                <tr>
                    <th>Sale Date</th>
                    <th>Day</th>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Revenue (GH₵)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($week_days as $day): ?>
                    <?php if (!empty($day['sales'])): ?>
                        <?php foreach ($day['sales'] as $sale): ?>
                            <tr>
                                <td><?php echo date('F j, Y', strtotime($day['date'])); ?></td>
                                <td><?php echo $day['day']; ?></td>
                                <td><?php echo $sale['product_name']; ?></td>
                                <td><?php echo $sale['total_quantity']; ?></td>
                                <td>GH₵<?php echo number_format($sale['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><?php echo date('F j, Y', strtotime($day['date'])); ?></td>
                            <td><?php echo $day['day']; ?></td>
                            <td colspan="3">No sales</td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="4" style="text-align: right;"><strong>Total Weekly Revenue:</strong></td>
                    <td class="total"><strong>GH₵<?php echo number_format($total_revenue, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
