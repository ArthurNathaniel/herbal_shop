<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    // Insert expenditure into the database
    $sql_insert_expenditure = "INSERT INTO expenditures (date, amount, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql_insert_expenditure);
    $stmt->bind_param("sds", $date, $amount, $description);

    if ($stmt->execute()) {
        $success_message = "Expenditure added successfully.";
    } else {
        $error_message = "Error adding expenditure: " . $conn->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expenditure</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">

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
        <div class="form-container">
            <div class="title">
                <h1>Add Expenditure</h1>
            </div>
            <?php if (isset($success_message)) : ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)) : ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="forms">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="forms">
                    <label for="amount">Amount (GH₵)</label>
                    <input type="number" step="0.01" id="amount" name="amount" required>
                </div>
                <div class="forms">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                <div class="forms">
                    <button type="submit">Add Expenditure</button>
                </div>
            </form>
        </div>
        <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
    </div>
</body>

</html>