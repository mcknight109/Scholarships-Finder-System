<?php
include '../config.php';

session_start();

$user_id = $_SESSION['user_id'] ?? null;
$profile_image = '../uploads/default.png'; // fallback default

if ($user_id) {
    $stmt = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($picture);
    if ($stmt->fetch() && !empty($picture)) {
        $profile_image = '../uploads/' . $picture;
    }
    $stmt->close();
}

if (isset($_GET['id']) && isset($_GET['update'])) {
    $application_id = $_GET['id'];

    // Update the application status to 'reviewed' if it's still 'pending'
    $updateQuery = "UPDATE applications SET status = 'reviewed' WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();
}

$filter = isset($_GET['status']) ? $_GET['status'] : 'all';

$query = "
    SELECT 
        applications.id AS application_id,
        users.name AS student_name,
        scholarships.title AS scholarship_title,
        applications.status AS application_status
    FROM applications
    INNER JOIN users ON applications.user_id = users.id
    INNER JOIN scholarships ON applications.scholarship_id = scholarships.id
";

if ($filter !== 'all') {
    $query .= " WHERE applications.status = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">  
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">    
    <link rel="stylesheet" href="../assets/sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="css/style.scss">
    <link rel="stylesheet" href="../alert.scss">
</head>
<body>
    
    <div class="wrapper">
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
                            <a href="#" class="nav-link">
                                <i class="nav-icon fas fa-light fa-bell"></i>
                                <p onclick="alert('Notifications function will be implemented soon.')" style="text-decoration: line-through;">Notifications</p>
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

        <!-- Navbar -->
        <nav class="main-header navbar-expand ">
            <ul class="navbar-nav">
                <!-- Sidebar Toggle Button -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
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
                    <img src="<?= htmlspecialchars($profile_image) ?>" alt="profile image">
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
                <p class="fs-4 fw-semibold">All Applicants</p>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Applications</li>
                </ol>
            </div>
            <div class="content">
                <div class="scholar-head">
                    <form class="form-inline">
                        <div class="input-group input-group-sm">
                        <input id="searchApplicant" class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar border " type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php $currentStatus = isset($_GET['status']) ? $_GET['status'] : 'all'; ?>
                    <div class="sideHeader" id="toggle-buttons">
                        <a href="applicants.php?status=all">
                            <button class="btn <?= $currentStatus === 'all' ? 'active' : '' ?>">All</button>
                        </a>
                        <a href="applicants.php?status=pending">
                            <button class="btn <?= $currentStatus === 'pending' ? 'active' : '' ?>">Pending</button>
                        </a>
                        <a href="applicants.php?status=approved">
                            <button class="btn <?= $currentStatus === 'approved' ? 'active' : '' ?>">Approved</button>
                        </a>
                        <a href="applicants.php?status=rejected">
                            <button class="btn <?= $currentStatus === 'rejected' ? 'active' : '' ?>">Rejected</button>
                        </a>
                    </div>
                </div>
                <div id="applicantsList" class="app-container row">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card applicant-card shadow-sm h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-user-graduate text-primary me-2"></i>&nbsp;<?= htmlspecialchars($row['student_name']) ?>
                                    </h5>
                                        <p class="mb-2"><strong>Scholarship:</strong> <?= htmlspecialchars($row['scholarship_title']) ?></p>
                                        <p class="mb-0"><strong>Status:</strong> 
                                        <span class="badge bg-<?php 
                                            echo ($row['application_status'] === 'approved') ? 'success' : 
                                                (($row['application_status'] === 'pending') ? 'warning' : 'danger'); 
                                        ?>">
                                            <?= ucfirst(htmlspecialchars($row['application_status'])) ?>
                                        </span>
                                    </p>
                                    <!-- Action Buttons -->
                                    <div class="mt-auto d-flex justify-content-between">
                                    <a href="view-application.php?id=<?= $row['application_id'] ?>&update=1" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?= $row['application_id'] ?>)">
                                        <i class="fas fa-trash-alt me-2"></i>Delete
                                    </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                        <div class="alert alert-info">No applicants found.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('searchApplicant').addEventListener('input', function() {
            let input = this.value.toLowerCase();
            let applicants = document.querySelectorAll('#applicantsList .applicant-card');

            applicants.forEach(function(card) {
                let name = card.querySelector('.card-title').textContent.toLowerCase();
                let scholarship = card.querySelector('p strong').nextSibling.textContent.toLowerCase();

                if (name.includes(input) || scholarship.includes(input)) {
                    card.parentElement.style.display = 'block';
                } else {
                    card.parentElement.style.display = 'none';
                }
            });
        });
    </script>

    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This application will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '',
                cancelButtonColor: '#6c757d',
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
                    window.location.href = 'controls/del-application.php?id=' + id;
                }
            });
        }
    </script>

    <script>
        document.getElementById("toggle-buttons").addEventListener('click', function(e){
            if (e.target.classList.contains('btn')){
            const buttons = document.querySelectorAll('#toggle-buttons, .btn');
            buttons.forEach(btn => btn.classList.remove('active'))
            e.target.classList.add('active');
            }
        });
    </script>
    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->    
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script>
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>    
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
