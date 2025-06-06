<?php
session_start();
include '../config.php';

// Fetch the logged-in user info
$user = null;
$profileImage = 'default.png'; // fallback

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT name, picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Set profile image if available
    if ($user && $user['picture']) {
        $profileImage = $user['picture'];
    }
}
// Initialize message
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title       = $_POST['title'];
    $description = $_POST['description'];
    $eligibility = $_POST['eligibility'];
    $deadline    = $_POST['deadline'];
    $status      = $_POST['status'];
    $imageName   = "";

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName    = basename($_FILES['image']['name']);
        $uploadPath   = '../uploads/' . $imageName;

        if (!move_uploaded_file($imageTmpPath, $uploadPath)) {
            $message = "Error uploading image.";
        }
    }

    $query = "INSERT INTO scholarships (title, description, eligibility, deadline, images, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("ssssss", $title, $description, $eligibility, $deadline, $imageName, $status);

    if ($stmt->execute()) {
      $success = true;
      $message = "Scholarship created successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Scholarship</title>
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js">
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="css/form.scss">
  <link rel="stylesheet" href="../alert.scss">
</head>
<body>
  <header>
    <div class="logo">
      <img src="../images/realogo.png" alt="Logo Picture">
    </div>
    <div class="nav-side">
      <div class="prof-img">
        <a href="stud-profile.php">
          <img src="../uploads/<?= htmlspecialchars($profileImage) ?>" alt="profile image">
        </a>
      </div>
      <div class="logout-btn">
        <a href="../logout.php">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </header>

    <div class="createWrapper">
      <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2>Create a New Scholarship</h2>
          <a href="scholarships.php" class="btn btn-back">
            <i class="fas fa-arrow-left"></i>Go Back
          </a>
        </div>
      <div class="card shadow">
        <div class="card-body">
          <p class="text-muted mb-4">Complete the form below to add a new scholarship opportunity.</p>
          <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="title" class="form-label">Scholarship Title:</label>
              <input type="text" class="form-control" id="title" name="title" placeholder="Enter scholarship title" required>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description:</label>
              <textarea class="form-control" id="description" name="description" rows="4" placeholder="Provide details about the scholarship" required></textarea>
            </div>

            <div class="mb-3">
              <label for="eligibility" class="form-label">Eligibility:</label>
              <textarea class="form-control" id="eligibility" name="eligibility" rows="3" placeholder="List all eligibility criteria" required></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="deadline" class="form-label">Deadline:</label>
                <input type="date" class="form-control" id="deadline" name="deadline" required>
              </div>

              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status:</label>
                <select class="form-select" id="status" name="status">
                  <option value="open" selected>Open</option>
                  <option value="closed">Closed</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="image" class="form-label">Scholarship Image: (optional)</label>
              <input class="form-control" type="file" id="image" name="image" accept="image/*">
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Scholarship
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap CDN -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
    const success = <?= $success ? 'true' : 'false' ?>;
    const message = <?= json_encode($message) ?>;

    if (message) {
        Swal.fire({
            icon: success ? 'success' : 'error',
            title: success ? 'Success!' : 'Error!',
            text: message,
            confirmButtonColor: '',
            confirmButtonText: 'OK',
            width: 330,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                htmlContainer: 'custom-swal-text',
                icon: 'custom-swal-icon',
                confirmButton: 'custom-swal-btn',
                cancelButton: 'custom-swal-cancel'
            }
        }).then(() => {
            if (success) {
                window.location.href = 'scholarships.php';
            }
        });
    }
  </script>

</body>
</html>
