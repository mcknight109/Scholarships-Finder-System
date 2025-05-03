<?php
include '../config.php'; // Database connection

if (isset($_GET['id']) && isset($_GET['update'])) {
    $application_id = $_GET['id'];

    $updateQuery = "UPDATE applications SET status = 'reviewed' WHERE id = ? AND status = 'pending'";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch application details
$application = null;
if (isset($_GET['id'])) {
    $application_id = $_GET['id'];

    $query = "SELECT a.*, u.name, u.gender 
              FROM applications a
              LEFT JOIN users u ON a.user_id = u.id
              WHERE a.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    
    <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="css/style.scss">
    <title>Document</title>
</head>
<body>
<header>
    <div class="logo">
        <img src="../images/realogo.png" alt="Logo Picture">
    </div>

    <div class="nav-side">
        <div class="prof-img">
            <a href="profile.php">
            <img src="../uploads/scholar3.jpg" alt="profile image">
            </a>
        </div>

        <div class="logout-btn">
            <a href="../logout.php">
            <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</header>
    <div class="viewApp">
        <div class="container my-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>View Application</h2>
                <a href="applicants.php" class="btn btn-secondary">Go Back</a>
            </div>
            <?php if ($application): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Application Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Name:</strong> <?= htmlspecialchars($application['name']) ?></div>
                            <div class="col-md-6"><strong>Gender:</strong> <?= htmlspecialchars($application['gender']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Age:</strong> <?= htmlspecialchars($application['age']) ?></div>
                            <div class="col-md-6"><strong>Contact:</strong> <?= htmlspecialchars($application['contact']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Address:</strong> <?= htmlspecialchars($application['address']) ?></div>
                            <div class="col-md-6"><strong>School:</strong> <?= htmlspecialchars($application['school']) ?></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6"><strong>Grade Level:</strong> <?= htmlspecialchars($application['grade_level']) ?></div>
                            <div class="col-md-6"><strong>Reason:</strong> <?= htmlspecialchars($application['reason']) ?></div>
                        </div>
                        <div class="mb-3">
                            <strong>Uploaded Document:</strong><br>
                            <?php if (!empty($application['document'])): ?>
                                <a href="../../uploads/<?= htmlspecialchars($application['document']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">View Document</a>
                            <?php else: ?>
                                <p class="text-muted">No document uploaded.</p>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Applied At:</strong> <?= htmlspecialchars($application['applied_at']) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong> 
                            <span class="badge 
                                <?php 
                                    if ($application['status'] == 'pending') echo 'bg-warning';
                                    elseif ($application['status'] == 'reviewed') echo 'bg-info';
                                    elseif ($application['status'] == 'approved') echo 'bg-success';
                                    elseif ($application['status'] == 'rejected') echo 'bg-danger';
                                ?>">
                                <?= ucfirst($application['status']) ?>
                            </span>
                        </div>

                        <?php if ($application['status'] == 'reviewed' || $application['status'] == 'pending'): ?>
                            <div class="d-flex gap-2 mt-4">
                                <form action="update-status.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $application['id'] ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn btn-success">Approve</button>
                                </form>

                                <form action="update-status.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $application['id'] ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">No application found.</div>
            <?php endif; ?>
        </div>
    </div>
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