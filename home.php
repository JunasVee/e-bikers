<?php
// Start the session
session_start();

// Database credentials
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  // If not logged in, redirect to login.php
  header("Location: login.php");
  exit();
}

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
$user_logged_in = false;
$user_name = "";
$profile_picture = "uploads/default.png"; // Default picture in case user has not uploaded one

if (isset($_SESSION['user_id'])) {
  $user_logged_in = true;

  // Fetch the user's name and profile picture
  $user_id = $_SESSION['user_id'];
  $sql = "SELECT username, profile_picture FROM account WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($user_name, $profile_picture);
  $stmt->fetch();
  $stmt->close();
}

// Logout handler
if (isset($_GET['logout'])) {
  session_unset();
  session_destroy();
  header("Location: home.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>E-Bikers</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: "Poppins", sans-serif;
    }

    .bg-lightblue {
      background-color: #4da8da;
    }

    .hero {
      background: url("https://plus.unsplash.com/premium_photo-1694558642770-6857b87e82a2?q=80&w=1471&auto=format&fit=crop") center/cover;
      color: white;
      text-align: center;
      padding: 100px 20px;
    }

    .feature-icon {
      font-size: 3rem;
      color: #007bff;
    }

    .profile-pic {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-lightblue px-5">
    <div class="container">
      <a class="navbar-brand text-white fw-bold" href="#">E-Bikers</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mx-auto">
          <li class="nav-item mx-3"><a class="nav-link text-white fw-semibold" href="/e-bikers/">Home</a></li>
          <li class="nav-item mx-3"><a class="nav-link text-white fw-semibold" href="#why-us">Why Us?</a></li>
          <li class="nav-item mx-3"><a class="nav-link text-white fw-semibold" href="#how">How?</a></li>
          <li class="nav-item mx-3"><a class="nav-link text-white fw-semibold" href="#locations">Locations</a></li>
        </ul>
      </div>
      <div class="d-flex">
        <?php if ($user_logged_in): ?>
          <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
          <!-- Display the profile picture from the database -->
          <a href="signedin.php"><img src="<?php echo $profile_picture; ?>" alt="Profile Picture" class="profile-pic me-3"></a>
          <a href="home.php?logout=true"><button class="btn btn-outline-light me-3">Logout</button></a>
        <?php else: ?>
          <a href="login.php"><button class="btn btn-outline-light me-3">Login</button></a>
        <?php endif; ?>
        <a href="contact.php"><button class="btn btn-light text-primary">Contact</button></a>
      </div>
    </div>
  </nav>

  <section class="hero">
    <div class="container">
      <h1 class="fw-bold">Ride Green, Ride Smart, Ride E-Bikers</h1>
      <p class="lead">Affordable & secure electric bike rentals near universities in Surabaya.</p>
      <a href="order.php" class="btn btn-light btn-lg mt-3">Start Riding</a>
    </div>
  </section>

  <div class="container my-5" id="why-us">
    <h2 class="text-center fw-bold text-primary mb-4">Why Choose E-Bikers?</h2>
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">🚲 Eco-Friendly Transport</h5>
            <p class="card-text">Reduce carbon emissions while enjoying a convenient ride around the city.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">🔐 High Security System</h5>
            <p class="card-text">QR-based locking and tracking ensure a safe rental experience.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">📍 Convenient Locations</h5>
            <p class="card-text">Strategically placed near universities for easy access.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">💰 Affordable for Students</h5>
            <p class="card-text">Low-cost rentals designed to fit a student’s budget.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container my-5" id="how">
    <h2 class="text-center fw-bold text-primary mb-4">How It Works</h2>
    <div class="row row-cols-1 row-cols-md-2 g-4">
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">📲 Scan QR Code</h5>
            <p class="card-text">Use your phone to scan the QR code on the bike to unlock it.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">🚴‍♂️ Ride Anywhere</h5>
            <p class="card-text">Enjoy your ride and reach your destination with ease.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">📍 Park at a Station</h5>
            <p class="card-text">Park at designated stations to keep the system organized.</p>
          </div>
        </div>
      </div>
      <div class="col">
        <div class="card border-0 shadow">
          <div class="card-body">
            <h5 class="card-title text-primary fw-bold">🔒 Lock & End Rental</h5>
            <p class="card-text">Lock the bike via the app to complete your rental session.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="container my-6" id="locations">
    <h2 class="text-center fw-bold text-primary mb-5">Initial Stations</h2>
    <div class="row row-cols-1 row-cols-md-2 g-5 justify-content-center">
      <div class="col text-center">
        <div class="card border-0">
          <img src="https://www.its.ac.id/wp-content/uploads/2020/07/Logo-ITS-1.png" alt="ITS Surabaya Logo"
            class="mx-auto" style="width: 150px;" />
          <div class="card-body">
            <a href="https://www.google.com/maps/place/Taman+Air+Mancur+Menari+ITS/" target="_blank">
              <h5 class="card-title fw-bold">ITS Surabaya</h5>
            </a>
          </div>
        </div>
      </div>
      <div class="col text-center">
        <div class="card border-0">
          <img src="https://upload.wikimedia.org/wikipedia/commons/6/65/Logo-Branding-UNAIR-biru.png" alt="UNAIR Logo"
            class="mx-auto" style="width: 95px;" />
          <div class="card-body">
            <a href="https://www.google.com/maps/place/Universitas+Airlangga+-+Campus+B/" target="_blank">
              <h5 class="card-title fw-bold">Airlangga University</h5>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3">
    <p>© 2025 E-Bikers. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", function (e) {
          e.preventDefault();
          const targetId = this.getAttribute("href").substring(1);
          const targetElement = document.getElementById(targetId);
          if (targetElement) {
            smoothScrollTo(targetElement.offsetTop, 800);
          }
        });
      });

      function smoothScrollTo(targetPosition, duration) {
        const startPosition = window.scrollY;
        const distance = targetPosition - startPosition;
        let startTime = null;

        function animation(currentTime) {
          if (startTime === null) startTime = currentTime;
          const elapsedTime = currentTime - startTime;
          const easeInOut = easeInOutCubic(elapsedTime, startPosition, distance, duration);
          window.scrollTo(0, easeInOut);
          if (elapsedTime < duration) requestAnimationFrame(animation);
        }

        function easeInOutCubic(t, b, c, d) {
          t /= d / 2;
          if (t < 1) return c / 2 * t * t * t + b;
          t -= 2;
          return c / 2 * (t * t * t + 2) + b;
        }

        requestAnimationFrame(animation);
      }
    });
  </script>
</body>

</html>