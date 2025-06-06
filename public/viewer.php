<?php

require_once '../src/config/app.php';
require_once '../src/classes/SessionManager.php';
require_once '../src/classes/FileManager.php';

// Start the session and initialize the uploaded files storage.
SessionManager::start();

// Get the file ID and renderer from the query parameters.
$fileId = $_GET['id'] ?? '';
$renderer = $_GET['renderer'] ?? 'swagger';

// Get the file data.
$file = SessionManager::getUploadedFile($fileId);

// If the file is not found, redirect to the index page.
if (!$fileId || !$file) {
    header('Location: index.php');
    exit;
}

// Get the file path and check if it exists.
$filePath = FileManager::getFilePath($file['file_name']);

if (!FileManager::fileExists($file['file_name'])) {
    header('Location: index.php');
    exit;
}

// Determine if we need to serve the spec file directly.
if (isset($_GET['spec'])) {
    $extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

    if ($extension === 'json') {
        header('Content-Type: application/json');
    } else {
        header('Content-Type: text/yaml');
    }

    readfile($filePath);
    exit;
}

// Validate renderer
$validRenderers = ['swagger', 'rapidoc'];
if (!in_array($renderer, $validRenderers)) {
    $renderer = 'swagger'; // Default fallback
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($file['custom_name']); ?> - <?php echo ucfirst($renderer); ?></title>
    <link rel="stylesheet" href="./assets/css/viewer.css">
    <link rel="stylesheet" href="./assets/css/rapidoc-viewer.css">
</head>

<body>
    <div class="header">
        <h1>Schema:&nbsp; <?php echo htmlspecialchars($file['custom_name']); ?></h1>
        <div class="header-actions">
            <a href="viewer.php?id=<?php echo $fileId; ?>&renderer=swagger"
                class="btn <?php echo $renderer === 'swagger' ? 'btn-active' : 'btn-light'; ?>">
                Swagger UI
            </a>
            <a href="viewer.php?id=<?php echo $fileId; ?>&renderer=rapidoc"
                class="btn <?php echo $renderer === 'rapidoc' ? 'btn-active' : 'btn-light'; ?>">
                RapiDoc
            </a>
            <a href="index.php" class="btn btn-light">← Back</a>
        </div>
    </div>

    <div class="viewer-container">
        <?php
        // Include the appropriate renderer template
        $templatePath = __DIR__ . "/views/{$renderer}-viewer.php";
        if (file_exists($templatePath)) {
            include $templatePath;
        } else {
            echo "<p>Error: Renderer template not found.</p>";
        }
        ?>
    </div>
</body>

</html>
