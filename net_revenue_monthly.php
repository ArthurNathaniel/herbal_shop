<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

function calculate_net_revenue($conn, $start_date, $end_date)
{
    $sql_total_sales = "SELECT SUM(total_amount) AS total_sales FROM sales WHERE payment_time BETWEEN ? AND ?";
    $stmt_sales = $conn->prepare($sql_total_sales);
    $stmt_sales->bind_param("ss", $start_date, $end_date);
    $stmt_sales->execute();
    $result_total_sales = $stmt_sales->get_result();
    $total_sales = $result_total_sales->fetch_assoc()['total_sales'];

    $sql_total_expenditures = "SELECT SUM(amount) AS total_expenditures FROM expenditures WHERE date BETWEEN ? AND ?";
    $stmt_expenditures = $conn->prepare($sql_total_expenditures);
    $stmt_expenditures->bind_param("ss", $start_date, $end_date);
    $stmt_expenditures->execute();
    $result_total_expenditures = $stmt_expenditures->get_result();
    $total_expenditures = $result_total_expenditures->fetch_assoc()['total_expenditures'];

    $net_revenue = $total_sales - $total_expenditures;

    return array(
        'total_sales' => $total_sales,
        'total_expenditures' => $total_expenditures,
        'net_revenue' => $net_revenue
    );
}

$monthly_revenue = null;

if (isset($_POST['query_type']) && $_POST['query_type'] === 'monthly') {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $start_date = $year . "-" . $month . "-01 00:00:00";
    $end_date = date("Y-m-t 23:59:59", strtotime($start_date));
    $monthly_revenue = calculate_net_revenue($conn, $start_date, $end_date);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Net Revenue</title>
    <?php include 'cdn.php'; ?>
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
        <div class="net-revenue-container">
            <h1>Monthly Net Revenue</h1>
            <form method="POST" action="">
                <div class="forms">
                    <input type="hidden" name="query_type" value="monthly">
                    <label for="year">Select Year:</label>
                    <select id="year" name="year" required>
                        <?php for ($i = 2024; $i <= 2090; $i++) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="forms">
                    <label for="month">Select Month:</label>
                    <select id="month" name="month" required>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                </div>
                <div class="forms">
                    <button type="submit">View Monthly Revenue</button>
                </div>
            </form>

            <?php if ($monthly_revenue !== null) : ?>
                <h2>Results for <?php echo htmlspecialchars($_POST['year']) . '-' . htmlspecialchars($_POST['month']); ?></h2>
                <p>Total Sales: GH₵<?php echo number_format($monthly_revenue['total_sales'], 2); ?></p>
                <p>Total Expenditures: GH₵<?php echo number_format($monthly_revenue['total_expenditures'], 2); ?></p>
                <p class="net-revenue <?php if ($monthly_revenue['net_revenue'] < 0) echo 'negative-revenue'; ?>">
                    Net Revenue: GH₵<?php echo number_format($monthly_revenue['net_revenue'], 2); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>