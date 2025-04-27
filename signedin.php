<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, email, profile_picture FROM account WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $profile_picture);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Bikers - Rent a Bike</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script defer src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #f8f9fa; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh;
        }
        .container-box { 
            background: white; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
            width: 100%; 
            max-width: 500px; 
        }
        .btn-primary { 
            background-color: #4DA8DA; 
            border: none; 
        }
        .btn-primary:hover { 
            background-color: #3C94C2; 
        }
        .profile-img { 
            width: 100px; 
            height: 100px; 
            border-radius: 50%; 
            object-fit: cover; 
        }
        .btn-custom { 
            background-color: #007bff; 
            color: white; 
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="container-box">
            <div class="text-center mb-4">
                <!-- Display profile picture -->
                <?php if ($profile_picture): ?>
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" class="profile-img mb-3">
                <?php else: ?>
                    <img src="uploads/default.jpg" alt="Profile Picture" class="profile-img mb-3">
                <?php endif; ?>
                
                <!-- Display username -->
                <h4 class="fw-bold"><?php echo htmlspecialchars($username); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($email); ?></p>
            </div>

            <!-- Change Profile Picture Form -->
            <form action="upload.php" method="post" enctype="multipart/form-data" class="mb-4">
                <div class="mb-3">
                    <label for="profile_picture" class="form-label">Change Profile Picture</label>
                    <input type="file" class="form-control" id="profile_picture" name="profile_picture">
                </div>
                <button type="submit" class="btn btn-custom w-100">Upload</button>
            </form>

            <!-- Change Password Form -->
            <form action="change_password.php" method="post">
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
