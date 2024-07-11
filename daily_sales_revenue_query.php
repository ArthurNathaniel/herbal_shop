<?php
include 'db.php';
session_start();

// Check if admin is logged in

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Get the selected date from the form submission, default to today if not set
$selected_date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

// Query to fetch daily sales revenue for each product
$sql_daily_sales = "SELECT 
                        p.product_name, 
                        SUM(si.total_price) AS daily_revenue 
                    FROM sales_items si
                    INNER JOIN sales s ON si.sales_id = s.id
                    INNER JOIN products p ON si.product_id = p.id
                    WHERE DATE(s.payment_time) = ?
                    GROUP BY p.product_name
                    ORDER BY p.product_name";

$stmt_daily_sales = $conn->prepare($sql_daily_sales);
$stmt_daily_sales->bind_param("s", $selected_date);
$stmt_daily_sales->execute();
$result_daily_sales = $stmt_daily_sales->get_result();

// Initialize an array to store the sales data
$daily_sales_data = [];
$total_revenue = 0;

while ($row = $result_daily_sales->fetch_assoc()) {
    $product_name = $row['product_name'];
    $daily_revenue = $row['daily_revenue'];
    $daily_sales_data[$product_name] = $daily_revenue;
    $total_revenue += $daily_revenue;
}

$stmt_daily_sales->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Daily Sales Revenue</title>
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
        <h1>Daily Sales Revenue for <?php echo $selected_date; ?></h1>
    </div>

    <form method="post" action="">
       <div class="forms">
       <label for="date">Select Date:</label>
       <input type="text" placeholder="Pick a date"  name="date" id="date" required> 
       </div>
     <div class="forms">
     <button type="submit">View</button>
     </div>
    </form>

    <table>
        <tr>
            <th>Product Name</th>
            <th>Daily Revenue (GH₵)</th>
        </tr>
        <?php foreach ($daily_sales_data as $product_name => $daily_revenue): ?>
            <tr>
                <td><?php echo $product_name; ?></td>
                <td>GH₵<?php echo number_format($daily_revenue, 2); ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td>Total Revenue</td>
            <th class="total">GH₵<?php echo number_format($total_revenue, 2); ?></th>
        </tr>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
     flatpickr("#date", {
            dateFormat: "Y-m-d",
            // minDate: "today",
            maxDate: "today",
            disableMobile: true
        });
</script>

</body>
</html>
