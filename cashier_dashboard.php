<?php
// Start session and include database connection
include 'db.php';
session_start();

// Check if cashier is logged in
if (!isset($_SESSION['cashier'])) {
    header("Location: login.php");
    exit();
}

$cashierUsername = $_SESSION['cashier'];

// Function to execute SQL query and return revenue data
function getRevenueData($sql)
{
    global $conn;

    $result = $conn->query($sql);

    $productNames = [];
    $revenues = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $productNames[] = $row['product_name'];
            $revenues[] = $row['product_revenue'];
        }
    }

    return [
        'labels' => $productNames,
        'data' => $revenues,
    ];
}

// Today's Revenue
$todaySql = "SELECT p.product_name, SUM(si.total_price) AS product_revenue
             FROM products p
             LEFT JOIN sales_items si ON p.id = si.product_id
             JOIN sales s ON si.sales_id = s.id
             WHERE DATE(s.payment_time) = CURDATE()
             GROUP BY p.id, p.product_name";
$todayData = getRevenueData($todaySql);

// This Week's Revenue
$thisWeekSql = "SELECT p.product_name, SUM(si.total_price) AS product_revenue
                FROM products p
                LEFT JOIN sales_items si ON p.id = si.product_id
                JOIN sales s ON si.sales_id = s.id
                WHERE YEARWEEK(s.payment_time) = YEARWEEK(NOW())
                GROUP BY p.id, p.product_name";
$thisWeekData = getRevenueData($thisWeekSql);

// This Month's Revenue
$thisMonthSql = "SELECT p.product_name, SUM(si.total_price) AS product_revenue
                 FROM products p
                 LEFT JOIN sales_items si ON p.id = si.product_id
                 JOIN sales s ON si.sales_id = s.id
                 WHERE MONTH(s.payment_time) = MONTH(NOW()) AND YEAR(s.payment_time) = YEAR(NOW())
                 GROUP BY p.id, p.product_name";
$thisMonthData = getRevenueData($thisMonthSql);

// This Year's Revenue
$thisYearSql = "SELECT p.product_name, SUM(si.total_price) AS product_revenue
                FROM products p
                LEFT JOIN sales_items si ON p.id = si.product_id
                JOIN sales s ON si.sales_id = s.id
                WHERE YEAR(s.payment_time) = YEAR(NOW())
                GROUP BY p.id, p.product_name";
$thisYearData = getRevenueData($thisYearSql);

// Convert data to JSON format
$todayJsonData = json_encode([
    'labels' => $todayData['labels'],
    'data' => $todayData['data'],
]);

$thisWeekJsonData = json_encode([
    'labels' => $thisWeekData['labels'],
    'data' => $thisWeekData['data'],
]);

$thisMonthJsonData = json_encode([
    'labels' => $thisMonthData['labels'],
    'data' => $thisMonthData['data'],
]);

$thisYearJsonData = json_encode([
    'labels' => $thisYearData['labels'],
    'data' => $thisYearData['data'],
]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body>

    <div class="dashboard_all">
        <div class="dashboard_grid">
            <div class="side">
                <?php include 'sidebar.php' ?>
            </div>

            <div class="logout">
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>
                </a>
                <p> Logout</p>
            </div>
        </div>


        <div class="heading">
            <div class="greeting">Welcome, <?php echo htmlspecialchars($cashierUsername); ?>!</div>

            <h1 style="text-align: center;">Revenue per Product</h1>
        </div>
        <div class="dashboard">
            <div class="chart">
                <h4>Chart for Today</h4>
                <canvas id="todayChart" width="400" height="400"></canvas>
            </div>
            <div class="chart">
                <h4>Chart for This Week</h4>
                <canvas id="thisWeekChart" width="400" height="400"></canvas>
            </div>
            <div class="chart">
                <h4>Chart for This Month</h4>
                <canvas id="thisMonthChart" width="400" height="400"></canvas>
            </div>
            <div class="chart">
                <h4>Chart for This Year</h4>
                <canvas id="thisYearChart" width="400" height="400"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // JavaScript part for Chart.js
        var ctx1 = document.getElementById('todayChart').getContext('2d');
        var ctx2 = document.getElementById('thisWeekChart').getContext('2d');
        var ctx3 = document.getElementById('thisMonthChart').getContext('2d');
        var ctx4 = document.getElementById('thisYearChart').getContext('2d');

        // Today's Revenue Chart (Bar Chart)
        var todayData = <?php echo $todayJsonData; ?>;
        var todayChart = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: todayData.labels,
                datasets: [{
                    label: 'Today Revenue',
                    data: todayData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        callback: function(value, index, values) {
                            return '$' + value.toFixed(2); // Format y-axis labels with $
                        }
                    }
                }
            }
        });

        // This Week's Revenue Chart (Doughnut Chart)
        var thisWeekData = <?php echo $thisWeekJsonData; ?>;
        var thisWeekChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: thisWeekData.labels,
                datasets: [{
                    label: 'This Week Revenue',
                    data: thisWeekData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '$' + tooltipItem.raw.toFixed(2); // Format tooltip with $
                            }
                        }
                    }
                }
            }
        });

        // This Month's Revenue Chart (Pie Chart)
        var thisMonthData = <?php echo $thisMonthJsonData; ?>;
        var thisMonthChart = new Chart(ctx3, {
            type: 'pie',
            data: {
                labels: thisMonthData.labels,
                datasets: [{
                    label: 'This Month Revenue',
                    data: thisMonthData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '$' + tooltipItem.raw.toFixed(2); // Format tooltip with $
                            }
                        }
                    }
                }
            }
        });

        // This Year's Revenue Chart (Bar Chart)
        var thisYearData = <?php echo $thisYearJsonData; ?>;
        var thisYearChart = new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: thisYearData.labels,
                datasets: [{
                    label: 'This Year Revenue',
                    data: thisYearData.data,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        callback: function(value, index, values) {
                            return '$' + value.toFixed(2); // Format y-axis labels with $
                        }
                    }
                }
            }
        });
    </script>
</body>

</html>