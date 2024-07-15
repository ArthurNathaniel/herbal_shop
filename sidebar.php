<div class="sidebar_all">
    <i class="fa-solid fa-circle-xmark close-icon" onclick="this.parentElement.style.display='none';"></i>
    <div class="logos"></div>
  
    <div class="links">
     
        <?php
        // Check if the logged-in user is an admin or a cashier
        if (isset($_SESSION['admin'])) {
            // Links for admin
            echo ' <h3>PRODUCTS</h3>';
            echo '<a href="add_product.php">Add Product</a>';
            echo '<a href="edit_product.php">Edit Product Name</a>';
            echo '<a href="view_stock.php">View  Product Stock </a>';
            echo '<a href="update_price.php">Update Product Price</a>';
            echo '<a href="refill_product.php">Refill  Product Stock</a>';
            echo '<a href="remove_product.php">Remove Product Stock</a>';

            echo ' <h3>EXPENDITURE</h3>';
            echo '<a href="add_expenditure.php">Add Expenditure</a>';
            echo '<a href="expenditure_view.php">View Expenditures</a>';
            
            echo ' <h3>SALES REVENUE</h3>';
            echo '<a href="admin_dashboard.php">Admin Dashboard</a>';
            echo '<a href="cancel_sales.php">Cancel Sales</a>';
            echo '<a href="daily_sales_revenue_query.php">Daily Sales Revenue</a>';
            echo '<a href="weekly_sales_revenue_query.php">Weekly Sales Revenue</a>';
            echo '<a href="monthly_sales_revenue_query.php">Monthly Sales Revenue</a>';
            echo '<a href="yearly_sales_revenue_query.php">Yearly Sales Revenue</a>';

            echo ' <h3>NET REVENUE</h3>';
            echo '<a href="net_revenue_daily.php">Daily Net Revenue</a>';
            echo '<a href="net_revenue_monthly.php">Monthly Net Revenue</a>';
            echo '<a href="net_revenue_yearly.php">Yearly Net Revenue</a>';
        } 
        elseif (isset($_SESSION['cashier'])) {
            echo ' <h3>CASHIER</h3>';
            echo '<a href="cashier_dashboard.php">Cashier Dashboard</a>';
            echo '<a href="pos.php">POS</a>';
           
            echo '<a href="view_sales.php">View Sales</a>';
            echo '<a href="daily_payment_method.php">Daily Payment Method</a>';
        }
        ?>
    </div>
</div>

<button id="toggleButton">
    <i class="fa-solid fa-bars-staggered"></i>
</button>
<p>Menu</p>
<script>
    // Get the button and sidebar elements
    var toggleButton = document.getElementById("toggleButton");
    var sidebar = document.querySelector(".sidebar_all");
    // var icon = toggleButton.querySelector("i");

    // Add click event listener to the button
    toggleButton.addEventListener("click", function() {
        // Toggle the visibility of the sidebar
        if (sidebar.style.display === "none" || sidebar.style.display === "") {
            sidebar.style.display = "block";
            // icon.classList.remove("fa-bars-staggered");
            // icon.classList.add("fa-xmark");
        } else {
            // sidebar.style.display = "none";
            // icon.classList.remove("fa-xmark");
            // icon.classList.add("fa-bars-staggered");
        }
    });
</script>