<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            background: linear-gradient(to right, #0062E6, #33AEFF);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 30px;
            width: 200px;
            margin: 10px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        h1 {
            margin-bottom: 30px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to UMCC Student Registration</h1>
        <p class="lead">Please login or register to continue.</p>
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-primary">Register</a>
    </div>
</body>
</html>
