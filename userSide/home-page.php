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

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard</title>
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
  <link rel="stylesheet" href="../assets/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="../alert.scss">
</head>
<body>
  <div class="wrapper">
    <!-- Sidebar -->
    <div class="main-sidebar sidebar-dark-primary elevation-0">
      <div class="nav-logo" href="home-page.php">
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
    <nav class="main-header navbar-expand">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#">
            <i class="fas fa-bars"></i>
          </a>
        </li>
        <!-- Right navbar -->
        <!-- <li class="nav-item d-flex align-items-center">
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
        </li> -->
      </ul>
      <div class="nav-side">
        <div class="noti">
          <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
          </a>
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
                <p><?php echo htmlspecialchars($row['deadline']); ?></p>
              </div>
              <div class="status">
                <span>STATUS:</span><br> 
                <p><?php echo ucfirst($row['status']); ?></p>
              </div>
              <div class="apply-btn">
                <button onclick="confirmApply(<?php echo $row['id']; ?>)">Apply Now</button>
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

          <p><span>Applied:</span> 4</p>

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

  <!-- SweetAlert2 JS -->
  <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
  <!-- AdminLTE Scripts -->
  <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
  <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
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
