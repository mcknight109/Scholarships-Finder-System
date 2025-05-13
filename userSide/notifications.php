<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$picture = 'default.png'; // fallback image

// Fetch user profile picture
if ($user_id) {
    $stmt = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $picture = $row['picture'] ?: 'default.png';
    }
}

// Fetch approved applications
$notifications = [];
if ($user_id) {
    $stmt = $conn->prepare("
        SELECT s.title, a.applied_at
        FROM applications a
        JOIN scholarships s ON a.scholarship_id = s.id
        WHERE a.user_id = ? AND a.status = 'approved'
        ORDER BY a.applied_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
// Count approved applications for notification
$notif_stmt = $conn->prepare("SELECT COUNT(*) AS notif_count FROM applications WHERE user_id = ? AND status = 'approved'");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notif_count = $notif_row['notif_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="css/temp.scss">
  <link rel="stylesheet" href="../alert.scss">
  <title>Notifications</title>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="main-sidebar sidebar-dark-primary elevation-3">
            <div class="nav-logo" href="admin-dashboard.php">
                <i class="fas fa-user-graduate mr-1 text-white"></i>
                <span>WELCOME STUDENT</span>
            </div>
        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a href="home-page.php" class="nav-link active">
                            <i class="nav-icon fas fa-home"></i>
                            <p>Home</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Profile</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="applications.php" class="nav-link">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Applications</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="notifications.php" class="nav-link">
                            <i class="nav-icon fas fa-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <p onclick="alert('Settings function will be implemented soon.')" style="text-decoration: line-through;">Settings</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar-expand">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <div class="nav-side">
            <div class="noti dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <?php if ($notif_count > 0): ?>
                    <span class="badge badge-danger navbar-badge"><?php echo $notif_count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <?php if ($notif_count > 0): ?>
                    <a href="notifications.php" class="dropdown-item">
                        <i class="fas fa-check-circle mr-2 text-success"></i> You have <?php echo $notif_count; ?> approved application<?php echo $notif_count > 1 ? 's' : ''; ?>
                    </a>
                    <?php else: ?>
                    <span class="dropdown-item text-muted">No new notifications</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="prof-img">
                <a href="profile.php">
                    <img src="../uploads/<?php echo htmlspecialchars($picture); ?>" alt="profile image">
                </a>
            </div>

            <div class="logout-btn">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </nav>
    

        <div class="content-wrapper">
            <div class="content-header">
                <p>Application Notifications</p>
            </div>
            <div class="notiContent p-3">
                <div class="container-fluid">
                    <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                        <div class="d-flex align-items-center">
                            <img src="../uploads/<?php echo htmlspecialchars($picture); ?>" class="img-circle elevation-2 mr-3" alt="User Image" width="40" height="40">
                            <div>
                            <strong>Application Approved!</strong><br>
                            Your application for <strong><?php echo htmlspecialchars($notif['title']); ?></strong> was approved on 
                            <?php echo date("F j, Y", strtotime($notif['applied_at'])); ?>.
                            </div>
                        </div>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No new notifications yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->    
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>    
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>