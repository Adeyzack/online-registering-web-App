<?php
include('config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $middle_name = $_POST['middle_name'];
    $email = $_POST['email'];
    $street_address = $_POST['street_address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip_code = $_POST['zip_code'];
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    
    // Validate password
    $password_regex = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{7,}$/";
    if (!preg_match($password_regex, $password)) {
        echo "<script>alert('Password must be at least 7 characters long, and include at least one special character, one upper and lower case letter, and one numeric value.'); window.history.back();</script>";
        exit();
    }

    // Validate age
    $dob = new DateTime($date_of_birth);
    $today = new DateTime();
    $age = $today->diff($dob)->y;
    if ($age < 16) {
        echo "<script>alert('You must be at least 16 years old to register.'); window.history.back();</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, middle_name, email, street_address, city, state, zip_code, date_of_birth, gender, role, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student', 0)");
    $stmt->bind_param("ssssssssssss", $username, $hashed_password, $first_name, $last_name, $middle_name, $email, $street_address, $city, $state, $zip_code, $date_of_birth, $gender);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful. Please contact admin for account activation.'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function validatePassword() {
            var password = document.getElementById("password").value;
            var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{7,}$/;
            if (!regex.test(password)) {
                alert("Password must be at least 7 characters long, and include at least one special character, one upper and lower case letter, and one numeric value.");
                return false;
            }
            return true;
        }

        function validateAge() {
            var dob = new Date(document.getElementById("date_of_birth").value);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            if (age < 16) {
                alert("You must be at least 16 years old to register.");
                return false;
            }
            return true;
        }

        function validateForm() {
            return validatePassword() && validateAge();
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>User Registration</h2>
        <form action="register.php" method="POST" onsubmit="return validateForm();">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="street_address">Street Address</label>
                <input type="text" class="form-control" id="street_address" name="street_address" required>
            </div>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" class="form-control" id="city" name="city" required>
            </div>
            <div class="form-group">
                <label for="state">State</label>
                <input type="text" class="form-control" id="state" name="state" required>
            </div>
            <div class="form-group">
                <label for="zip_code">ZIP Code</label>
                <input type="text" class="form-control" id="zip_code" name="zip_code" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
</body>
</html>
