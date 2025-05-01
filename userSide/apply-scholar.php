<?php
session_start();
include '../config.php';

$studentId = $_SESSION['user_id']; // make sure the user is logged in
$scholarshipId = $_GET['scholar_id'] ?? null;
$message = '';

// Fetch user details
$userQuery = "SELECT name, gender FROM users WHERE id = ?";
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
            $message = "<div class='alert alert-success'>Application submitted successfully with documents!</div>";
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
  <title>Apply Scholarship</title>
  <link rel="stylesheet" href="../assets/AdminLTE/dist/css/adminlte.min.css" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/bootstrap/bootstrap.min.js" />
  <link rel="stylesheet" href="../assets/AdminLTE/plugins/fontawesome-free/css/all.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="css/style.scss">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/msphLogo.png" alt="Logo Picture" class="img-fluid">
        </div>
        <div class="nav-side">
            <div class="prof-img">
                <a href="profile.php">
                    <img src="../uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="profile image">
                </a>
            </div>
            <div class="logout-btn">
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="applyWrapper">
        <div class="container my-5" style="max-width: 1240px">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Apply for Scholarship: <?php echo htmlspecialchars($scholarship['title']); ?></h2>
                <a href="home-page.php" class="btn btn-secondary">Go Back</a>
            </div>

            <form action="apply-scholar.php?scholar_id=<?php echo $scholarshipId; ?>" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Left Column: Application Form -->
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="mb-3">Scholarship Application Form</h3>
                                <p class="text-muted mb-4">Complete the form below to apply for the scholarship opportunity.</p>
                                <?php echo $message; ?>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <input type="text" class="form-control" id="gender" name="gender" value="<?php echo htmlspecialchars($user['gender']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="text" class="form-control" id="age" name="age" required>
                                </div>

                                <div class="mb-3">
                                    <label for="contact" class="form-label">Contact</label>
                                    <input type="text" class="form-control" id="contact" name="contact" required>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" required>
                                </div>

                                <div class="mb-3">
                                    <label for="school" class="form-label">School</label>
                                    <input type="text" class="form-control" id="school" name="school" required>
                                </div>

                                <div class="mb-3">
                                    <label for="grade_level" class="form-label">Grade Level</label>
                                    <input type="text" class="form-control" id="grade_level" name="grade_level" required>
                                </div>

                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason for Applying</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Submit Documents -->
                    <div class="col-md-6">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <h3 class="mb-3">Submit Documents</h3>
                                <div class="row g-3">
                                    <?php
                                    $docs = ['Certificate of Enrollment (COE)', 'Academic Records (TOR)', 'Birth Certificate', 'Valid School ID'];
                                    foreach ($docs as $key => $doc): ?>
                                        <div class="col-md-6">
                                            <div class="card p-3 shadow-sm">
                                                <label class="form-label"><?php echo $doc; ?></label>
                                                <input type="file" class="form-control" name="documents[]" required>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <p class="text-muted mt-2">Please upload required documents in PDF or image format.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unified submit button at the bottom -->
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit Application with Documents</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
