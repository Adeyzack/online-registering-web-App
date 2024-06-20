<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Welcome to the Student Registration System</h2>

        <?php
        if (isset($_SESSION['message'])) {
            echo "<div class='alert alert-info'>" . $_SESSION['message'] . "</div>";
            unset($_SESSION['message']);
        }
        ?>

        <a href="register.php" class="btn btn-primary">Register</a>
        <a href="login.php" class="btn btn-secondary">Login</a>
    </div>
</body>
</html>
