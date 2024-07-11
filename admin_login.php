<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $_SESSION['admin'] = $username;
        header("Location: admin_dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php' ?>
    <title>Admin Login</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/login.css">

</head>

<body>
    <div class="login_all">
        <div class="login_swiper">
            <div class="swiper mySwiper">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="./images/1.png" alt="">
                        <div class="swiper_text">
                            <p>
                                Efficiently manage sales and inventory with our intuitive POS system.
                            </p>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="./images/2.png" alt="">
                        <div class="swiper_text">
                            <p>
                                Simplify your sales and inventory with our easy-to-use POS system.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>
        </div>
        <div class="login_forms">
            <form method="post" action="">
                <div class="forms">
                    <h2>Welcome back!</h2>
                    <p>
                        Start managing your finance faster and better
                    </p>
                </div>
                <?php if ($error): ?>
                <div class="forms error">
                    <p><?php echo $error; ?></p>
                   <i class="fa-regular fa-circle-xmark close-icon" onclick="this.parentElement.style.display='none';"></i>
                </div>
                <?php endif; ?>
                <div class="forms">
                    <label>Username:</label>
                    <input type="text" placeholder="Enter your username" name="username" required>
                    <span><i class="fas fa-user"></i></span>
                </div>
                <div class="forms">
                    <label>Password:</label>
                    <input type="password" placeholder="Enter your password" name="password" required>
                    <span><i class="fas fa-lock"></i></span>
                </div>
                <div class="forms">
                    <button type="submit">Login</button>
                </div>
                <div class="forms">
                    <p>Login as a Cashier <a href="cashier_login.php">Click Here</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="./js/swiper.js"></script>
</body>

</html>
