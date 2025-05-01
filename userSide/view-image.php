<?php
// Check if image is provided
if (!isset($_GET['img'])) {
    echo "Image not found.";
    exit;
}

$image = basename($_GET['img']); // Security: prevent path traversal
$image_path = "../../uploads/" . $image; // Updated path to the uploads folder

// Check if the image file exists
if (!file_exists($image_path)) {
    echo "Image does not exist.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Image</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light text-center">

    <div class="container mt-5">
        <h3 class="mb-4">Scholarship Image</h3>
        <img src="<?php echo htmlspecialchars($image_path); ?>" class="img-fluid border rounded" style="max-height: 80vh;" alt="Scholarship Image">
        <div class="mt-3">
            <a href="home-page.php" class="btn btn-primary">Back to Homepage</a>
        </div>
    </div>

</body>
</html>
