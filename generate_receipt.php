<?php
include 'db.php';

// Check if sales_id is provided in the URL
if (isset($_GET['sales_id'])) {
    $sales_id = $_GET['sales_id'];

    // Query to fetch sales details
    $sql_sales = "SELECT s.id AS sales_id, s.total_amount, s.payment_method, s.payment_time, si.product_id, p.product_name, si.quantity, si.price, si.total_price
                  FROM sales s
                  INNER JOIN sales_items si ON s.id = si.sales_id
                  INNER JOIN products p ON si.product_id = p.id
                  WHERE s.id = ?";

    $stmt_sales = $conn->prepare($sql_sales);
    $stmt_sales->bind_param("i", $sales_id);
    $stmt_sales->execute();
    $result_sales = $stmt_sales->get_result();

    if ($result_sales->num_rows > 0) {
        // Fetch the sales information (first row)
        $sales_info = $result_sales->fetch_assoc();
        $sales_id = $sales_info['sales_id'];
        $total_amount = $sales_info['total_amount'];
        $payment_method = $sales_info['payment_method'];
        $payment_time = $sales_info['payment_time'];

        // Format payment time
        $formatted_payment_time = date('Y-m-d H:i:s', strtotime($payment_time));

        // Display receipt details
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <?php include 'cdn.php' ?>
    <title>Sales Receipt - #<?php echo $sales_id; ?></title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            width: 58mm;
        }

        .receipt-container {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px dashed #000;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-info,
        .receipt-total {
            margin-bottom: 10px;
        }

        .receipt-info p,
        .receipt-total p {
            margin: 0;
        }

        .receipt-products table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-products th,
        .receipt-products td {
            text-align: left;
            padding: 2px 0;
        }

        .receipt-products th {
            border-bottom: 1px dashed #000;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 5px;
            margin-top: 10px;
            background-color: #000;
            color: white;
            border: none;
            text-align: center;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1>Sales Receipt</h1>
            <p>RECEIPT ID: <?php echo $sales_id; ?></p>
        </div>

        <div class="receipt-info">
            <p><strong>Date & Time:</strong> <?php echo $formatted_payment_time; ?></p>
            <p><strong>Payment Method:</strong> <?php echo $payment_method; ?></p>
        </div>

        <div class="receipt-products">
            <h3>Products Sold:</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset the result set pointer to the beginning
                    $result_sales->data_seek(0);

                    // Iterate through each sales item and display it
                    while ($row = $result_sales->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>GH₵<?php echo number_format($row['price'], 2); ?></td>
                        <td>GH₵<?php echo number_format($row['total_price'], 2); ?></td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <div class="receipt-total">
            <p>Grand Total: <b>GH₵<?php echo number_format($total_amount, 2); ?></b></p>
        </div>
        <button class="print-button" onclick="window.print();">Print Receipt</button>
    </div>

    <br>
    <br>
    <a href="pos.php">Return back To POS</a>
</body>

</html>
<?php
    } else {
        echo "Error: No sales found with ID #{$sales_id}.";
    }

    // Close statement and connection
    $stmt_sales->close();
    $conn->close();
} else {
    echo "Error: Sales ID parameter is missing.";
}
?>
