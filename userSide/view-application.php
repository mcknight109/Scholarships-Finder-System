<?php
session_start();
include '../../config.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: ../login.php");
    exit;
}

// Fetch application and user info including scholarship title
$sql = "SELECT 
            a.id AS application_id, a.age, a.contact, a.address, a.school, a.grade_level, 
            a.reason, a.document, a.status, 
            u.name, u.gender,
            s.title AS scholarship_title
        FROM applications a
        JOIN users u ON a.user_id = u.id
        JOIN scholarships s ON a.scholarship_id = s.id
        WHERE a.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../sweetalert2/sweetalert2.min.css">
    <link rel="stylesheet" href="../css/style.scss">
</head>
<body>
<header>
    <div class="logo">
        <img src="../../images/msphLogo.png" alt="Logo Picture">
    </div>
    <ul>
        <li>
            <a href="index.php">
                <p>Go back</p>
            </a>
        </li>
    </ul>
</header>

<div class="viewWrapper">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Applied Scholarship: <?= htmlspecialchars($application['scholarship_title']) ?></h2>
            <a href="../applications.php" class="btn btn-secondary">Go Back</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
            <?php if ($application): ?>
            <h3 class="mb-3">Application Details</h3>
            <p class="text-muted mb-4">Below are the details of your scholarship application.</p>

            <form>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['name']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Gender</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['gender']) ?>" readonly>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Age</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['age']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['contact']) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($application['address']) ?>" readonly>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">School</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['school']) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Grade Level</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($application['grade_level']) ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason for Application</label>
                    <textarea class="form-control" rows="4" readonly><?= htmlspecialchars($application['reason']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Submitted Document</label><br>
                    <?php if (!empty($application['document'])): ?>
                        <a href="../../uploads/<?= htmlspecialchars($application['document']) ?>" target="_blank" class="btn btn-primary btn-sm mt-2">
                        View Document
                        </a>
                    <?php else: ?>
                        <p class="text-muted mt-2">No document uploaded.</p>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Application Status</label>
                    <p class="form-control-plaintext">
                        <?= ucfirst(strtolower($application['status'])) ?>
                    </p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="../stud-applications.php" class="btn btn-secondary">Back</a>
                    <button type="button" class="btn btn-danger" onclick="confirmCancel(<?= $application['application_id'] ?>)">
                        Cancel Application
                    </button>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-info text-center">
                No application found.
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../sweetalert2/sweetalert2.all.min.js"></script>
<script>
    function confirmCancel(applicationId) {
        Swal.fire({
            title: "Are you sure?",
            text: "You are about to cancel your application.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, cancel it",
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
                window.location.href = `cancel-application.php?id=${applicationId}`;
            }
        });
    }
</script>

</body>
</html>
