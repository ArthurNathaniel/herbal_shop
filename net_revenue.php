<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Function to calculate net revenue
function calculate_net_revenue($conn, $start_date, $end_date) {
    // Fetch total sales amount
    $sql_total_sales = "SELECT SUM(total_amount) AS total_sales FROM sales WHERE payment_time BETWEEN ? AND ?";
    $stmt_sales = $conn->prepare($sql_total_sales);
    $stmt_sales->bind_param("ss", $start_date, $end_date);
    $stmt_sales->execute();
    $result_total_sales = $stmt_sales->get_result();
    $total_sales = $result_total_sales->fetch_assoc()['total_sales'];

    // Fetch total expenditure amount
    $sql_total_expenditures = "SELECT SUM(amount) AS total_expenditures FROM expenditures WHERE expenditure_date BETWEEN ? AND ?";
    $stmt_expenditures = $conn->prepare($sql_total_expenditures);
    $stmt_expenditures->bind_param("ss", $start_date, $end_date);
    $stmt_expenditures->execute();
    $result_total_expenditures = $stmt_expenditures->get_result();
    $total_expenditures = $result_total_expenditures->fetch_assoc()['total_expenditures'];

    // Calculate net revenue
    $net_revenue = $total_sales - $total_expenditures;

    return array(
        'total_sales' => $total_sales,
        'total_expenditures' => $total_expenditures,
        'net_revenue' => $net_revenue
    );
}

// Variables to hold revenue data
$daily_revenue = null;
$monthly_revenue = null;
$yearly_revenue = null;

// Handle daily query
if (isset($_POST['query_type']) && $_POST['query_type'] === 'daily') {
    $date = $_POST['date'];
    $start_date = $date . " 00:00:00";
    $end_date = $date . " 23:59:59";
    $daily_revenue = calculate_net_revenue($conn, $start_date, $end_date);
}

// Handle monthly query
if (isset($_POST['query_type']) && $_POST['query_type'] === 'monthly') {
    $year = $_POST['year'];
    $month = $_POST['month'];
    $start_date = $year . "-" . $month . "-01 00:00:00";
    $end_date = date("Y-m-t 23:59:59", strtotime($start_date)); // Last day of the month
    $monthly_revenue = calculate_net_revenue($conn, $start_date, $end_date);
}

// Handle yearly query
if (isset($_POST['query_type']) && $_POST['query_type'] === 'yearly') {
    $year = $_POST['year'];
    $start_date = $year . "-01-01 00:00:00";
    $end_date = $year . "-12-31 23:59:59";
    $yearly_revenue = calculate_net_revenue($conn, $start_date, $end_date);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Net Revenue</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/net_revenue.css">
    <style>
        .net-revenue-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .net-revenue-container h1 {
            margin-bottom: 20px;
            text-align: center;
        }

        .net-revenue-container form {
            margin-bottom: 20px;
            text-align: center;
        }

        .net-revenue-container p {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .net-revenue-container .net-revenue {
            font-size: 2em;
            font-weight: bold;
            color: #4CAF50;
        }

        .net-revenue-container .negative-revenue {
            color: #f44336;
        }

        .net-revenue-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .net-revenue-container table, .net-revenue-container th, .net-revenue-container td {
            border: 1px solid #ccc;
        }

        .net-revenue-container th, .net-revenue-container td {
            padding: 10px;
            text-align: center;
        }

        .net-revenue-container th {
            background-color: #f2f2f2;
        }
    </style>
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
            <h1>Net Revenue</h1>

            <form method="POST" action="">
                <input type="hidden" name="query_type" value="daily">
                <label for="date">Select Date:</label>
                <input type="date" id="date" name="date" required>
                <button type="submit">View Daily Revenue</button>
            </form>

            <?php if ($daily_revenue !== null) : ?>
                <h2>Daily Revenue</h2>
                <p>Total Sales: GH₵<?php echo number_format($daily_revenue['total_sales'], 2); ?></p>
                <p>Total Expenditures: GH₵<?php echo number_format($daily_revenue['total_expenditures'], 2); ?></p>
                <p class="net-revenue <?php if ($daily_revenue['net_revenue'] < 0) echo 'negative-revenue'; ?>">
                    Net Revenue: GH₵<?php echo number_format($daily_revenue['net_revenue'], 2); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="query_type" value="monthly">
                <label for="year">Select Year:</label>
                <select id="year" name="year" required>
                    <?php for ($i = 2024; $i <= 2090; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
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
                <button type="submit">View Monthly Revenue</button>
            </form>

            <?php if ($monthly_revenue !== null) : ?>
                <h2>Monthly Revenue</h2>
                <p>Total Sales: GH₵<?php echo number_format($monthly_revenue['total_sales'], 2); ?></p>
                <p>Total Expenditures: GH₵<?php echo number_format($monthly_revenue['total_expenditures'], 2); ?></p>
                <p class="net-revenue <?php if ($monthly_revenue['net_revenue'] < 0) echo 'negative-revenue'; ?>">
                    Net Revenue: GH₵<?php echo number_format($monthly_revenue['net_revenue'], 2); ?>
                </p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="query_type" value="yearly">
                <label for="yearly_year">Select Year:</label>
                <select id="yearly_year" name="year" required>
                    <?php for ($i = 2024; $i <= 2090; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit">View Yearly Revenue</button>
            </form>

            <?php if ($yearly_revenue !== null) : ?>
                <h2>Yearly Revenue</h2>
                <p>Total Sales: GH₵<?php echo number_format($yearly_revenue['total_sales'], 2); ?></p>
                <p>Total Expenditures: GH₵<?php echo number_format($yearly_revenue['total_expenditures'], 2); ?></p>
                <p class="net-revenue <?php if ($yearly_revenue['net_revenue'] < 0) echo 'negative-revenue'; ?>">
                    Net Revenue: GH₵<?php echo number_format($yearly_revenue['net_revenue'], 2); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
