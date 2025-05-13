<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$sql = "SELECT name, email, gender, picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $gender, $picture);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = $_POST['name'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_gender = $_POST['gender'];
    $picture_filename = $picture;

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['picture']['tmp_name'];
        $orig_name = basename($_FILES['picture']['name']);
        $ext = pathinfo($orig_name, PATHINFO_EXTENSION);
        $new_filename = uniqid('pfp_', true) . '.' . $ext;
        $destination = '../uploads/' . $new_filename;

        if (move_uploaded_file($tmp_name, $destination)) {
            $picture_filename = $new_filename;
        }
    }

    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET name=?, email=?, password=?, gender=?, picture=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $new_name, $new_email, $hashed_password, $new_gender, $picture_filename, $user_id);
    } else {
        $update_sql = "UPDATE users SET name=?, email=?, gender=?, picture=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $new_name, $new_email, $new_gender, $picture_filename, $user_id);
    }

    $update_success = false;

    if ($stmt->execute()) {
        $update_success = true;
    } else {
        $update_success = false;
    }

    $stmt->close();
}
// Count approved applications for notification
$notif_stmt = $conn->prepare("SELECT COUNT(*) AS notif_count FROM applications WHERE user_id = ? AND status = 'approved'");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
$notif_row = $notif_result->fetch_assoc();
$notif_count = $notif_row['notif_count'];
?>

<!-- HTML Starts Below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/style.scss">
    <link rel="stylesheet" href="../alert.scss">
    <title>Student Profile</title>
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
                    <img src="../uploads/<?php echo htmlspecialchars($picture); ?>" alt="profile image">
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
            <div class="profile-container">
                <div class="profile-form">
                    <div class="form-header">
                        <h2>Edit Profile</h2>
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="text-center mb-4">
                            <img id="previewImage" src="<?= !empty($picture) ? '../uploads/' . htmlspecialchars($picture) : 'default-avatar.png' ?>" width="120" height="120" style="border-radius: 50%; border: 1px solid #892ea4;">
                            <br><br>
                            <input type="file" name="picture" accept="image/*" onchange="previewProfileImage(this)" class="form-control-file">
                        </div>
                        <div class="form-group">
                            <label>Name:</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required class="form-control" style="border-radius: 20px; border: 1px solid #892ea4;">
                        </div>
                        <div class="form-group">
                            <label>Gender:</label>
                            <select name="gender" required class="form-control" style="border-radius: 20px; border: 1px solid #892ea4;">
                                <option value="Male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="form-control" style="border-radius: 20px; border: 1px solid #892ea4;">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="password" placeholder="Leave blank to keep current" class="form-control" style="border-radius: 20px; border: 1px solid #892ea4;">
                        </div>
                        <div class="action-btn">
                            <button type="submit" style="border-radius: 20px; height: 45px; width: 100%; border: 0; background-color: #892ea4; color: #ffffff;">Save Changes</button>
                        </div>
                    </form>
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
        function previewProfileImage(input) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    </script>

<?php if (isset($update_success) && $update_success): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Profile Updated!',
        text: 'Your changes have been saved successfully.',
        confirmButtonText: 'Go Back',
        width: 330,
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-text',
            icon: 'custom-swal-icon',
            confirmButton: 'custom-swal-btn',
            cancelButton: 'custom-swal-cancel'
        },
        buttonsStyling: false
    }).then(() => {
        window.location.href = 'profile.php';
    });

</script>
    <?php elseif (isset($update_success) && !$update_success): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed!',
            text: 'There was a problem saving your changes.',
            confirmButtonText: 'Go Back',
            width: 330,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                htmlContainer: 'custom-swal-text',
                icon: 'custom-swal-icon',
                confirmButton: 'custom-swal-btn',
                cancelButton: 'custom-swal-cancel'
            },
            buttonsStyling: false
        });
</script>
    <?php endif; ?>

</body>
</html>
