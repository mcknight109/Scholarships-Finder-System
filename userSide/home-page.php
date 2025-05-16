<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get logged-in student details
$user_stmt = $conn->prepare("SELECT name, picture FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$profilePic = !empty($user['picture']) ? $user['picture'] : 'default.png';

// Fetch applied scholarships for this user
$applied_ids = [];
$app_stmt = $conn->prepare("SELECT scholarship_id FROM applications WHERE user_id = ?");
$app_stmt->bind_param("i", $user_id);
$app_stmt->execute();
$app_result = $app_stmt->get_result();
while ($app_row = $app_result->fetch_assoc()) {
    $applied_ids[] = $app_row['scholarship_id'];
}

// Fetch scholarships
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

$query = "SELECT * FROM scholarships WHERE 1";

if (!empty($search)) {
  $search = $conn->real_escape_string($search);
  $query .= " AND title LIKE '%$search%'";
}

if (!empty($filter) && in_array($filter, ['open', 'closed'])) {
  $filter = $conn->real_escape_string($filter);
  $query .= " AND status = '$filter'";
}

$query .= " ORDER BY deadline DESC";
$result = $conn->query($query);

// Fetch applied scholarships count for this user
$app_count_stmt = $conn->prepare("SELECT COUNT(*) AS applied_count FROM applications WHERE user_id = ?");
$app_count_stmt->bind_param("i", $user_id);
$app_count_stmt->execute();
$app_count_result = $app_count_stmt->get_result();
$app_count_row = $app_count_result->fetch_assoc();
$applied_count = $app_count_row['applied_count'];

// Count all relevant notifications (approved, rejected, or pending/reviewed older than 10 days)
$notif_stmt = $conn->prepare("
    SELECT COUNT(*) AS notif_count 
    FROM applications 
    WHERE user_id = ? 
    AND (
        status = 'approved' 
        OR status = 'rejected' 
        OR (status IN ('pending', 'reviewed') AND applied_at <= NOW() - INTERVAL 10 DAY)
    )
");
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
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
  <link rel="stylesheet" href="../assets/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="../alert.scss">
  <title>Student Dashboard</title>  
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
            <img src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="profile image">
          </a>
        </div>
        <div class="logout-btn">
          <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
      </div>
    </nav>
    

    <div class="content-wrapper d-flex flex-wrap">
      <div class="content">
        <div class="searchCon">
        <form class="form-inline" method="GET" action="home-page.php">
          <div class="input-group input-group-sm">
            <input id="searchApplicant" name="search" class="form-control form-control-navbar" type="search" placeholder="Search by title..." aria-label="Search" value="<?php echo htmlspecialchars($search); ?>">
            <div class="input-group-append">
              <button class="btn btn-navbar border" type="submit">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </form>
          <div class="select" id="toggle-buttons">
            <form method="GET" action="home-page.php" style="display:inline;">
              <input type="hidden" name="filter" value="">
              <button type="submit" class="btn <?php echo empty($_GET['filter']) ? 'active' : ''; ?>">All</button>
            </form>
            <form method="GET" action="home-page.php" style="display:inline;">
              <input type="hidden" name="filter" value="open">
              <button type="submit" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'open') ? 'active' : ''; ?>">Open</button>
            </form>
            <form method="GET" action="home-page.php" style="display:inline;">
              <input type="hidden" name="filter" value="closed">
              <button type="submit" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] === 'closed') ? 'active' : ''; ?>">Closed</button>
            </form>
          </div>
        </div>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <div id="scholarship-list" class="div-wrapper">
            <div class="picture">
              <?php if (!empty($row['images'])): ?>
                <a href="view-image.php?img=<?php echo urlencode($row['images']); ?>">
                  <img src="../uploads/<?php echo htmlspecialchars($row['images']); ?>" alt="Scholarship Image">
                </a>
              <?php else: ?>
                <p>No image available</p>
              <?php endif; ?>
            </div>
              <h3><?php echo htmlspecialchars($row['title']); ?></h3>
              <div class="descriptions">
              <span>DESCRIPIONS:</span><br> 
                <p><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
              </div>
              <div class="eligibility">
                <span>ELIGIBILITY:</span><br> 
                <p><?php echo nl2br(htmlspecialchars($row['eligibility'])); ?></p>
              </div>
              <div class="deadline">
                <span>DEADLINE:</span><br> 
                <p><?php echo date("F j, Y", strtotime($row['deadline'])); ?></p>
              </div>
              <div class="status">
                <span>STATUS:</span><br> 
                <p
                  <?php 
                    if ($row['status'] === "open") {
                      echo "style='color: green'";
                    } else {
                      echo "style='color: gray'";
                    }
                  ?>
                >
                  <?php echo ucfirst($row['status']); ?>
                </?>
              </div>
              <div class="apply-btn">
              <?php if (in_array($row['id'], $applied_ids)): ?>
                <button disabled style="background-color: gray; cursor: not-allowed;">Applied</button>
              <?php elseif ($row['status'] === 'closed'): ?>
                <button disabled style="background-color: gray; cursor: not-allowed;">Closed</button>
              <?php else: ?>
                <button onclick="confirmApply(<?php echo $row['id']; ?>)">Apply Now</button>
              <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No scholarships available at the moment.</p>
        <?php endif; ?>
      </div>

      <aside class="side-pf">
        <div class="pfp">
          <img src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="profile image">
        </div>
        <div class="pfp-info">
          <p><?php echo htmlspecialchars($user['name']); ?></p>

          <p><span>Applied:</span> <?php echo $applied_count; ?></p>

          <div class="activeStatus">
            <?php if (isset($_SESSION['user_id'])): ?>
              <div class="on"></div>
                <p>Online</p>
            <?php else: ?>
              <div class="off"></div>
                <p>Offline</p>
            <?php endif; ?>
          </div>
        </div>
      </aside>
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
    function confirmApply(scholarId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "Do you really want to apply for this scholarship?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, apply now!',
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
          window.location.href = 'apply-scholar.php?scholar_id=' + scholarId;
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
</body>
</html>
