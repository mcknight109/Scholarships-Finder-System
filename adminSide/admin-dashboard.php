<?php
require '../config.php';
date_default_timezone_set('Asia/Manila');

session_start();
require '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch the logged-in user's info
$userQuery = "SELECT name, picture FROM users WHERE id = ?";
$stmtUser = $conn->prepare($userQuery);
$stmtUser->bind_param("i", $userId);
$stmtUser->execute();
$userResult = $stmtUser->get_result();
$currentUser = $userResult->fetch_assoc();

$userName = $currentUser['name'] ?? 'Admin';
$userPicture = $currentUser['picture'] ?? 'default.png'; // fallback if no image uploaded

$yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
$query = "SELECT name, role, last_login FROM users WHERE last_login >= ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $yesterday);
$stmt->execute();
$result = $stmt->get_result();

// Fetch application count per scholarship per month
$barData = [];

// Fetch scholarship count per month
$scholarshipQuery = "
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month,
        COUNT(*) AS total_scholarships
    FROM scholarships
    GROUP BY month
    ORDER BY month ASC
";
$scholarshipResult = $conn->query($scholarshipQuery);

// Fetch application count per month
$applicationsQuery = "
    SELECT 
        DATE_FORMAT(applied_at, '%Y-%m') AS month,
        COUNT(*) AS total_applications
    FROM applications
    GROUP BY month
    ORDER BY month ASC
";
$applicationsResult = $conn->query($applicationsQuery);

// Initialize months
$months = [
    "January" => 0, "February" => 0, "March" => 0, "April" => 0,
    "May" => 0, "June" => 0, "July" => 0, "August" => 0,
    "September" => 0, "October" => 0, "November" => 0, "December" => 0
];

// Override with actual data for scholarships and applications
$scholarshipMonths = $months;
$applicationsMonths = $months;

while ($row = $scholarshipResult->fetch_assoc()) {
    $monthNum = $row['month']; // e.g. 2025-05
    $monthName = date("F", strtotime($monthNum));
    if (isset($scholarshipMonths[$monthName])) {
        $scholarshipMonths[$monthName] = (int)$row['total_scholarships'];
    }
}

while ($row = $applicationsResult->fetch_assoc()) {
    $monthNum = $row['month']; // e.g. 2025-05
    $monthName = date("F", strtotime($monthNum));
    if (isset($applicationsMonths[$monthName])) {
        $applicationsMonths[$monthName] = (int)$row['total_applications'];
    }
}

// Prepare the data for the chart
$labels = array_keys($months);
$scholarshipData = array_values($scholarshipMonths);
$applicationsData = array_values($applicationsMonths);

// Add to datasets
$datasets = [
    [
        'label' => 'Scholarships Created',
        'data' => $scholarshipData,
        'backgroundColor' => 'rgba(255, 99, 132, 0.7)'
    ],
    [
        'label' => 'Applications Applied',
        'data' => $applicationsData,
        'backgroundColor' => 'rgba(54, 162, 235, 0.7)'
    ]
];


// Count Active Scholarships
$activeScholarshipsQuery = "SELECT COUNT(*) as total FROM scholarships";
$activeScholarshipsResult = $conn->query($activeScholarshipsQuery);
$activeScholarships = $activeScholarshipsResult->fetch_assoc()['total'] ?? 0;

// Count Registered Users
$registeredUsersQuery = "SELECT COUNT(*) as total FROM users";
$registeredUsersResult = $conn->query($registeredUsersQuery);
$registeredUsers = $registeredUsersResult->fetch_assoc()['total'] ?? 0;

// Count Pending Applications
$pendingAppsQuery = "SELECT COUNT(*) as total FROM applications WHERE status = 'pending'";
$pendingAppsResult = $conn->query($pendingAppsQuery);
$pendingApplications = $pendingAppsResult->fetch_assoc()['total'] ?? 0;


function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;

    if ($diff < 60) {
        return $diff . " seconds ago";
    } elseif ($diff < 3600) {
        return floor($diff / 60) . " minutes ago";
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . " hours ago";
    } else {
        return "Yesterday at " . date("h:i A", $time);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/style.scss">
    <title>Admin Dashboard</title>    
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
                                <i class="nav-icon fas fa-light fa-users"></i>
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

        <!-- Navbar -->
        <nav class="main-header navbar-expand ">
            <ul class="navbar-nav">
                <!-- Sidebar Toggle Button -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <form class="form-inline" style="width: 300px;">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                                <div class="input-group-append">
                                <button class="btn btn-navbar border " type="submit">
                                <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </li>
            </ul>

            <div class="nav-side">
                <div class="noti">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                    </a>
                </div>

                <div class="prof-img">
                    <a href="stud-profile.php" title="<?= htmlspecialchars($userName) ?>">
                        <img src="../uploads/<?= htmlspecialchars($userPicture) ?>" alt="Profile Image">
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
                <p class="fs-4 fw-semibold">Admin Dashboard</p>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>

            <div class="dash-con">
                <div class="con-1">
                    <!-- Info Boxes -->
                    <div class="row">
                    <!-- Scholarships -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box shadow">
                            <span class="info-box-icon bg-info"><i class="fas fa-graduation-cap"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Scholarships</span>
                                <span class="info-box-number"><?= $activeScholarships ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Students -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box shadow">
                            <span class="info-box-icon bg-success"><i class="fas fa-user-graduate"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Registered Users</span>
                                <span class="info-box-number"><?= $registeredUsers ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Online -->
                    <div class="col-md-4 col-sm-6 col-12">
                        <div class="info-box shadow">
                            <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Applications</span>
                                <span class="info-box-number"><?= $pendingApplications ?></span>
                            </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 col-sm-12">
                        <div class="card card-info shadow">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Applications per Scholarship (Monthly 2025)</h3>
                            </div>
                            <div class="card-body">
                                <canvas id="applicationsBarChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="con-2">
                    <div class="card card-primary shadow">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-clock"></i> Users Logged In Today</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Logged In</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($row['name']) ?></td>
                                                <td><span class="badge badge-info"><?= ucfirst($row['role']) ?></span></td>
                                                <td><?= timeAgo($row['last_login']) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No one has logged in today yet.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const barCtx = document.getElementById('applicationsBarChart').getContext('2d');
        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: <?= json_encode($datasets) ?>
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Month'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Entries'
                        }
                    }
                }
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
