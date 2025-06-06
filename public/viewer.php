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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($file['custom_name']); ?> - <?php echo ucfirst($renderer); ?></title>
    <link rel="stylesheet" href="./assets/css/viewer.css">

    <?php if ($renderer === 'swagger'): ?>
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui.css" />
    <?php else: ?>
        <script type="module" src="https://unpkg.com/rapidoc/dist/rapidoc-min.js"></script>
    <?php endif; ?>
</head>

<body>
    <div class="header">
        <h1><?php echo htmlspecialchars($file['custom_name']); ?></h1>
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
        <?php if ($renderer === 'swagger'): ?>
            <div id="swagger-ui"></div>
            <script src="https://unpkg.com/swagger-ui-dist@5.0.0/swagger-ui-bundle.js"></script>
            <script>
                SwaggerUIBundle({
                    url: 'viewer.php?id=<?php echo $fileId; ?>&spec=1',
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIBundle.presets.standalone
                    ],
                    plugins: [
                        SwaggerUIBundle.plugins.DownloadUrl
                    ],
                    tryItOutEnabled: true,
                    displayRequestDuration: true,
                    docExpansion: "list",
                    filter: false,
                    showExtensions: true,
                    showCommonExtensions: true,
                    requestInterceptor: function(request) {
                        // Handle CORS for localhost APIs
                        if (request.url.includes('localhost') || request.url.includes('127.0.0.1')) {
                            request.headers = request.headers || {};
                            request.headers['Access-Control-Allow-Origin'] = '*';
                        }
                        return request;
                    }
                });
            </script>
        <?php else: ?>
            <rapi-doc
                spec-url="viewer.php?id=<?php echo $fileId; ?>&spec=1"
                theme="light"
                bg-color="#fafafa"
                text-color="#333"
                header-color="#667eea"
                primary-color="#667eea"
                render-style="view"
                nav-bg-color="#f6f7f9"
                nav-text-color="#333"
                nav-hover-bg-color="#667eea"
                nav-hover-text-color="white"
                nav-accent-color="#667eea"
                show-header="false"
                allow-try="true"
                allow-authentication="true"
                allow-server-selection="true"
                default-schema-tab="schema"
                schema-style="tree"
                schema-expand-level="1">
            </rapi-doc>
        <?php endif; ?>
    </div>
</body>

</html>
