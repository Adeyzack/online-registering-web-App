<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

include('config.php');

// Handle account activation and deactivation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['activate_account'])) {
        $user_id = $_POST['user_id'];

        // Activate the account
        $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // Send activation email to the student using PHPMailer
            $stmt_email = $conn->prepare("SELECT email FROM users WHERE id = ?");
            $stmt_email->bind_param("i", $user_id);
            $stmt_email->execute();
            $stmt_email->bind_result($email);
            $stmt_email->fetch();
            $stmt_email->close();

            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'your_email@gmail.com'; // Your Gmail address
                $mail->Password = 'your_app_password'; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('your_email@gmail.com', 'University Admin');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Account Activation';
                $mail->Body = "Dear student, your account has been activated.";

                $mail->send();
                echo "<div class='alert alert-success'>Account activated and email sent successfully!</div>";
            } catch (Exception $e) {
                echo "<div class='alert alert-warning'>Account activated, but failed to send email. Mailer Error: {$mail->ErrorInfo}</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    } elseif (isset($_POST['deactivate_account'])) {
        $user_id = $_POST['user_id'];

        // Deactivate the account
        $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Account deactivated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Handle course deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_course_id'])) {
    $course_id = $_POST['delete_course_id'];

    // Delete all related registrations first
    $stmt = $conn->prepare("DELETE FROM registrations WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    if ($stmt->execute()) {
        // Now delete the course
        $stmt_course = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt_course->bind_param("i", $course_id);
        if ($stmt_course->execute()) {
            echo "<div class='alert alert-success'>Course deleted successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt_course->error . "</div>";
        }
        $stmt_course->close();
    } else {
        echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Add new course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $course_code = $_POST['course_code'];
    $instructor_name = $_POST['instructor_name'];
    $delivery_method = $_POST['delivery_method'];
    $course_description = $_POST['course_description'];
    $credit_hours = $_POST['credit_hours'];
    $semester = $_POST['semester'];

    if (empty($course_name) || empty($course_code) || empty($instructor_name) || empty($delivery_method) || empty($course_description) || empty($credit_hours) || empty($semester)) {
        echo "<div class='alert alert-danger'>Please fill in all fields.</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, instructor_name, delivery_method, course_description, credit_hours, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssssis", $course_name, $course_code, $instructor_name, $delivery_method, $course_description, $credit_hours, $semester);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Course added successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

$sql_courses = "SELECT * FROM courses";
$courses = $conn->query($sql_courses);
if ($courses === false) {
    die("Query failed: " . $conn->error);
}

$sql_students = "SELECT id, username, first_name, last_name, is_active FROM users WHERE role = 'student'";
$students = $conn->query($sql_students);
if ($students === false) {
    die("Query failed: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Admin Dashboard</h2>
        <h3>Activate/Deactivate Student Accounts</h3>
        <ul class="list-group">
            <?php while ($row = $students->fetch_assoc()): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <?= $row['first_name'] . ' ' . $row['last_name'] ?> (<?= $row['username'] ?>)
                    <?php if (!$row['is_active']): ?>
                        <form method="POST" action="admin.php" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm" name="activate_account">Activate</button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="admin.php" class="d-inline">
                            <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn btn-warning btn-sm" name="deactivate_account">Deactivate</button>
                        </form>
                        <span class="badge badge-success">Active</span>
                    <?php endif; ?>
                </li>
            <?php endwhile; ?>
        </ul>

        <h3 class="mt-5">Add New Course</h3>
        <form method="POST" action="admin.php">
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" class="form-control" id="course_name" name="course_name" required>
            </div>
            <div class="form-group">
                <label for="course_code">Course Code</label>
                <input type="text" class="form-control" id="course_code" name="course_code" required>
            </div>
            <div class="form-group">
                <label for="instructor_name">Instructor Name</label>
                <input type="text" class="form-control" id="instructor_name" name="instructor_name" required>
            </div>
            <div class="form-group">
                <label for="delivery_method">Delivery Method</label>
                <select class="form-control" id="delivery_method" name="delivery_method" required>
                    <option value="online">Online</option>
                    <option value="hybrid">Hybrid</option>
                    <option value="onsite">Onsite</option>
                </select>
            </div>
            <div class="form-group">
                <label for="course_description">Course Description</label>
                <textarea class="form-control" id="course_description" name="course_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="credit_hours">Credit Hours</label>
                <input type="number" class="form-control" id="credit_hours" name="credit_hours" required>
            </div>
            <div class="form-group">
                <label for="semester">Semester</label>
                <select class="form-control" id="semester" name="semester" required>
                    <option value="spring">Spring</option>
                    <option value="summer">Summer</option>
                    <option value="fall">Fall</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" name="add_course">Add Course</button>
        </form>

        <h3 class="mt-5">Existing Courses</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Name</th>
                    <th>Course Code</th>
                    <th>Instructor</th>
                    <th>Delivery Method</th>
                    <th>Credit Hours</th>
                    <th>Semester</th>
                    <th>Actions</th>
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
                        <td><?= ucfirst($row['semester']) ?></td>
                        <td>
                            <form method="POST" action="admin.php" class="d-inline">
                                <input type="hidden" name="delete_course_id" value="<?= $row['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="logout.php" class="btn btn-secondary mt-3">Logout</a>
    </div>
</body>
</html>
