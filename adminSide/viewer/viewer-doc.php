<?php
session_start();
include '../../config.php'; // Adjust path to match your structure

// Security check - only logged in users can access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$user_picture = '../../uploads/default.png'; // default fallback

// Fetch logged-in user's profile picture
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $picQuery = "SELECT picture FROM users WHERE id = ?";
    $stmt = $conn->prepare($picQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($picture);
    if ($stmt->fetch() && !empty($picture)) {
        $user_picture = "../../uploads/" . $picture;
    }
    $stmt->close();
}

// Get the file parameter
$file = isset($_GET['file']) ? $_GET['file'] : '';
$file_path = '../../uploads/' . $file; // Adjust to your actual upload path

// Security validation - prevent directory traversal attacks
$real_path = realpath($file_path);
$uploads_dir = realpath('../../uploads/'); // Path to your uploads directory

if (empty($file) || !$real_path || strpos($real_path, $uploads_dir) !== 0 || !file_exists($real_path)) {
    $error = "Invalid or unauthorized file access. Path: " . htmlspecialchars($file_path) . " | Real Path: " . htmlspecialchars($real_path) . " | Uploads Dir: " . htmlspecialchars($uploads_dir);
} else {
    // Get file extension
    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/AdminLTE/plugins/fontawesome-free/css/all.css">
    <link rel="stylesheet" href="../../assets/AdminLTE/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="../css/style.scss">
    <title>Document Viewer</title>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        header {
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .document-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin: 24px auto;
            max-width: 1200px;
            overflow: hidden;
        }
        
        .document-header {
            padding: 20px 24px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .document-title {
            font-size: 1.5rem;
            color: #333;
            margin: 0;
        }
        
        .document-content {
            padding: 0;
            text-align: center;
            min-height: 600px;
            background-color: #f0f0f0;
            position: relative;
        }
        
        .viewer-frame {
            width: 100%;
            height: 800px;
            border: none;
        }
        
        .image-viewer {
            max-width: 100%;
            max-height: 800px;
            margin: 20px auto;
            display: block;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .doc-preview {
            padding: 40px;
            background-color: #fff;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        
        .toolbar {
            background-color: #343a40;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .toolbar .btn {
            margin-left: 10px;
        }
        
        .error-container {
            padding: 100px 20px;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .btn-back {
            background-color: #3c8dbc;
            color: white;
        }
        
        .btn-back:hover {
            background-color: #367fa9;
            color: white;
        }
        
        @media (max-width: 768px) {
            .document-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .toolbar {
                flex-direction: column;
                gap: 10px;
            }
            
            .toolbar .btn-group {
                width: 100%;
                display: flex;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../../images/realogo.png" alt="Logo Picture" style="max-height: 40px;">
        </div>

        <div class="nav-side">
            <div class="prof-img">
                <a href="../../profile.php">
                    <img src="<?= $user_picture ?>" alt="profile image" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                </a>
            </div>

            <div class="logout-btn">
                <a href="../../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </header>

    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <?php if (isset($error)): ?>
                    <div class="document-container error-container">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <h3>Document Error</h3>
                        <p class="text-muted"><?= $error ?></p>
                        <a href="javascript:history.back()" class="btn btn-back mt-3">
                            <i class="fas fa-arrow-left mr-2"></i> Go Back
                        </a>
                    </div>
                <?php else: ?>
                    <div class="document-container">
                        <div class="document-header">
                            <h4 class="document-title">
                                <i class="fas fa-file-alt mr-2"></i>
                                <?= htmlspecialchars($file) ?>
                            </h4>
                            <div>
                                <a href="javascript:history.back()" class="btn btn-back">
                                    <i class="fas fa-arrow-left mr-2"></i> Back to Application
                                </a>
                                <a href="<?= $file_path ?>" download class="btn btn-success ml-2">
                                    <i class="fas fa-download mr-2"></i> Download
                                </a>
                            </div>
                        </div>
                        
                        <div class="toolbar">
                            <div>
                                <i class="fas <?php 
                                    if ($file_extension == 'pdf') echo 'fa-file-pdf';
                                    elseif (in_array($file_extension, ['doc', 'docx'])) echo 'fa-file-word'; 
                                    elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) echo 'fa-file-image';
                                    else echo 'fa-file';
                                ?> mr-2"></i>
                                File Type: <?= strtoupper($file_extension) ?>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-light" onclick="window.print()">
                                    <i class="fas fa-print mr-1"></i> Print
                                </button>
                                <?php if ($file_extension == 'pdf'): ?>
                                <button class="btn btn-sm btn-outline-light" id="zoomIn">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-light" id="zoomOut">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="document-content">
                            <?php
                            // Display content based on file type
                            if ($file_extension == 'pdf') {
                                echo '<iframe src="' . $file_path . '" class="viewer-frame" id="pdfViewer"></iframe>';
                            } 
                            else if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                echo '<img src="' . $file_path . '" class="image-viewer" alt="Document Image">';
                            }
                            else if (in_array($file_extension, ['doc', 'docx'])) {
                                // For Word documents, offer download or Google Docs viewer
                                echo '<div class="doc-preview">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-file-word fa-5x text-primary"></i>
                                            <h4 class="mt-3">Microsoft Word Document</h4>
                                            <p class="text-muted">Preview not available. Please download the file to view its contents.</p>
                                            <a href="' . $file_path . '" download class="btn btn-primary mt-2">
                                                <i class="fas fa-download mr-2"></i> Download Document
                                            </a>
                                            <a href="https://docs.google.com/viewer?url=' . urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$file_path") . '&embedded=true" target="_blank" class="btn btn-info mt-2 ml-2">
                                                <i class="fas fa-external-link-alt mr-2"></i> View in Google Docs
                                            </a>
                                        </div>
                                      </div>';
                            } 
                            else {
                                echo '<div class="doc-preview">
                                        <div class="text-center mb-4">
                                            <i class="fas fa-file fa-5x text-secondary"></i>
                                            <h4 class="mt-3">Unknown File Type</h4>
                                            <p class="text-muted">Preview not available for this file type. Please download the file to view its contents.</p>
                                            <a href="' . $file_path . '" download class="btn btn-primary mt-2">
                                                <i class="fas fa-download mr-2"></i> Download File
                                            </a>
                                        </div>
                                      </div>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="../assets/sweetalert2/sweetalert2.all.min.js"></script>
    <!-- AdminLTE Scripts -->    
    <script src="../assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>    
    <script src="../assets/AdminLTE/plugins/jquery/jquery.min.js"></script>    
    <script src="../assets/AdminLTE/dist/js/adminlte.min.js"></script>
    <script>
        // PDF viewer zoom controls
        document.addEventListener('DOMContentLoaded', function() {
            const pdfViewer = document.getElementById('pdfViewer');
            const zoomIn = document.getElementById('zoomIn');
            const zoomOut = document.getElementById('zoomOut');
            
            if (pdfViewer && zoomIn && zoomOut) {
                let scale = 1;
                
                zoomIn.addEventListener('click', function() {
                    scale += 0.1;
                    pdfViewer.style.transform = `scale(${scale})`;
                    pdfViewer.style.transformOrigin = 'top center';
                });
                
                zoomOut.addEventListener('click', function() {
                    if (scale > 0.5) {
                        scale -= 0.1;
                        pdfViewer.style.transform = `scale(${scale})`;
                        pdfViewer.style.transformOrigin = 'top center';
                    }
                });
            }
        });
    </script>
</body>
</html>