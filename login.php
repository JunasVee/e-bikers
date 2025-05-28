<?php
session_start();

// Database credentials
$servername = "localhost";
$dbusername = "e-bikers";
$dbpassword = "0a9s455r";
$dbname     = "e-bikers";

// --- GOOGLE OAUTH CONFIG --- //
$google_client_id     = "1040536887122-jcvsfrrlpujnn5pmevahi4ongcso3hcb.apps.googleusercontent.com";
$google_client_secret = "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";
$google_redirect_uri  = "https://sites.its.ac.id/inovasidigital/e-bikers/login.php";

// --- HANDLE GOOGLE OAUTH REDIRECT --- //
if (isset($_GET['code'])) {
    // 1. Exchange code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $post_data = [
        'code' => $_GET['code'],
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'redirect_uri' => $google_redirect_uri,
        'grant_type' => 'authorization_code',
    ];
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($post_data),
        ]
    ];
    $context = stream_context_create($options);
    $response = file_get_contents($token_url, false, $context);
    $token = json_decode($response, true);

    if (isset($token['access_token'])) {
        // 2. Fetch user info
        $userinfo_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
        $userinfo_opts = [
            'http' => [
                'header' => "Authorization: Bearer " . $token['access_token']
            ]
        ];
        $userinfo_context = stream_context_create($userinfo_opts);
        $userinfo_response = file_get_contents($userinfo_url, false, $userinfo_context);
        $userinfo = json_decode($userinfo_response, true);

        if (isset($userinfo['email'])) {
            // 3. Log in or register the user automatically
            $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
            if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

            $email = $userinfo['email'];
            $username = isset($userinfo['name']) ? $userinfo['name'] : explode('@', $email)[0];
            $profile_picture = isset($userinfo['picture']) ? $userinfo['picture'] : null;
            $created_at = date('Y-m-d H:i:s');

            // Check if user exists
            $sql = "SELECT id FROM account WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // User exists, log them in
                $stmt->bind_result($user_id);
                $stmt->fetch();
            } else {
                // New user, create an account with ALL info!
                $sql2 = "INSERT INTO account (username, password, email, profile_picture, created_at) VALUES (?, ?, ?, ?, ?)";
                $random_pw = bin2hex(random_bytes(8)); // random password
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("sssss", $username, $random_pw, $email, $profile_picture, $created_at);
                $stmt2->execute();
                $user_id = $stmt2->insert_id;
                $stmt2->close();
            }
            $stmt->close();
            $conn->close();

            // Store user ID in session and cookie
            $_SESSION['user_id'] = $user_id;
            setcookie('user_id', $user_id, time() + 3600 * 24 * 30, "/");
            setcookie('user_email', $email, time() + 3600 * 24 * 30, "/");

            // Redirect to main page
            header("Location: home.php");
            exit();
        } else {
            $error_message = "❌ Failed to get user info from Google.";
        }
    } else {
        $error_message = "❌ Failed to authenticate with Google.";
    }
}

// --- HANDLE LOCAL LOGIN --- //
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Connect to the database
    $conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT id, email, password FROM account WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $db_email, $db_password);
    $stmt->fetch();

    // Check password (no hashing in your code, should use hashing in production)
    if ($stmt->num_rows == 1 && $password === $db_password) {
        $_SESSION['user_id'] = $user_id;
        setcookie('user_id', $user_id, time() + 3600 * 24 * 30, "/");
        setcookie('user_email', $db_email, time() + 3600 * 24 * 30, "/");
        header("Location: home.php");
        exit();
    } else {
        $error_message = "❌ Invalid email or password.";
    }

    $stmt->close();
    $conn->close();
}

// --- GOOGLE OAUTH LOGIN URL --- //
$google_login_url =
    "https://accounts.google.com/o/oauth2/v2/auth"
    . "?client_id=" . urlencode($google_client_id)
    . "&redirect_uri=" . urlencode($google_redirect_uri)
    . "&response_type=code"
    . "&scope=" . urlencode("openid email profile")
    . "&access_type=online"
    . "&prompt=select_account";
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
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
                </form>
                <div class="mt-2">
                    <a href="<?= $google_login_url ?>"
                        class="btn border shadow-sm d-flex align-items-center justify-content-center py-2"
                        style="background: #fff; border-color: #dadce0; gap: 10px; font-weight: 500; font-size: 16px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c1/Google_%22G%22_logo.svg/36px-Google_%22G%22_logo.svg.png" alt="Google" style="width: 24px; height: 24px;">
                        <span style="color: #444;">Sign in with Google</span>
                    </a>
                </div>

                <p class="mt-3 text-muted">Don't have an account? <a href="register.php" class="text-primary">Register</a></p>
                <?php if (isset($error_message)): ?>
                    <p class="mt-2 text-danger"><?php echo $error_message; ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
