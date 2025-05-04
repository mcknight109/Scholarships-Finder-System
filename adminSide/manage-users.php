<?php
include '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT picture FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$profileImage = !empty($user['picture']) ? $user['picture'] : 'default.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
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
                    <img src="../uploads/<?= htmlspecialchars($profileImage) ?>" alt="profile image">
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
                <p class="fs-4 fw-semibold">Users Management</p>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="admin-dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">User Management</li>
                </ol>
            </div>

            <div class="content">
                <div class="tableH">
                    <form class="form-inline">
                        <div class="input-group input-group-sm">
                            <input id="searchInput" class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar border " type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <div class="sideHeader">
                        <a href="">
                            <button>Archives</button>
                        </a>
                        <a href="add-user.php">
                            <button>
                                <i class="fas fa-plus me-2"></i>Create Account
                            </button>
                        </a>
                    </div>
                </div>

            <!-- User Table Card -->
            <div class="card">
                <div class="card-body">
                    <table id="userTable" class="table table-bordered table-hover">
                        <thead class="">
                            <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Role</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM users";
                        $result = $conn->query($query);
                        $count = 1;
                        while ($row = $result->fetch_assoc()):
                        ?>
                        <tr>
                            <td class="text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="../uploads/<?= htmlspecialchars($row['picture']) ?>" alt="Profile" width="40" height="40" style="border-radius: 50%; object-fit: cover;">
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['gender']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>
                            <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#editUserModal<?= $row['id'] ?>">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?= $row['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            </td>
                        </tr>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal<?= $row['id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <form action="controls/edit_user.php" method="POST" enctype="multipart/form-data">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit User</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <div class="form-group text-center">
                                                <img src="../uploads/<?= htmlspecialchars($row['picture']) ?>" alt="Current Picture" width="80" height="80" style="border-radius: 50%; object-fit: cover;">
                                                <div class="mt-2">
                                                    <input type="file" name="picture" class="form-control-file">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Name</label>
                                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($row['name']) ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Role</label>
                                                <select name="role" class="form-control">
                                                    <option value="student" <?= $row['role'] == 'student' ? 'selected' : '' ?>>Student</option>
                                                    <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Password (leave blank to keep current)</label>
                                                <input type="password" name="password" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>     
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <!-- <script src="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js"></script> -->
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
    <!-- AdminLTE Table -->
    <script src="../assets/AdminLTE/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/AdminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="../assets/AdminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
    <script src="../assets/AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $("#userTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#userTable').DataTable({
            paging: true,
            info: true,
            searching: false,
            lengthChange: false,
            ordering: false,
            responsive: true,
            pageLength: 7
            });
        });
    </script>

    <script>
        function deleteUser(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!',
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
                    window.location.href = 'controls/delete_user.php?id=' + userId;
                }
            });
        }
    </script>
</body>
</html>
