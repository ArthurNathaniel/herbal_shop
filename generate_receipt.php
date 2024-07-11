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
            <title>Sales Receipt - #<?php echo $sales_id; ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                }
                .receipt-container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ccc;
                    text-align: center;
                }
                .receipt-header {
                    margin-bottom: 20px;
                }
                .receipt-info {
                    margin-bottom: 10px;
                }
                .receipt-info p {
                    margin: 5px 0;
                }
                .receipt-products {
                    margin-top: 20px;
                }
                .receipt-products p {
                    text-align: left;
                    margin-bottom: 10px;
                }
            </style>
        </head>
        <body>
            <div class="receipt-container">
                <div class="receipt-header">
                    <h1>Sales Receipt</h1>
                    <p>#<?php echo $sales_id; ?></p>
                </div>
                <div class="receipt-info">
                    <p><strong>Date & Time:</strong> <?php echo $formatted_payment_time; ?></p>
                    <p><strong>Payment Method:</strong> <?php echo $payment_method; ?></p>
                    <p><strong>Total Amount:</strong> GH₵<?php echo number_format($total_amount, 2); ?></p>
                </div>
                <div class="receipt-products">
                    <h3>Products Sold:</h3>
                    <?php
                    // Reset the result set pointer to the beginning
                    $result_sales->data_seek(0);

                    // Iterate through each sales item and display it
                    while ($row = $result_sales->fetch_assoc()) {
                    ?>
                        <p>
                            <strong>Product Name:</strong> <?php echo $row['product_name']; ?><br>
                            <strong>Price (GH₵):</strong> GH₵<?php echo number_format($row['price'], 2); ?><br>
                            <strong>Quantity:</strong> <?php echo $row['quantity']; ?><br>
                            <strong>Total Price (GH₵):</strong> GH₵<?php echo number_format($row['total_price'], 2); ?>
                        </p>
                    <?php
                    }
                    ?>
                </div>
                <p style="text-align: center; margin-top: 20px;">
                    <button onclick="window.print();">Print Receipt</button>
                </p>
            </div>
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
