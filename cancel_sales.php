<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle cancel sale action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_sale'])) {
    $sales_id = $_POST['sales_id'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Retrieve all items in the sale
        $sql_select_items = "SELECT product_id, quantity FROM sales_items WHERE sales_id = $sales_id";
        $result_items = $conn->query($sql_select_items);

        if ($result_items && $result_items->num_rows > 0) {
            while ($row = $result_items->fetch_assoc()) {
                $product_id = $row['product_id'];
                $quantity = $row['quantity'];

                // Revert the stock quantity
                $sql_update_stock = "UPDATE products SET quantity = quantity + $quantity WHERE id = $product_id";
                $conn->query($sql_update_stock);
            }

            // Delete items from sales_items table
            $sql_delete_items = "DELETE FROM sales_items WHERE sales_id = $sales_id";
            $conn->query($sql_delete_items);

            // Delete sale from sales table
            $sql_delete_sale = "DELETE FROM sales WHERE id = $sales_id";
            $conn->query($sql_delete_sale);

            // Commit transaction
            $conn->commit();
            $message = "Sale cancelled successfully.";
            $msg_type = "success";
        } else {
            throw new Exception("Error: Sale items not found.");
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $message = $e->getMessage();
        $msg_type = "error";
    }
}

// Retrieve all sales transactions with product details
$sql_select_sales = "
    SELECT s.id as sales_id, s.total_amount, s.payment_method, s.payment_time, 
           GROUP_CONCAT(p.product_name SEPARATOR ', ') as product_names 
    FROM sales s 
    JOIN sales_items si ON s.id = si.sales_id 
    JOIN products p ON si.product_id = p.id 
    GROUP BY s.id 
    ORDER BY s.payment_time DESC";
$result_sales = $conn->query($sql_select_sales);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Cancel Sales</title>
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
            <h1>Cancel Sales</h1>
        </div>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $msg_type; ?>">
                <?php echo $message; ?>
                <i class="fa-solid fa-circle-xmark close-icon" onclick="this.parentElement.style.display='none';"></i>
            </div>
        <?php endif; ?>
        <table border="1">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Total Amount (GH₵)</th>
                    <th>Payment Method</th>
                    <th>Payment Time</th>
                    <th>Products</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_sales && $result_sales->num_rows > 0) : ?>
                    <?php while ($row = $result_sales->fetch_assoc()) : ?>
                        <tr>
                            <td><?php echo $row['sales_id']; ?></td>
                            <td>GH₵<?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><?php echo $row['payment_time']; ?></td>
                            <td><?php echo $row['product_names']; ?></td>
                            <td>
                                <form method="post" action="" onsubmit="return confirm('Are you sure you want to cancel this sale?');">
                                    <input type="hidden" name="sales_id" value="<?php echo $row['sales_id']; ?>">
                                    <button type="submit" name="cancel_sale">Cancel Sale</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No sales transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>
