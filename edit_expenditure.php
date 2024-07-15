<?php
include 'db.php';
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch expenditure details
if (isset($_GET['id'])) {
    $expenditure_id = $_GET['id'];
    $sql_select_expenditure = "SELECT * FROM expenditures WHERE id = ?";
    $stmt_select = $conn->prepare($sql_select_expenditure);
    $stmt_select->bind_param("i", $expenditure_id);
    $stmt_select->execute();
    $result_expenditure = $stmt_select->get_result();
    $expenditure = $result_expenditure->fetch_assoc();
    $stmt_select->close();
}

// Update expenditure
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];

    $sql_update_expenditure = "UPDATE expenditures SET date = ?, amount = ?, description = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update_expenditure);
    $stmt_update->bind_param("sdsi", $date, $amount, $description, $expenditure_id);

    if ($stmt_update->execute()) {
        $success_message = "Expenditure updated successfully.";
        header("Location: expenditure_view.php");
        exit();
    } else {
        $error_message = "Error updating expenditure: " . $conn->error;
    }
    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Expenditure</title>
    <?php include 'cdn.php'; ?>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/revenue.css">
    <style>
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .forms {
            margin-bottom: 15px;
        }

        .forms label {
            display: block;
            margin-bottom: 5px;
        }

        .forms input,
        .forms textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        .forms button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .forms button:hover {
            background-color: #0056b3;
        }
    </style>
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
            <h1>Edit Expenditure</h1>
            <?php if (isset($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php elseif (isset($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="forms">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo $expenditure['date']; ?>" required>
                </div>
                <div class="forms">
                    <label for="amount">Amount (GH₵)</label>
                    <input type="number" step="0.01" id="amount" name="amount" value="<?php echo $expenditure['amount']; ?>" required>
                </div>
                <div class="forms">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required><?php echo $expenditure['description']; ?></textarea>
                </div>
                <div class="forms">
                    <button type="submit">Update Expenditure</button>
                </div>
            </form>
            <p><a href="expenditure_view.php">Back to Expenditures</a></p>
        </div>
    </div>
</body>

</html>
