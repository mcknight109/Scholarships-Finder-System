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
        $update_sql = "UPDATE users SET name=?, email=?, password=? gender=?, picture=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sssssi", $new_name, $new_email, $hashed_password, $new_gender, $picture_filename, $user_id);
    } else {
        $update_sql = "UPDATE users SET name=?, email=?, gender=?, picture=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $new_name, $new_email, $new_gender, $picture_filename, $user_id);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); location.href='profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }

    $stmt->close();
}
?>

<!-- HTML Starts Below -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Profile</title>
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="css/style.scss">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    

        <div class="content-wrapper d-flex flex-wrap">
            <div class="profile-wrapper">
                <div class="form-header"><h3>Edit Profile</h3></div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="pfp text-center">
                        <img id="previewImage" src="<?= !empty($picture) ? '../uploads/' . htmlspecialchars($picture) : 'default-avatar.png' ?>" width="120" height="120">
                        <br><br>
                        <input type="file" name="picture" accept="image/*" onchange="previewProfileImage(this)">
                    </div>
                    <div class="form-container">
                        <div class="form-input">
                            <label>Name:</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
                        </div>
                        <div class="form-input">
                            <label>Gender:</label>
                            <select name="gender" required>
                                <option value="Male" <?= $gender === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="Female" <?= $gender === 'female' ? 'selected' : '' ?>>Female</option>
                            </select>
                        </div>
                        <div class="form-input">
                            <label>Email:</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="form-input">
                            <label>Password:</label>
                            <input type="password" name="password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="action-btn mt-3">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewProfileImage(input) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    </script>
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
