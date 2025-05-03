<?php
include '../config.php';

include '../config.php';

$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

if ($statusFilter === 'open' || $statusFilter === 'closed') {
  $query = "SELECT * FROM scholarships WHERE status = '$statusFilter' ORDER BY deadline DESC";
} else {
  $query = "SELECT * FROM scholarships ORDER BY deadline DESC";
}

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
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
    </aside>

    <!-- Navbar -->
    <nav class="main-header navbar-expand">
      <ul class="navbar-nav">
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
            <img src="../uploads/scholar3.jpg" alt="profile image">
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
        <p class="fs-4 fw-semibold">All Scholarships</p>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="admin-dashboard.php">Home</a></li>
          <li class="breadcrumb-item active">Scholarships</li>
        </ol>
      </div>

      <div class="content">
        <div class="scholar-head">
          <form class="form-inline">
              <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" id="searchScholarship" placeholder="Search" aria-label="Search" onkeyup="searchScholarships()">
                <div class="input-group-append">
                    <button class="btn btn-navbar border " type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
          </form>
          <?php
            $currentStatus = isset($_GET['status']) ? $_GET['status'] : 'all';
          ?>
          <div class="sideHeader" id="toggle-buttons">
            <a href="scholarships.php?status=all">
              <button class="btn <?= $currentStatus === 'all' ? 'active' : '' ?>">All</button>
            </a>
            <a href="scholarships.php?status=open">
              <button class="btn <?= $currentStatus === 'open' ? 'active' : '' ?>">Open</button>
            </a>
            <a href="scholarships.php?status=closed">
              <button class="btn <?= $currentStatus === 'closed' ? 'active' : '' ?>">Closed</button>
            </a>
            <a href="create-scholar.php">
              <button class="btn">Create Scholarship</button>
            </a>
          </div>
        </div>
        <div id="scholarshipList">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <div class="card mb-4 shadow-sm">
                <div class="row g-0 h-100">
                  <!-- Image on the left -->
                  <div class="col-md-4 h-100">
                    <?php if (!empty($row['images'])): ?>
                      <img src="../uploads/<?php echo htmlspecialchars($row['images']); ?>" class="img-fluid" alt="Scholarship Image">
                    <?php else: ?>
                      <div class="no-image">
                        <p class="m-0">No Image</p>
                      </div>
                    <?php endif; ?>
                  </div>

                  <!-- Text on the right -->
                  <div class="col-md-8 h-100">
                    <div class="card-body">
                      <div class="title">
                        <h4><?php echo htmlspecialchars($row['title']); ?></h4>
                      </div>
                      <div class="eligibility">
                        <p><strong>Eligibility:</strong> <?php echo nl2br(htmlspecialchars($row['eligibility'])); ?></p>
                      </div>
                      <div class="deadline">
                        <p><strong>Deadline:</strong> <?php echo date('F, d, Y', strtotime($row['deadline'])); ?></p>
                      </div>
                      <div class="status">
                        <p><strong>Status:</strong> <?php echo ucfirst($row['status']); ?></p>
                      </div>
                      <div>
                        <a href="edit-scholar.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <button type="button" class="btn btn-outline-light btn-sm" onclick="deleteScholar(<?= $row['id'] ?>)">
                          <i class="fas fa-trash-alt"></i> Delete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="alert alert-info">No scholarships found.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function searchScholarships() {
      var input = document.getElementById('searchScholarship');
      var filter = input.value.toLowerCase();
      var cards = document.querySelectorAll('#scholarshipList .card');

      cards.forEach(function(card) {
        var title = card.querySelector('.title h4').textContent.toLowerCase();
        var eligibility = card.querySelector('.eligibility p').textContent.toLowerCase();

        if (title.includes(filter) || eligibility.includes(filter)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
    }
  </script>

  <script>
    function deleteScholar(id) {
      Swal.fire({
        title: 'Are you sure?',
        text: "This scholarship will be deleted!",
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
          // Redirect to PHP delete handler
          window.location.href = 'controls/del-scholar.php?id=' + id;
        }
      });
    }
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
