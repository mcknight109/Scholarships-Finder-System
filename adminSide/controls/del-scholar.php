<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Fetch scholarship to archive
    $query = mysqli_query($conn, "SELECT * FROM scholarships WHERE id = $id");
    if (mysqli_num_rows($query) > 0) {
        $scholar = mysqli_fetch_assoc($query);

        // Insert into archive table
        $archive = mysqli_query($conn, "
            INSERT INTO scholarships_archive (original_id, title, description, eligibility, deadline, images, status)
            VALUES (
                '{$scholar['id']}',
                '" . mysqli_real_escape_string($conn, $scholar['title']) . "',
                '" . mysqli_real_escape_string($conn, $scholar['description']) . "',
                '" . mysqli_real_escape_string($conn, $scholar['eligibility']) . "',
                '{$scholar['deadline']}',
                '" . mysqli_real_escape_string($conn, $scholar['images']) . "',
                '{$scholar['status']}'
            )
        ");

        if ($archive) {
            // Delete the original
            mysqli_query($conn, "DELETE FROM scholarships WHERE id = $id");
            $_SESSION['message'] = 'Scholarship archived successfully.';
        } else {
            $_SESSION['error'] = 'Failed to archive scholarship.';
        }
    } else {
        $_SESSION['error'] = 'Scholarship not found.';
    }
}

header("Location: ../scholarships.php");
exit();
?>
