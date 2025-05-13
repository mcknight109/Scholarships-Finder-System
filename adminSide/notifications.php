<?php
session_start();
require '../config.php';

$user_id = $_SESSION['user_id'] ?? null;
$picture = 'default.png'; // fallback image

// Fetch admin profile picture
if ($user_id) {
    $stmt = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $picture = $row['picture'] ?: 'default.png';
    }
}

// Fetch new user applications (status = 'pending')
$notifications = [];
$stmt = $conn->prepare("
    SELECT a.applied_at, s.title AS scholarship_title, u.name AS user_name, u.picture AS user_pic
    FROM applications a
    JOIN users u ON a.user_id = u.id
    JOIN scholarships s ON a.scholarship_id = s.id
    WHERE a.status = 'pending'
    ORDER BY a.applied_at DESC
");
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
  <link rel="stylesheet" href="../alert.scss">
  <title>Notifications</title>
</head>
<body>
<div class="wrapper">
    <!-- Sidebar -->
    <div class="main-sidebar sidebar-dark-primary elevation-3">
        <div class="nav-logo" href="admin-dashboard.php">
            <i class="fas fa-user-shield mr-1 text-white"></i>
            <span>WELCOME ADMIN</span>
        </div>
        <div class="sidebar">
            <!-- Sidebar -->
            <aside class="main-sidebar sidebar-dark-primary elevation-3">
                <div class="nav-logo" href="admin-dashboard.php">
                    <i class="fas fa-user-shield mr-1 text-white"></i>
                    <span>WELCOME ADMIN</span>
                </div>
                
                <div class="sidebar">
                    <nav class="mt-2">
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a href="admin-dashboard.php" class="nav-link active">
                                    <i class="nav-icon fas fa-home"></i>
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="manage-users.php" class="nav-link">
                                    <i class="nav-icon fas fa-users"></i>
                                    <p>Manage Users</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="scholarships.php" class="nav-link">
                                    <i class="nav-icon fas fa-list"></i>
                                    <p>Scholarships</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="applicants.php" class="nav-link">
                                    <i class="nav-icon fas fa-clipboard-list"></i>
                                    <p>Applicants</p>
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
            </aside>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar-expand">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <div class="nav-side">
            <div class="noti">
                <a class="nav-link" href="#"><i class="far fa-bell"></i></a>
            </div>
            <div class="prof-img">
                <a href="profile.php">
                    <img src="../uploads/<?php echo htmlspecialchars($picture); ?>" alt="profile image">
                </a>
            </div>
            <div class="logout-btn">
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <p>New User Applications</p>
        </div>
        <div class="content">
            <div class="scholar-head justify-content-end">
                <form class="form-inline">
                    <div class="input-group input-group-sm">
                        <input id="searchInput" class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-navbar border" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <div class="alert alert-info alert-dismissible fade show">
                            <div class="d-flex align-items-center">
                                <img src="../uploads/<?php echo htmlspecialchars($notif['user_pic'] ?: 'default.png'); ?>" class="img-circle elevation-2 mr-3" alt="User Image" width="40" height="40">
                                <div>
                                    <strong><?php echo htmlspecialchars($notif['user_name']); ?></strong> applied for 
                                    <strong><?php echo htmlspecialchars($notif['scholarship_title']); ?></strong> on 
                                    <?php echo date("F j, Y g:i A", strtotime($notif['applied_at'])); ?>.
                                </div>
                            </div>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No new applications at the moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<!-- Scripts -->
<script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
<script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script>
<script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
<script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
