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

// Query to fetch yearly sales revenue for each product
$sql_yearly_sales = "SELECT 
                        p.product_name, 
                        SUM(si.total_price) AS yearly_revenue 
                      FROM sales_items si
                      INNER JOIN sales s ON si.sales_id = s.id
                      INNER JOIN products p ON si.product_id = p.id
                      WHERE YEAR(s.payment_time) = ?
                      GROUP BY p.product_name
                      ORDER BY p.product_name";

$stmt_yearly_sales = $conn->prepare($sql_yearly_sales);
$stmt_yearly_sales->bind_param("i", $selected_year);
$stmt_yearly_sales->execute();
$result_yearly_sales = $stmt_yearly_sales->get_result();

// Initialize an array to store the sales data
$yearly_sales_data = [];
$total_revenue = 0;

while ($row = $result_yearly_sales->fetch_assoc()) {
    $product_name = $row['product_name'];
    $yearly_revenue = $row['yearly_revenue'];
    $yearly_sales_data[$product_name] = $yearly_revenue;
    $total_revenue += $yearly_revenue;
}

$stmt_yearly_sales->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Yearly Sales Revenue</title>
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
    <h1>Yearly Sales Revenue for <?php echo $selected_year; ?></h1>

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
        <th>Product Name</th>
        <th>Yearly Revenue (GH₵)</th>
    </tr>
    <?php foreach ($yearly_sales_data as $product_name => $yearly_revenue): ?>
        <tr>
            <td><?php echo $product_name; ?></td>
            <td>GH₵<?php echo number_format($yearly_revenue, 2); ?></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <th>Total Revenue</th>
        <td>GH₵<?php echo number_format($total_revenue, 2); ?></td>
    </tr>
</table>
    </div>
</body>
</html>
