<?php
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];
    $contact  = $_POST['contact'];
    $age      = $_POST['age'];
    $gender   = $_POST['gender'];
    $school   = $_POST['school'];
    $address  = $_POST['address'];
    $picture  = ''; // You can handle file upload here if needed

    $query = "INSERT INTO users (name, email, password, role, contact, age, gender, school, address, picture)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt  = $conn->prepare($query);
    $stmt->bind_param("ssssssssss", $name, $email, $password, $role, $contact, $age, $gender, $school, $address, $picture);

    if ($stmt->execute()) {
        header("Location: ../manage-users.php?status=added");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../AdminLTE/plugins/bootstrap/bootstrap.min.js">
    <link rel="stylesheet" href="../AdminLTE/plugins/fontawesome-free/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.scss">
</head>
<body>
    <header>
        <div class="logo">
        <img src="../../images/msphLogo.png" alt="Logo Picture">
        </div>
    </header>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Add new User</h2>
            <a href="../scholarships.php" class="btn btn-secondary">Back to List</a>
        </div>
            <div class="card shadow">
                <div class="card-body">
                <form action="add_user.php" method="POST" enctype="multipart/form-data">

                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact" class="form-label">Contact Number</label>
                        <input type="text" class="form-control" name="contact" id="contact" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="age" class="form-label">Age</label>
                            <input type="text" class="form-control" name="age" id="age" required>
                        </div>
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select name="gender" class="form-select" id="gender" required>
                            <option disabled selected value="">Choose...</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    </div>

                    <div class="mb-3">
                        <label for="school" class="form-label">School</label>
                        <input type="text" class="form-control" name="school" id="school" required>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Complete Address</label>
                        <input type="text" class="form-control" name="address" id="address" required>
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

                    <div class="d-flex justify-content-between">
                        <a href="../manage-users.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE Scripts -->
    <script src="../AdminLTE/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../AdminLTE/dist/js/adminlte.min.js"></script>
</html>
