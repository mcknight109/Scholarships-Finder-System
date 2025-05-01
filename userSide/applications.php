<?php
session_start();
include '../config.php';

// Get user ID from session
$studentId = $_SESSION['user_id'];

// Get scholarship applications
$query = "SELECT a.*, s.title 
          FROM applications a 
          JOIN scholarships s ON a.scholarship_id = s.id 
          WHERE a.user_id = ?
          ORDER BY a.applied_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $studentId);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/plugins/bootstrap/bootstrap.min.js">
    <link rel="stylesheet" href="../AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.scss">
    <link rel="stylesheet" href="../alert.scss">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="main-sidebar sidebar-dark-primary elevation-0">
            <div class="nav-logo" href="admin-dashboard.php">
                <span>STUDENT</span>
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
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-bell"></i>
                                <p onclick="alert('Settings function will be implemented soon.')">Notifications</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-cog"></i>
                                <p onclick="alert('Settings function will be implemented soon.')">Settings</p>
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
                <div class="noti">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                    </a>
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
                    <div class="sideHeader">
                        <a href="#">
                            <button>All</button>
                        </a>
                        <a href="#  ">
                            <button>Pending</button>
                        </a>
                        <a href="#">
                            <button>Approved</button>
                        </a>
                        <a href="#">
                            <button>Rejected</button>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
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
                                        <a href="controls/view-application.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm mt-2 me-2">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <!-- Delete Button with Alert -->
                                        <button class="btn btn-danger btn-sm mt-2 delete-btn"
                                            data-id="<?php echo $row['id']; ?>">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </button>
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

    <!-- SweetAlert2 JS -->
    <script src="../sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->
    <script src="../AdminLTE/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../AdminLTE/dist/js/adminlte.min.js"></script>

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
</body>
</html>
