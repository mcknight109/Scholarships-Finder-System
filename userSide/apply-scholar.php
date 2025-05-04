<?php
session_start();
include '../config.php';

$studentId = $_SESSION['user_id']; // make sure the user is logged in
$scholarshipId = $_GET['scholar_id'] ?? null;
$message = '';

// Fetch user details
$userQuery = "SELECT name, gender, picture FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $studentId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$user = $userResult->fetch_assoc();

// Fetch scholarship title
$scholarshipQuery = "SELECT title FROM scholarships WHERE id = ?";
$scholarshipStmt = $conn->prepare($scholarshipQuery);
$scholarshipStmt->bind_param("i", $scholarshipId);
$scholarshipStmt->execute();
$scholarshipResult = $scholarshipStmt->get_result();
$scholarship = $scholarshipResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = $_POST['age'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $school = $_POST['school'];
    $grade_level = $_POST['grade_level'];
    $reason = $_POST['reason'];
    $target_dir = "../uploads/";

    $uploaded_files = [];

if (isset($_FILES['documents']) && is_array($_FILES['documents']['name']) && !empty($_FILES['documents']['name'][0])) {
    foreach ($_FILES['documents']['name'] as $index => $fileName) {
        $fileTmp = $_FILES['documents']['tmp_name'][$index];
        $uniqueName = time() . '_' . uniqid() . '_' . basename($fileName);
        $filePath = $target_dir . $uniqueName;

        if (move_uploaded_file($fileTmp, $filePath)) {
            $uploaded_files[] = $uniqueName;
        }
    }

    if (count($uploaded_files) === count($_FILES['documents']['name'])) {
        $document_json = json_encode($uploaded_files);

        $insert = "INSERT INTO applications (user_id, scholarship_id, age, contact, address, school, grade_level, reason, document, status)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insert);
        $stmt->bind_param("iisssssss", $studentId, $scholarshipId, $age, $contact, $address, $school, $grade_level, $reason, $document_json);

        if ($stmt->execute()) {
            $message = "success";
        } else {
            $message = "<div class='alert alert-danger'>Database error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Some documents failed to upload.</div>";
    }
} else {
    $message = "<div class='alert alert-warning'>No documents uploaded.</div>";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
  <link rel="stylesheet" href="css/style.scss">
  <link rel="stylesheet" href="css/temp.scss">
  <link rel="stylesheet" href="../alert.scss">
  <title>Apply for Scholarship</title>
</head>
<body>
    <!-- Header remains unchanged -->
    <header>
        <div class="logo">
            <img src="../images/realogo.png" alt="Logo Picture" class="img-fluid">
        </div>
        <div class="nav-side">
            <div class="prof-img">
                <a href="profile.php">
                    <img src="../uploads/<?php echo htmlspecialchars($user['picture']); ?>" alt="profile image">
                </a>
            </div>
            <div class="logout-btn">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Begin redesigned applyWrapper section -->
    <div class="applyWrapper">
        <div class="container">
            <div class="page-header">
                <h2>Apply for Scholarship: <?php echo htmlspecialchars($scholarship['title']); ?></h2>
                <a href="home-page.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Scholarships
                </a>
            </div>

            <?php echo $message; ?>

            <form action="apply-scholar.php?scholar_id=<?php echo $scholarshipId; ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Left Column: Application Form -->
                    <div class="LeftForm col-lg-6">
                        <div class="card form-card">
                            <div class="card-body">
                                <h3>Personal Information</h3>
                                <p class="text-muted mb-4">Complete the form below to apply for this scholarship opportunity.</p>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <input type="text" class="form-control" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" required min="1" max="100">
                                </div>

                                <div class="mb-3">
                                    <label for="contact" class="form-label">Contact Number</label>
                                    <input type="tel" class="form-control" id="contact" name="contact" required placeholder="e.g., +63 912 345 6789">
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Complete Address</label>
                                    <input type="text" class="form-control" id="address" name="address" required placeholder="Street, City, Province, Postal Code">
                                </div>

                                <div class="mb-3">
                                    <label for="school" class="form-label">School/University Name</label>
                                    <input type="text" class="form-control" id="school" name="school" required placeholder="Enter your School">
                                </div>

                                <div class="mb-3">
                                    <label for="grade_level" class="form-label">Grade/Year Level</label>
                                    <input type="text" class="form-control" id="grade_level" name="grade_level" required placeholder="e.g., 3rd Year College, Grade 10">
                                </div>

                                <div class="mb-4">
                                    <label for="reason" class="form-label">Why are you applying for this scholarship?</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="4" required placeholder="Please explain your reasons and how this scholarship will help you achieve your goals..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Submit Documents -->
                    <div class="rightFrom col-lg-6">
                        <div class="card form-card">
                            <div class="card-body">
                                <h3>Required Documents</h3>
                                <p class="text-muted mb-4">Please upload all required documents in PDF or image format (JPG, PNG).</p>

                                <div class="row g-3">
                                    <?php
                                    $docs = [
                                        'Certificate of Enrollment (COE)' => 'Current enrollment proof from your school',
                                        'Academic Records (TOR)' => 'Transcript of Records or recent Report Card',
                                        'Birth Certificate' => 'PSA/NSO issued Birth Certificate',
                                        'Valid School ID' => 'Front and back of your school ID'
                                    ];
                                    
                                    $i = 1;
                                    foreach ($docs as $doc => $description): ?>
                                    <div class="col-md-6">
                                        <div class="doc-card">
                                            <div class="doc-title">
                                                <i class="fas fa-file-alt me-2" style="color: #5c1d6f;"></i>
                                                <?php echo $doc; ?>
                                            </div>
                                            <p class="text-muted small"><?php echo $description; ?></p>
                                            <input type="file" class="form-control" name="documents[]" id="doc<?php echo $i++; ?>" required>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="mt-4">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-submit">
                                            <i class="fas fa-paper-plane me-2"></i> Submit Application
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

    <?php if ($message === 'success'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Application Submitted!',
                text: 'Your scholarship application has been successfully submitted.',
                confirmButtonColor: '#5c1d6f',
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
                window.location.href = 'home-page.php';
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>