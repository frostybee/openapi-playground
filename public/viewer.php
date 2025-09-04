<?php

require_once '../src/config/app.php';
require_once '../src/classes/SessionManager.php';
require_once '../src/classes/FileManager.php';
require_once '../src/classes/ExampleManager.php';

// Start the session and initialize the uploaded files storage.
SessionManager::start();

// Get the file ID and renderer from the query parameters.
$fileId = trim($_GET['id'] ?? '');
$renderer = trim($_GET['renderer'] ?? 'swagger');

// Sanitize inputs
$fileId = preg_replace('/[^a-zA-Z0-9._-]/', '', $fileId);
$renderer = preg_replace('/[^a-zA-Z0-9]/', '', $renderer);

// Check if this is an example file or uploaded file
$isExample = str_starts_with($fileId, 'example_');
$file = null;
$filePath = '';

if ($isExample) {
    // Get example file data
    $file = ExampleManager::getExample($fileId);
    if ($file) {
        $filePath = ExampleManager::getExampleFilePath($file['file_name']);
    }
} else {
    // Get uploaded file data
    $file = SessionManager::getUploadedFile($fileId);
    if ($file) {
        $filePath = FileManager::getFilePath($file['file_name']);
    }
}

// If the file is not found, redirect to the index page.
if (!$fileId || !$file) {
    header('Location: index.php');
    exit;
}

// Check if file exists
$fileExists = $isExample ?
    ExampleManager::exampleExists($file['file_name']) :
    FileManager::fileExists($file['file_name']);

if (!$fileExists) {
    header('Location: index.php');
    exit;
}

// Determine if we need to serve the spec file directly.
if (isset($_GET['spec'])) {
    // Additional security check for file path
    if (empty($filePath) || !file_exists($filePath)) {
        http_response_code(404);
        exit('File not found');
    }

    // Validate the file path is within allowed directories
    $realPath = realpath($filePath);
    $allowedPaths = [
        realpath(__DIR__ . '/../storage/uploads/'),
        realpath(__DIR__ . '/../examples/')
    ];

    $pathAllowed = false;
    foreach ($allowedPaths as $allowedPath) {
        if ($allowedPath && strpos($realPath, $allowedPath) === 0) {
            $pathAllowed = true;
            break;
        }
    }

    if (!$pathAllowed) {
        http_response_code(403);
        exit('Access denied');
    }

    $extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));

    // Set appropriate content type and security headers
    if ($extension === 'json') {
        header('Content-Type: application/json; charset=utf-8');
    } else {
        header('Content-Type: text/yaml; charset=utf-8');
    }

    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-cache, no-store, must-revalidate');

    // Read file safely
    $content = file_get_contents($filePath);
    if ($content !== false) {
        echo $content;
    } else {
        http_response_code(500);
        exit('Unable to read file');
    }
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
    <title><?php echo htmlspecialchars($file['display_name'] ?? $file['custom_name']); ?> - <?php echo ucfirst($renderer); ?></title>
    <link rel="stylesheet" href="./assets/css/viewer.css">
    <link rel="stylesheet" href="./assets/css/rapidoc-viewer.css">
</head>

<body>
    <div class="header">
        <h1>Schema:&nbsp; <?php echo htmlspecialchars($file['display_name'] ?? $file['custom_name']); ?></h1>
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
        // Include the appropriate renderer template with security validation
        $allowedTemplates = ['swagger-viewer.php', 'rapidoc-viewer.php'];
        $templateFile = $renderer . '-viewer.php';

        if (in_array($templateFile, $allowedTemplates)) {
            $templatePath = __DIR__ . "/views/" . $templateFile;
            $realTemplatePath = realpath($templatePath);
            $viewsDir = realpath(__DIR__ . "/views/");

            // Ensure template is in the views directory
            if ($realTemplatePath && $viewsDir && strpos($realTemplatePath, $viewsDir) === 0) {
                include $templatePath;
            } else {
                echo "<p>Error: Template access denied.</p>";
            }
        } else {
            echo "<p>Error: Invalid renderer template.</p>";
        }
        ?>
    </div>
</body>

</html>
