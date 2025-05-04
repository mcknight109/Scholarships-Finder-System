<?php
include '../config.php';

session_start();

$user_picture = 'default.jpg'; // fallback
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($user_picture);
    $stmt->fetch();
    $stmt->close();
}

$message = ""; // Default message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $gender   = $_POST['gender'];
    $picture  = '';

    // File upload handling
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "../../uploads/";
        $filename = basename($_FILES['picture']['name']);
        $targetPath = $uploadDir . $filename;

        // Move uploaded file
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetPath)) {
            $picture = $filename;
        } else {
            $message = "Failed to upload profile picture.";
        }
    }

    if (empty($message)) {
        $query = "INSERT INTO users (name, email, password, role, gender, picture)
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt  = $conn->prepare($query);
        $stmt->bind_param("ssssss", $name, $email, $password, $role, $gender, $picture);

        if ($stmt->execute()) {
            $message = "User account created successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/style.scss">
    <link rel="stylesheet" href="css/form.scss">
    <title>Add User</title>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/realogo.png" alt="Logo Picture">
        </div>
        <div class="nav-side">
            <div class="prof-img">
                <a href="stud-profile.php">
                <img src="../uploads/<?= htmlspecialchars($user_picture ?: 'default.jpg') ?>" alt="profile image">
                </a>
            </div>

            <div class="logout-btn">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="container my-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Create Account</h2>
            <a href="manage-users.php" class="btn btn-back">
                <i class="fas fa-arrow-left"></i>Go Back
            </a>
        </div>
           <div class="card shadow">
                <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                <form action="add_user.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" class="form-select" id="gender" required>
                            <option disabled selected value="">Choose...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select name="role" class="form-select" id="role">
                                <option value="student">Student</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" name="picture" id="picture">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" name="email" id="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="password" required>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
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
</html>
</body>