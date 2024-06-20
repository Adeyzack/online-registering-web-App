<?php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $activation_key = $_POST['activation_key'];

    // Verify the activation key and username
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND activation_key = ?");
    $stmt->bind_param("ss", $username, $activation_key);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        // Activate the account
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET is_active = 1, activation_key = NULL WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Account activated successfully! You can now log in.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } else {
        echo "<div class='alert alert-danger'>Invalid username or activation key!</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activate Account</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Activate Account</h2>
        <form method="POST" action="activate.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="activation_key">Activation Key</label>
                <input type="text" class="form-control" id="activation_key" name="activation_key" required>
            </div>
            <button type="submit" class="btn btn-primary">Activate</button>
        </form>
    </div>
</body>
</html>
