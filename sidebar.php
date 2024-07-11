<div class="sidebar_all">
    <i class="fa-solid fa-circle-xmark close-icon" onclick="this.parentElement.style.display='none';"></i>
    <div class="logo"></div>
  
    <div class="links">
        <h3>Dashboard</h3>
        <?php
        // Check if the logged-in user is an admin or a cashier
        if (isset($_SESSION['admin'])) {
            // Links for admin
            echo '<a href="admin_dashboard.php">Admin Dashboard</a>';
            echo '<a href="daily_sales_revenue_query.php">Daily Sales Revenue</a>';
            echo '<a href="weekly_sales_revenue_query.php">Weekly Sales Revenue</a>';
            echo '<a href="monthly_sales_revenue_query.php">Monthly Sales Revenue</a>';
            echo '<a href="yearly_sales_revenue_query.php">Yearly Sales Revenue</a>';
        } elseif (isset($_SESSION['cashier'])) {
            // Links for cashier
            echo '<a href="cashier_dashboard.php">Cashier Dashboard</a>';
            echo '<a href="pos.php">POS</a>';
        }
        ?>
    </div>
</div>

<button id="toggleButton">
    <i class="fa-solid fa-bars-staggered"></i>
</button>

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