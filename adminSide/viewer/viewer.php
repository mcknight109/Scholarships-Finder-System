<?php
session_start();
include '../../config.php';

$filename = isset($_GET['file']) ? basename($_GET['file']) : null;
$filePath = "../../uploads/" . $filename;

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="CSS/style.css" type="text/css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.9.359/pdf.min.js"
		integrity="sha512-U5C477Z8VvmbYAoV4HDq17tf4wG6HXPC6/KM9+0/wEXQQ13gmKY2Zb0Z2vu0VNUWch4GlJ+Tl/dfoLOH4i2msw==" crossorigin="anonymous"
		referrerpolicy="no-referrer"></script>
	<title>PDF Viewer</title>
</head>

<body>
	<main>
		<?php if ($filename && file_exists($filePath)): ?>
			<?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
				<img src="<?= htmlspecialchars($filePath) ?>" alt="Image Document" style="max-width: 100%; height: auto;">
			<?php elseif ($ext == 'pdf'): ?>
				<iframe src="<?= htmlspecialchars($filePath) ?>" width="100%" height="600px" style="border: none;"></iframe>
			<?php elseif ($ext == 'docx'): ?>
				<p><a href="<?= htmlspecialchars($filePath) ?>" target="_blank" class="btn btn-primary">Download and View DOCX</a></p>
			<?php else: ?>
				<p>Unsupported file format.</p>
			<?php endif; ?>
		<?php else: ?>
			<p>No document found or file is missing.</p>
		<?php endif; ?>
	</main>
	<footer>
		<ul>
			<li>
				<a href="../applicants.php">
					<button>Go Back</button>
				</a>
			</li>
			<li class="pagination">
				<button id="previous"><i class="fas fa-arrow-alt-circle-left"></i></button>
				<span id="current_page">0 of 0</span>
				<button id="next"><i class="fas fa-arrow-alt-circle-right"></i></button>
			</li>

			<li>
				<span id="zoomValue">150%</span>
				<input type="range" id="zoom" name="cowbell" min="100" max="300" value="150" step="50" disabled>
			</li>
		</ul>
	</footer>
	<script type="text/javascript" src="JS/index.js"></script>
</body>

</html>