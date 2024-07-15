<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql_delete = "DELETE FROM expenditures WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $delete_id);
    if ($stmt_delete->execute()) {
        $success_message = "Expenditure deleted successfully.";
    } else {
        $error_message = "Error deleting expenditure: " . $conn->error;
    }
    $stmt_delete->close();
}

// Retrieve all expenditures
$sql_select_expenditures = "SELECT id, date, amount, description, created_at FROM expenditures ORDER BY date DESC";
$result_expenditures = $conn->query($sql_select_expenditures);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>View Expenditures</title>
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
        <div class="table-container">
            <h1>View Expenditures</h1>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount (GH₵)</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_expenditures && $result_expenditures->num_rows > 0): ?>
                        <?php while ($row = $result_expenditures->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td>GH₵<?php echo number_format($row['amount'], 2); ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td class="action-icons">
                                    <a href="edit_expenditure.php?id=<?php echo $row['id']; ?>"><i class="fa-solid fa-pen"></i></a>
                                    <a href="expenditure_view.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this expenditure?');"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No expenditures found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
        </div>
    </div>
</body>

</html>
