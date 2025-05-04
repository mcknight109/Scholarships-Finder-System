<?php
include '../config.php';
session_start();

$profileImage = 'default.png'; // fallback image

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (!empty($user['picture'])) {
            $profileImage = $user['picture'];
        }
    }
}

// Get the ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../scholarships.php?error=missing_id");
    exit();
}

$id = $_GET['id'];
$message = '';

// Fetch existing scholarship details
$sql = "SELECT * FROM scholarships WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Scholarship not found.");
}

$row = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $eligibility = $_POST['eligibility'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    $imageName = $row['images']; // default to existing image

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    $update = "UPDATE scholarships SET title=?, description=?, eligibility=?, deadline=?, status=?, images=? WHERE id=?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("ssssssi", $title, $description, $eligibility, $deadline, $status, $imageName, $id);
    
    if ($stmt->execute()) {
        $message = "Scholarship updated successfully!";
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM scholarships WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
    } else {
        $message = "Failed to update scholarship.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="css/form.scss">
  <link rel="stylesheet" href="../alert.scss">
  <title>Edit Scholarship</title>
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
        <h2>Edit Scholarship</h2>
        <a href="scholarships.php" class="btn btn-back">
          <i class="fas fa-arrow-left"></i>Go Back
        </a>
      </div>
      <div class="card shadow">
        <div class="card-body">
        <p class="text-muted mb-4">Complete the form below to edit the scholarship.</p>
          <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="title" class="form-label">Scholarship Title</label>
              <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($row['title']) ?>" required>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($row['description']) ?></textarea>
            </div>

            <div class="mb-3">
              <label for="eligibility" class="form-label">Eligibility</label>
              <textarea class="form-control" id="eligibility" name="eligibility" rows="4" required><?= htmlspecialchars($row['eligibility']) ?></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="date" class="form-control" id="deadline" name="deadline" value="<?= $row['deadline'] ?>" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="open" <?= $row['status'] == 'open' ? 'selected' : '' ?>>Open</option>
                  <option value="closed" <?= $row['status'] == 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
              <label for="image" class="form-label">Scholarship Image (optional)</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*">
              <?php if (!empty($row['images'])): ?>
                <div class="mt-2">
                  <img src="../uploads/<?= $row['images'] ?>" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                </div>
              <?php endif; ?>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Update Scholarship
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- SweetAlert2 JS -->
  <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
  <script>
    <?php if ($message): ?>
      const success = <?= json_encode($message === "Scholarship updated successfully!") ?>;
      const message = <?= json_encode($message) ?>;

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
          window.location.href = 'scholarships.php';  // Redirect after success
        }
      });
    <?php endif; ?>
  </script>

  <!-- Bootstrap 5 -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
  <script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script>
  <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>    
  <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
