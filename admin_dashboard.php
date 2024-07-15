<?php
// Start session and include database connection
include 'db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$cashierUsername = $_SESSION['admin'];

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

// Function to get customer count
function getCustomerCount($sql)
{
    global $conn;
    $result = $conn->query($sql);
    $count = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $count = $row['customer_count'];
    }
    return $count;
}

// Function to get total sales
function getTotalSales($sql)
{
    global $conn;
    $result = $conn->query($sql);
    $total = 0;
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $total = $row['total_sales'];
    }
    return $total;
}

// Customer count for today
$todayCustomerSql = "SELECT COUNT(DISTINCT id) AS customer_count
                     FROM sales
                     WHERE DATE(payment_time) = CURDATE()";
$todayCustomerCount = getCustomerCount($todayCustomerSql);

// Customer count for this week
$thisWeekCustomerSql = "SELECT COUNT(DISTINCT id) AS customer_count
                        FROM sales
                        WHERE YEARWEEK(payment_time) = YEARWEEK(NOW())";
$thisWeekCustomerCount = getCustomerCount($thisWeekCustomerSql);

// Customer count for this month
$thisMonthCustomerSql = "SELECT COUNT(DISTINCT id) AS customer_count
                         FROM sales
                         WHERE MONTH(payment_time) = MONTH(NOW()) AND YEAR(payment_time) = YEAR(NOW())";
$thisMonthCustomerCount = getCustomerCount($thisMonthCustomerSql);

// Customer count for this year
$thisYearCustomerSql = "SELECT COUNT(DISTINCT id) AS customer_count
                        FROM sales
                        WHERE YEAR(payment_time) = YEAR(NOW())";
$thisYearCustomerCount = getCustomerCount($thisYearCustomerSql);

// Customer count for all time
$allTimeCustomerSql = "SELECT COUNT(DISTINCT id) AS customer_count FROM sales";
$allTimeCustomerCount = getCustomerCount($allTimeCustomerSql);

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

// Total sales for today
$todayTotalSalesSql = "SELECT SUM(total_price) AS total_sales
                       FROM sales_items si
                       JOIN sales s ON si.sales_id = s.id
                       WHERE DATE(s.payment_time) = CURDATE()";
$todayTotalSales = getTotalSales($todayTotalSalesSql);

// Total sales for this week
$thisWeekTotalSalesSql = "SELECT SUM(total_price) AS total_sales
                          FROM sales_items si
                          JOIN sales s ON si.sales_id = s.id
                          WHERE YEARWEEK(s.payment_time) = YEARWEEK(NOW())";
$thisWeekTotalSales = getTotalSales($thisWeekTotalSalesSql);

// Total sales for this month
$thisMonthTotalSalesSql = "SELECT SUM(total_price) AS total_sales
                           FROM sales_items si
                           JOIN sales s ON si.sales_id = s.id
                           WHERE MONTH(s.payment_time) = MONTH(NOW()) AND YEAR(s.payment_time) = YEAR(NOW())";
$thisMonthTotalSales = getTotalSales($thisMonthTotalSalesSql);

// Total sales for this year
$thisYearTotalSalesSql = "SELECT SUM(total_price) AS total_sales
                          FROM sales_items si
                          JOIN sales s ON si.sales_id = s.id
                          WHERE YEAR(s.payment_time) = YEAR(NOW())";
$thisYearTotalSales = getTotalSales($thisYearTotalSalesSql);

// Total sales for all time
$allTimeTotalSalesSql = "SELECT SUM(total_price) AS total_sales FROM sales_items";
$allTimeTotalSales = getTotalSales($allTimeTotalSalesSql);

// Convert data to JSON format for charts
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body>

    <div class="dashboard_all">
        <div class="dashboard_grid">
            <div class="side">
                <?php include 'sidebar.php' ?>
            </div>

            <div class="logout">
                <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
                <p> Logout</p>
            </div>
        </div>

        <div class="heading">
            <div class="greeting">
            <p>Welcome, <?php echo htmlspecialchars($cashierUsername); ?>!</p>

                <div id="clock"></div>
            </div>
        </div>
      
        <div class="swiper mySwiper2">
            <h1>Customers</h1>
            <div class="swiper-wrapper">
                <div class="swiper-slide c_box customer1">
                    <h3>Today:</h3>
                    <h1><?php echo $todayCustomerCount; ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer2">
                    <h3>This Week:</h3>
                    <h1> <?php echo $thisWeekCustomerCount; ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer3">
                    <h3>This Month:</h3>
                    <h1> <?php echo $thisMonthCustomerCount; ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer4">
                    <h3>This Year:</h3>
                    <h1><?php echo $thisYearCustomerCount; ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>

                <div class="swiper-slide c_box customer5">
                    <h3>All Time: </h3>
                    <h1> <?php echo $allTimeCustomerCount; ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
            </div>
        </div>
    
        <div class="swiper mySwiper3">
            <h1>Total Sales</h1>
            <div class="swiper-wrapper">
                <div class="swiper-slide c_box customer5">
                    <h3>Today:</h3>
                    <h1>₵<?php echo number_format($todayTotalSales, 2); ?></h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer4">
                    <h3>This Week:</h3>
                    <h1> ₵<?php echo number_format($thisWeekTotalSales, 2); ?></h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer2">
                    <h3>This Month:</h3>
                    <h1> ₵<?php echo number_format($thisMonthTotalSales, 2); ?> </h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
                <div class="swiper-slide c_box customer3">
                    <h3>This Year:</h3>
                    <h1> ₵<?php echo number_format($thisYearTotalSales, 2); ?></h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>

                <div class="swiper-slide c_box customer1">
                    <h3>All Time: </h3>
                    <h1> ₵<?php echo number_format($allTimeTotalSales, 2); ?></h1>
                    <i class="fa-solid fa-user-group"></i>
                </div>
            </div>
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
    <script src="./js/swiper.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            let hours = now.getHours();
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12; // the hour '0' should be '12'
            const hoursString = hours.toString().padStart(2, '0');
            const timeString = `${hoursString}:${minutes}:${seconds} ${ampm}`;
            document.getElementById('clock').textContent = timeString;
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);
        });

        function createChart(chartId, labels, data) {
            const ctx = document.getElementById(chartId).getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Revenue',
                        data: data,
                        backgroundColor: [
    'rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)', 'rgb(153, 102, 255)',
    'rgb(201, 203, 207)', 'rgb(255, 159, 64)', 'rgb(255, 99, 71)', 'rgb(144, 238, 144)', 'rgb(173, 216, 230)',
    'rgb(250, 128, 114)', 'rgb(255, 69, 0)', 'rgb(255, 20, 147)', 'rgb(138, 43, 226)', 'rgb(139, 69, 19)',
    'rgb(47, 79, 79)', 'rgb(112, 128, 144)', 'rgb(119, 136, 153)', 'rgb(0, 255, 255)', 'rgb(0, 128, 128)',
    'rgb(123, 104, 238)', 'rgb(72, 61, 139)', 'rgb(106, 90, 205)', 'rgb(240, 230, 140)', 'rgb(255, 140, 0)',
    'rgb(255, 215, 0)', 'rgb(255, 248, 220)', 'rgb(240, 255, 255)', 'rgb(70, 130, 180)', 'rgb(176, 196, 222)',
    'rgb(220, 20, 60)', 'rgb(255, 182, 193)', 'rgb(255, 160, 122)', 'rgb(250, 250, 210)', 'rgb(127, 255, 0)',
    'rgb(173, 255, 47)', 'rgb(0, 250, 154)', 'rgb(144, 238, 144)', 'rgb(32, 178, 170)', 'rgb(0, 255, 127)',
    'rgb(50, 205, 50)', 'rgb(255, 127, 80)', 'rgb(222, 184, 135)', 'rgb(255, 228, 196)', 'rgb(255, 218, 185)',
    'rgb(218, 112, 214)', 'rgb(186, 85, 211)', 'rgb(148, 0, 211)', 'rgb(153, 50, 204)', 'rgb(147, 112, 219)'
],
                        // borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₵' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateClock();
            setInterval(updateClock, 1000);

            const todayData = <?php echo $todayJsonData; ?>;
            createChart('todayChart', todayData.labels, todayData.data);

            const thisWeekData = <?php echo $thisWeekJsonData; ?>;
            createChart('thisWeekChart', thisWeekData.labels, thisWeekData.data);

            const thisMonthData = <?php echo $thisMonthJsonData; ?>;
            createChart('thisMonthChart', thisMonthData.labels, thisMonthData.data);

            const thisYearData = <?php echo $thisYearJsonData; ?>;
            createChart('thisYearChart', thisYearData.labels, thisYearData.data);
        });
    </script>
</body>

</html>