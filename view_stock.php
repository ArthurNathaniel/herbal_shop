<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en"> 

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>View Stock</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
    <style>
        .delete-btn {
            padding: 5px 10px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 3px;
        }
        .delete-btn:hover {
            background-color: #c82333;
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
            <h1>View Stock</h1>
        </div>

        <div class="forms">
            <p>To Refill Product or Stock <a href="refill_product.php">Click Here</a></p>
            <br>
            <p>To Remove Product or Stock <a href="remove_product.php">Click Here</a></p>
        </div>

        <div class="forms search">
            <input type="text" id="searchInput" class="search-input" placeholder="Search for products...">
        </div>

        <table id="productsTable">
            <thead>
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <!-- <td><?php echo $row['id']; ?></td> -->
                        <td><?php echo $row['product_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td><?php echo $row['price']; ?></td> <!-- Display price -->
                        <td>
                            <form method="post" action="delete_product.php">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div id="noProducts" class="no-products">No products found</div>

    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#productsTable tbody tr');
            let visibleRows = 0;

            rows.forEach(row => {
                const productName = row.cells[0].textContent.toLowerCase();
                const quantity = row.cells[1].textContent.toLowerCase();
                const price = row.cells[2].textContent.toLowerCase();

                if (productName.includes(filter) || quantity.includes(filter) || price.includes(filter)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('noProducts').style.display = visibleRows === 0 ? 'block' : 'none';
        });
    </script>
</body>

</html>
