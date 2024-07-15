<?php
include 'db.php';
session_start();

if (!isset($_SESSION['cashier'])) {
    header("Location: cashier_login.php");
    exit();
}

$where_clauses = [];
$params = [];
$types = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['query_date'])) {
        $where_clauses[] = 'DATE(s.payment_time) = ?';
        $params[] = $_POST['query_date'];
        $types .= 's';
    }
    if (!empty($_POST['payment_method'])) {
        $where_clauses[] = 's.payment_method = ?';
        $params[] = $_POST['payment_method'];
        $types .= 's';
    }
}

$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Retrieve all sales transactions with product details
$sql_select_sales = "
    SELECT s.id as sales_id, s.total_amount, s.payment_method, s.payment_time, 
           GROUP_CONCAT(p.product_name SEPARATOR ', ') as product_names 
    FROM sales s 
    JOIN sales_items si ON s.id = si.sales_id 
    JOIN products p ON si.product_id = p.id 
    $where_sql
    GROUP BY s.id 
    ORDER BY s.payment_time DESC";

$stmt = $conn->prepare($sql_select_sales);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result_sales = $stmt->get_result();

$total_sales = 0;
$sales_data = [];
while ($row = $result_sales->fetch_assoc()) {
    $total_sales += $row['total_amount'];
    $sales_data[] = $row;
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            <h2>Query Sales</h2>
        </div>
        <form method="post" action="">
            <div class="forms">
                <label>Date:</label>
                <input type="text" id="query_date" name="query_date" placeholder="YYYY-MM-DD" required>
            </div>
            <!-- <div class="forms">
                <label>Payment Method:</label>
                <select name="payment_method">
                    <option value="">Select Payment Method</option>
                    <option value="Cash">Cash</option>
                    <option value="Mobile Money (MoMo)">Mobile Money (MoMo)</option>
                </select>
            </div> -->
            <div class="forms">
                <button type="submit">Query</button>
            </div>
        </form>
        <table border="1">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Total Amount (GH₵)</th>
                    <th>Payment Method</th>
                    <th>Payment Time</th>
                    <th>Products</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($sales_data)) : ?>
                    <?php foreach ($sales_data as $row) : ?>
                        <tr>
                            <td><?php echo $row['sales_id']; ?></td>
                            <td>GH₵<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><?php echo $row['payment_time']; ?></td>
                            <td><?php echo $row['product_names']; ?></td>
                            <td class="print"><a href="generate_receipt.php?sales_id=<?php echo $row['sales_id']; ?>"><i class="fa-solid fa-print"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="6"><strong>Total Sales: GH₵<?php echo number_format($total_sales, 2); ?></strong></td>
                    </tr>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No sales transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="cashier_dashboard.php">Back to Dashboard</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#query_date", {
            dateFormat: "Y-m-d",
            maxDate: "today",
        });
    </script>
</body>

</html>
