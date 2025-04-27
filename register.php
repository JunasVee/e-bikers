<?php
// Database connection settings
$servername = "localhost";
$dbusername = "e-bikers";
$dbpassword = "0a9s455r"; // MySQL database password
$dbname = "e-bikers"; // Your database name

// Create connection
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Insert into database
    $sql = "INSERT INTO account (username, email, password) VALUES ('$username', '$email', '$password')";
    
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
        header("Location: login.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Bikers - Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="register.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .logo-link { text-align: center; margin-bottom: 20px; }
        .logo-link a { font-size: 26px; font-weight: bold; text-decoration: none; color: #4DA8DA; }
        .logo-link a:hover { color: #3C94C2; }
        .register-container { max-width: 400px; background: white; padding: 30px; border-radius: 10px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); text-align: center; }
        .btn-primary { background-color: #4DA8DA; border: none; }
        .btn-primary:hover { background-color: #3C94C2; }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="w-100 text-center">
            <div class="logo-link">
                <a href="/e-bikers/" class="navbar-brand">E-Bikers</a>
            </div>
            <div class="mx-auto register-container">
                <h2 class="fw-bold text-primary mb-4">Create an Account</h2>
                <form action="register.php" method="POST">
                    <div class="mb-3 text-start">
                        <label for="username" class="form-label fw-semibold">Full Name</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your full name" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
                </form>
                <p class="mt-3 text-muted">Already have an account? <a href="login.php" class="text-primary">Login</a></p>
                <p id="registerMessage" class="mt-2"></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
