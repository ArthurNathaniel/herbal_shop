<?php
include 'db.php';
session_start();

// Check if admin session is set
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php"); // Redirect if not logged in
    exit();
}
// Get the selected year from the form submission, default to the current year if not set
$selected_year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// Query to fetch monthly sales revenue for each product
$sql_monthly_sales = "SELECT 
                        p.product_name, 
                        MONTH(s.payment_time) AS sale_month, 
                        SUM(si.total_price) AS monthly_revenue 
                      FROM sales_items si
                      INNER JOIN sales s ON si.sales_id = s.id
                      INNER JOIN products p ON si.product_id = p.id
                      WHERE YEAR(s.payment_time) = ?
                      GROUP BY p.product_name, sale_month
                      ORDER BY p.product_name, sale_month";

$stmt_monthly_sales = $conn->prepare($sql_monthly_sales);
$stmt_monthly_sales->bind_param("i", $selected_year);
$stmt_monthly_sales->execute();
$result_monthly_sales = $stmt_monthly_sales->get_result();

// Initialize an array to store the sales data
$monthly_sales_data = [];
$product_names = [];

while ($row = $result_monthly_sales->fetch_assoc()) {
    $product_name = $row['product_name'];
    $sale_month = $row['sale_month'];
    $monthly_revenue = $row['monthly_revenue'];

    if (!isset($monthly_sales_data[$sale_month])) {
        $monthly_sales_data[$sale_month] = [];
    }

    $monthly_sales_data[$sale_month][$product_name] = $monthly_revenue;

    if (!in_array($product_name, $product_names)) {
        $product_names[] = $product_name;
    }
}

$stmt_monthly_sales->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Monthly Sales Revenue</title>
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
   <h1>Monthly Sales Revenue for <?php echo $selected_year; ?></h1>

<form method="post" action="">
   <div class="forms">
   <label for="year">Select Year:</label>
    <select name="year" id="year">
        <?php for ($year = 2024; $year <= 2090; $year++): ?>
            <option value="<?php echo $year; ?>" <?php if ($year == $selected_year) echo 'selected'; ?>><?php echo $year; ?></option>
        <?php endfor; ?>
    </select>
   </div>
    <div class="forms">
    <button type="submit">View</button>
    </div>
</form>

<table>
    <tr>
        <th>Month</th>
        <?php foreach ($product_names as $product_name): ?>
            <th><?php echo $product_name; ?></th>
        <?php endforeach; ?>
        <th>Total</th>
    </tr>
    <?php
    $grand_total = 0;
    for ($month = 1; $month <= 12; $month++) {
        $monthly_total = 0;
        echo "<tr>";
        echo "<td>" . date('F', mktime(0, 0, 0, $month, 10)) . "</td>";
        foreach ($product_names as $product_name) {
            $monthly_revenue = isset($monthly_sales_data[$month][$product_name]) ? $monthly_sales_data[$month][$product_name] : 0;
            echo "<td>GH₵" . number_format($monthly_revenue, 2) . "</td>";
            $monthly_total += $monthly_revenue;
        }
        echo "<td>GH₵" . number_format($monthly_total, 2) . "</td>";
        echo "</tr>";
        $grand_total += $monthly_total;
    }
    ?>
    <tr>
        <th>Total</th>
        <?php
        foreach ($product_names as $product_name) {
            $product_total = 0;
            for ($month = 1; $month <= 12; $month++) {
                $product_total += isset($monthly_sales_data[$month][$product_name]) ? $monthly_sales_data[$month][$product_name] : 0;
            }
            echo "<td class='total'>GH₵" . number_format($product_total, 2) . "</td>";
        }
        ?>
        <td class='total'>GH₵<?php echo number_format($grand_total, 2); ?></td>
    </tr>
</table>
   </div>
</body>
</html>
