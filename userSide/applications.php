<?php
session_start();
include '../config.php';

// Get user ID from session
$studentId = $_SESSION['user_id'];

$status = $_GET['status'] ?? 'all';

if ($status === 'all') {
    $query = "SELECT a.*, s.title 
              FROM applications a 
              JOIN scholarships s ON a.scholarship_id = s.id 
              WHERE a.user_id = ?
              ORDER BY a.applied_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $studentId);
} else {
    $query = "SELECT a.*, s.title 
              FROM applications a 
              JOIN scholarships s ON a.scholarship_id = s.id 
              WHERE a.user_id = ? AND a.status = ?
              ORDER BY a.applied_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $studentId, $status);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch profile picture
$picQuery = "SELECT picture FROM users WHERE id = ?";
$picStmt = $conn->prepare($picQuery);
$picStmt->bind_param("i", $studentId);
$picStmt->execute();
$picResult = $picStmt->get_result();

if ($picRow = $picResult->fetch_assoc()) {
    $picture = !empty($picRow['picture']) ? '../uploads/' . $picRow['picture'] : '../uploads/default.png';
} else {
    $picture = '../uploads/default.png';
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.scss">
    <link rel="stylesheet" href="../alert.scss">
    <title>Admin Dashboard</title>
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
        <nav class="main-header navbar-expand ">
            <ul class="navbar-nav">
                <!-- Sidebar Toggle Button -->
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
                    <a href="stud-profile.php">
                        <img src="<?php echo $picture; ?>" alt="profile image">
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
                <p class="fs-4 fw-semibold">Applied Scholarships</p>
            </div>
            <div class="container">
                <div class="scholar-head">
                    <form class="form-inline">
                        <div class="input-group input-group-sm">
                        <input id="searchInput" class="form-control form-control-navbar" type="search" placeholder="Search applications..." aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar border " type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php
                        $filter = $_GET['status'] ?? 'all';
                        function isActive($status, $current) {
                            return ($status === $current) ? 'active' : '';
                        }
                    ?>

                    <div class="sideHeader" id="toggle-buttons">
                        <a href="applications.php?status=all"><button class="btn <?= isActive($filter, 'all') ?>">All</button></a>
                        <a href="applications.php?status=pending"><button class="btn <?= isActive($filter, 'pending') ?>">Pending</button></a>
                        <a href="applications.php?status=approved"><button class="btn <?= isActive($filter, 'approved') ?>">Approved</button></a>
                        <a href="applications.php?status=rejected"><button class="btn <?= isActive($filter, 'rejected') ?>">Rejected</button></a>
                    </div>
                </div>
                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card application-card shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title text-primary">
                                            <i class="fas fa-award me-2"></i>&nbsp;<?php echo htmlspecialchars($row['title']); ?>
                                        </h5><br>
                                        <p class="mt-3"><strong>Status:</strong>
                                            <span class="badge bg-<?php
                                                echo ($row['status'] === 'approved') ? 'success' :
                                                    (($row['status'] === 'pending') ? 'warning' : 'danger');
                                            ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </p>

                                        <p class="text-muted"><strong>Applied At:</strong>&nbsp; 
                                            <?php echo date('F d, Y', strtotime($row['applied_at'])); ?>
                                        </p>

                                        <!-- View Button -->
                                        <a href="view-application.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm mt-2 me-2">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">No applications found.</div>
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
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->    
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>    
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.delete-btn').click(function () {
                var appId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This application will be permanently deleted!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    width: 330,
                    customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-text',
                    icon: 'custom-swal-icon',
                    confirmButton: 'custom-swal-btn',
                    cancelButton: 'custom-swal-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'controls/del-application.php?id=' + appId;
                    }
                });
            });
        });

         // ⭐ Search functionality for applications
         $('#searchInput').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('.application-card').filter(function() {
                $(this).parent().toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    </script>

    <script>
        document.getElementById('toggle-buttons').addEventListener('click', function(e){
            if (e.target.classList.contains('btn')){
                const buttons = document.querySelectorAll('#toggle-buttons, .btn');
                buttons.forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
            }
        });
    </script>
</body>
</html>
