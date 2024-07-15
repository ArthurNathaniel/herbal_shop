<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cashier'])) {
    header("Location: cashier_login.php");
    exit();
}

$total_sales = array_fill(1, 12, 0); // Initialize an array for total sales per month

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['query_year'])) {
        $query_year = $_POST['query_year'];

        // Retrieve all sales transactions for the specified year
        $sql_select_sales = "
            SELECT s.id as sales_id, s.total_amount, s.payment_method, s.payment_time, 
                   GROUP_CONCAT(p.product_name SEPARATOR ', ') as product_names 
            FROM sales s 
            JOIN sales_items si ON s.id = si.sales_id 
            JOIN products p ON si.product_id = p.id 
            WHERE YEAR(s.payment_time) = ?
            GROUP BY s.id 
            ORDER BY s.payment_time DESC";

        $stmt = $conn->prepare($sql_select_sales);
        $stmt->bind_param('i', $query_year);
        $stmt->execute();
        $result_sales = $stmt->get_result();

        $sales_data = [];
        while ($row = $result_sales->fetch_assoc()) {
            $month = (int)date('m', strtotime($row['payment_time']));
            $total_sales[$month] += $row['total_amount'];
            $sales_data[$month][] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>View Sales</title>
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
            <h1>View Sales</h1>
        </div>
        <div class="forms">
            <h2>Query Sales by Year</h2>
        </div>
        <form method="post" action="">
            <div class="forms">
                <label>Year:</label>
                <select name="query_year" required>
                    <option value="">Select Year</option>
                    <?php for ($year = 2024; $year <= 2090; $year++) : ?>
                        <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="forms">
                <button type="submit">Query</button>
            </div>
        </form>
        <table border="1">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Sale ID</th>
                    <th>Total Amount (GH₵)</th>
                    <th>Payment Method</th>
                    <th>Payment Time</th>
                    <th>Products</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $months = [
                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                ];
                foreach ($months as $month_num => $month_name) : ?>
                    <tr>
                        <td colspan="6" style="font-weight: bold;"><?php echo $month_name; ?></td>
                    </tr>
                    <?php if (!empty($sales_data[$month_num])) : ?>
                        <?php foreach ($sales_data[$month_num] as $row) : ?>
                            <tr>
                                <td></td>
                                <td><?php echo $row['sales_id']; ?></td>
                                <td>GH₵<?php echo number_format($row['total_amount'], 2); ?></td>
                                <td><?php echo $row['payment_method']; ?></td>
                                <td><?php echo $row['payment_time']; ?></td>
                                <td><?php echo $row['product_names']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No sales transactions found for <?php echo $month_name; ?>.</td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="6" style="font-weight: bold;">Total for <?php echo $month_name; ?>: GH₵<?php echo number_format($total_sales[$month_num], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a href="cashier_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>
