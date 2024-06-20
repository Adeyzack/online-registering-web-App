<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include('config.php');

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'admin') {
    header('Location: admin.php');
    exit();
}

// Initialize variables
$selected_semester = 'spring';

// Update the selected semester if posted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['semester'])) {
    $selected_semester = $_POST['semester'];
}

// Fetch available courses for the selected semester
$sql = "SELECT * FROM courses WHERE semester = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $selected_semester);
$stmt->execute();
$courses = $stmt->get_result();
$stmt->close();

// Handle adding courses to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $course_id = $_POST['course_id'];

    // Check if the student has already added the course to the cart for the selected semester
    $stmt = $conn->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ? AND course_id = ? AND semester = ?");
    $stmt->bind_param("iis", $user_id, $course_id, $selected_semester);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<div class='alert alert-danger'>You have already added this course to your cart for the selected semester.</div>";
    } else {
        // Add course to cart
        $stmt = $conn->prepare("INSERT INTO cart (user_id, course_id, semester) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $course_id, $selected_semester);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Course added to cart!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Handle course registration from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Fetch courses from cart
    $stmt = $conn->prepare("SELECT course_id, semester FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    $stmt->close();

    while ($row = $cart_items->fetch_assoc()) {
        $course_id = $row['course_id'];
        $semester = $row['semester'];

        // Register the student for the course
        $stmt = $conn->prepare("INSERT INTO registrations (user_id, course_id, semester) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $course_id, $semester);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Courses registered successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }

    // Clear the cart after registration
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

// Handle dropping courses
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['drop_course_id'])) {
    $registration_id = $_POST['drop_course_id'];

    // Drop the course for the student
    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $registration_id, $user_id);
    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Course dropped successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Student Dashboard</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="edit_profile.php">Edit Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Welcome to the Student Dashboard</h2>
        <h3>Register for Courses</h3>
        <form method="POST" action="dashboard.php">
            <div class="form-group">
                <label for="semester">Select Semester</label>
                <select class="form-control" id="semester" name="semester" onchange="this.form.submit()">
                    <option value="spring" <?= $selected_semester == 'spring' ? 'selected' : '' ?>>Spring</option>
                    <option value="summer" <?= $selected_semester == 'summer' ? 'selected' : '' ?>>Summer</option>
                    <option value="fall" <?= $selected_semester == 'fall' ? 'selected' : '' ?>>Fall</option>
                </select>
            </div>
        </form>
        <form method="POST" action="dashboard.php">
            <input type="hidden" name="semester" value="<?= $selected_semester ?>">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course Name</th>
                        <th>Course Code</th>
                        <th>Instructor</th>
                        <th>Delivery Method</th>
                        <th>Credit Hours</th>
                        <th>Add to Cart</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $courses->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['course_name'] ?></td>
                            <td><?= $row['course_code'] ?></td>
                            <td><?= $row['instructor_name'] ?></td>
                            <td><?= ucfirst($row['delivery_method']) ?></td>
                            <td><?= $row['credit_hours'] ?></td>
                            <td>
                                <form method="POST" action="dashboard.php">
                                    <input type="hidden" name="course_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-primary" name="add_to_cart">Add to Cart</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </form>

        <h3 class="mt-5">Your Cart</h3>
        <?php
        $conn = new mysqli($host, $user, $pass, $db);
        $sql_cart = "SELECT c.id, c.course_name, c.course_code, c.instructor_name, c.delivery_method, c.credit_hours, ct.semester 
                     FROM cart ct JOIN courses c ON ct.course_id = c.id WHERE ct.user_id = ?";
        $stmt_cart = $conn->prepare($sql_cart);
        $stmt_cart->bind_param("i", $user_id);
        $stmt_cart->execute();
        $cart_items = $stmt_cart->get_result();
        $stmt_cart->close();
        $conn->close();
        ?>
        <ul class="list-group">
            <?php while ($row = $cart_items->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $row['course_name'] ?> (<?= $row['course_code'] ?>) - <?= ucfirst($row['delivery_method']) ?> - <?= ucfirst($row['semester']) ?> (<?= $row['credit_hours'] ?> credit hours)
                </li>
            <?php endwhile; ?>
        </ul>
        <form method="POST" action="dashboard.php">
            <button type="submit" class="btn btn-success mt-3" name="register">Register All Courses</button>
        </form>

        <h3 class="mt-5">Registered Courses</h3>
        <?php
        $conn = new mysqli($host, $user, $pass, $db);
        $sql = "SELECT r.id, c.course_name, r.semester, c.credit_hours FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $conn->close();
        ?>
        <ul class="list-group">
            <?php while ($row = $result->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $row['course_name'] ?> (<?= $row['credit_hours'] ?> credit hours) - <?= ucfirst($row['semester']) ?>
                    <form method="POST" action="dashboard.php" class="d-inline">
                        <input type="hidden" name="drop_course_id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Drop</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
