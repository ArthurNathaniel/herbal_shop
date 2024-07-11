<?php
include 'db.php';
session_start();

// Redirect to login if cashier session is not set
if (!isset($_SESSION['cashier'])) {
    header("Location: cashier_login.php");
    exit();
}

// Initialize cart and total from session, or set to empty array and 0
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$total = isset($_SESSION['total']) ? $_SESSION['total'] : 0;

// Process adding items to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Retrieve product details and remaining stock
    $sql_product = "SELECT product_name, price, quantity AS remaining_stock FROM products WHERE id = $product_id";
    $result_product = $conn->query($sql_product);

    if ($result_product && $result_product->num_rows > 0) {
        $row = $result_product->fetch_assoc();
        $product_name = $row['product_name'];
        $price = $row['price'];
        $remaining_stock = $row['remaining_stock'];

        // Check if requested quantity is available in stock
        if ($quantity > $remaining_stock) {
            echo "Error: Insufficient stock for $product_name. Available stock: $remaining_stock";
        } else {
            // Calculate item total
            $item_total = $price * $quantity;

            // Add item to cart array with current date and time
            $cart[] = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'quantity' => $quantity,
                'price' => $price,
                'item_total' => $item_total,
                'timestamp' => date('Y-m-d H:i:s') // Add current date and time in 24-hour format
            ];

            // Update total cart amount
            $total += $item_total;

            // Store cart and total in session
            $_SESSION['cart'] = $cart;
            $_SESSION['total'] = $total;
        }
    } else {
        echo "Error: Product not found.";
    }
}

// Process clearing a single item from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_item_cart'])) {
    $item_index = $_POST['item_index'];
    if (isset($cart[$item_index])) {
        // Subtract item total from total amount
        $total -= $cart[$item_index]['item_total'];
        // Remove item from cart
        unset($cart[$item_index]);
        // Update session variables
        $_SESSION['cart'] = $cart;
        $_SESSION['total'] = $total;
    }
}

// Process clearing all items from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_all_cart'])) {
    // Clear cart and total in session
    $_SESSION['cart'] = [];
    $_SESSION['total'] = 0;
}

// Process checkout/payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    // Check if cart is not empty
    if (!empty($cart)) {
        // Check if payment mode is selected
        if (isset($_POST['payment_mode']) && ($_POST['payment_mode'] === 'cash' || $_POST['payment_mode'] === 'momo')) {
            $payment_mode = $_POST['payment_mode'];
            $payment_method = ($payment_mode === 'cash') ? 'Cash' : 'Mobile Money (MoMo)';

            // Begin transaction for inserting into sales table
            $conn->begin_transaction();

            try {
                // Insert payment details into sales table
                $insert_payment_sql = "INSERT INTO sales (total_amount, payment_method, payment_time) VALUES (?, ?, NOW())";
                $stmt_payment = $conn->prepare($insert_payment_sql);
                $stmt_payment->bind_param("ds", $total, $payment_method);
                $stmt_payment->execute();

                if ($stmt_payment->affected_rows > 0) {
                    $sales_id = $stmt_payment->insert_id;

                    // Insert cart items into sales_items table
                    $insert_item_sql = "INSERT INTO sales_items (sales_id, product_id, quantity, price, total_price, timestamp) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt_item = $conn->prepare($insert_item_sql);

                    foreach ($cart as $item) {
                        $stmt_item->bind_param("iiidss", $sales_id, $item['product_id'], $item['quantity'], $item['price'], $item['item_total'], $item['timestamp']);
                        $stmt_item->execute();

                        // Deduct sold quantity from product stock
                        $update_stock_sql = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
                        $stmt_stock = $conn->prepare($update_stock_sql);
                        $stmt_stock->bind_param("ii", $item['quantity'], $item['product_id']);
                        $stmt_stock->execute();
                    }

                    // Commit transaction
                    $conn->commit();

                    // Clear cart and total in session after successful insertion
                    $_SESSION['cart'] = [];
                    $_SESSION['total'] = 0;

                    // Redirect to print receipt page with sales ID
                    echo '<script>alert("Checkout successfully!"); window.location.href = "generate_receipt.php?sales_id=' . $sales_id . '";</script>';
                    exit();
                } else {
                    throw new Exception("Error: Failed to insert payment details.");
                }
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                echo $e->getMessage();
            }
        } else {
            echo "Error: Please select a payment mode.";
        }
    } else {
        echo "Error: Cart is empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Point of Sale (POS) System</title>
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
        <div class="title">
            <h1>Point of Sale (POS) System</h1>
        </div>

        <!-- Display cart and items -->
        <div class="forms">
            <h2>Cart</h2>
        </div>
        <table border="1">
            <tr>
                <th>Product Name</th>
                <th>Price (GH₵)</th>
                <th>Quantity</th>
                <th>Item Total (GH₵)</th>
                <th>Date & Time</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cart as $index => $item) : ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td>GH₵<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>GH₵<?php echo number_format($item['item_total'], 2); ?></td>
                    <td><?php echo date('Y-m-d H:i:s', strtotime($item['timestamp'])); ?></td>
                    <td>
                        <form method="post" action="">
                            <input type="hidden" name="item_index" value="<?php echo $index; ?>">
                            <button type="submit" name="clear_item_cart">Clear</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="4"><strong>Total</strong></td>
                <td>GH₵<?php echo number_format($total, 2); ?></td>
                <td>
                    <form method="post" action="">
                        <button type="submit" name="clear_all_cart">Clear All</button>
                    </form>
                </td>
            </tr>
        </table>

        <br>
        <br>
        <div class="forms">
            <h2>Add Item to Cart</h2>
        </div>
        <form method="post" action="">
            <div class="forms">
                <label>Select Product:</label>
                <select name="product_id" required>
                    <option value="">Select Product</option>
                    <?php
                    $sql_select_products = "SELECT id, product_name, price, quantity AS remaining_stock FROM products WHERE quantity > 0";
                    $result_products = $conn->query($sql_select_products);
                    if ($result_products && $result_products->num_rows > 0) {
                        while ($row = $result_products->fetch_assoc()) : ?>
                            <option value="<?php echo $row['id']; ?>">
                                <?php echo $row['product_name'] . ' - GH₵' . number_format($row['price'], 2); ?>
                                (Stock: <?php echo $row['remaining_stock']; ?>)
                            </option>
                    <?php endwhile;
                    } ?>
                </select>
            </div>
            <div class="forms">
                <label>Quantity:</label>
                <input type="number" name="quantity" min="1" required>
            </div>
            <div class="forms">
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </div>
        </form>

        <!-- Form for checkout -->
        <h2>Checkout</h2>
        <form method="post" action="">
            <div class="forms">
                <label>Select Payment Mode:</label>
                <select name="payment_mode" required>
                    <option value="">Select Payment Mode</option>
                    <option value="cash">Cash</option>
                    <option value="momo">Mobile Money (MoMo)</option>
                </select>
            </div>


            <div class="forms">
                <button type="submit" name="checkout">Checkout</button>
            </div>
        </form>
    </div>
</body>

</html>