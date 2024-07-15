<?php
include 'db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM cashiers WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['cashier'] = $username;
            header("Location: cashier_dashboard.php"); // Replace with the actual dashboard page
            exit();
        }
    }
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include 'cdn.php'; ?>
    <title>Cashier Login</title>
    <link rel="stylesheet" href="./css/base.css">
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                    <div class="toggle-password"><i class="fa-regular fa-eye-slash"></i></div>
                    <input type="password" id="password" placeholder="Enter your password" name="password" required>
                    <span><i class="fas fa-lock"></i></span>
                </div>
                <div class="forms">
                    <button type="submit">Login</button>
                </div>
                <div class="forms">
                    <p>Login as an Admin <a href="admin_login.php">Click Here</a></p>
                </div>
            </form>
        </div>
    </div>
    <style>
        .toggle-password {
            position: absolute;
            right: 20px !important;
            top: 50%;
            cursor: pointer;
        }
    </style>
    <script src="./js/swiper.js"></script>
    <script>
        // Toggle password visibility
        document.querySelector('.toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    </script>
</body>

</html>
