<?php
session_start();

// Database credentials
$servername = "localhost";
$dbusername = "e-bikers";
$dbpassword = "0a9s455r"; // MySQL database password
$dbname = "e-bikers"; // Your database name

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Connect to the database
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare the query to fetch user data
    $sql = "SELECT id, email, password FROM account WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $db_email, $db_password);
    $stmt->fetch();

    // Check if the email exists and password matches
    if ($stmt->num_rows == 1 && $password === $db_password) {
        // Store user ID in session
        $_SESSION['user_id'] = $user_id;

        // Set cookies to keep the user logged in (if desired)
        setcookie('user_id', $user_id, time() + 3600 * 24 * 30, "/"); // 30 days expiration
        setcookie('user_email', $db_email, time() + 3600 * 24 * 30, "/");

        // Redirect to the main page
        header("Location: home.php");
        exit();
    } else {
        $error_message = "❌ Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Bikers - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .logo-link {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-link a {
            font-size: 26px;
            font-weight: bold;
            text-decoration: none;
            color: #4DA8DA;
        }

        .logo-link a:hover {
            color: #3C94C2;
        }

        .login-container {
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .btn-primary {
            background-color: #4DA8DA;
            border: none;
        }

        .btn-primary:hover {
            background-color: #3C94C2;
        }
    </style>
</head>
<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <div class="w-100 text-center">
            <div class="logo-link">
                <a href="/e-bikers/" class="navbar-brand">E-Bikers</a>
            </div>
            <div class="mx-auto login-container">
                <h2 class="fw-bold text-primary mb-4">Login</h2>
                <form method="POST" action="login.php">
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email"
                            required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                </form>
                <p class="mt-3 text-muted">Don't have an account? <a href="register.php"
                        class="text-primary">Register</a></p>
                <?php if (isset($error_message)): ?>
                    <p class="mt-2 text-danger"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
