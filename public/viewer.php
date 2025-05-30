<?php
session_start();
require_once '../src/config/app.php';

$fileId = $_GET['id'] ?? '';
$renderer = $_GET['renderer'] ?? 'swagger';

if (!$fileId || !isset($_SESSION['uploaded_files'][$fileId])) {
    var_dump($_SESSION['uploaded_files']);exit;
    header('Location: index.php');
    exit;
}

$file = $_SESSION['uploaded_files'][$fileId];
$filePath = UPLOAD_DIR . $file['file_name'];

if (!file_exists($filePath)) {
    header('Location: index.php');
    exit;
}

// Determine if we need to serve the spec file directly
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

    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 1.5em;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .btn-active {
            background: white;
            color: #667eea;
        }

        .viewer-container {
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        #swagger-ui {
            height: 100%;
        }

        rapi-doc {
            height: 100%;
        }
    </style>

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
                    layout: "StandaloneLayout",
                    tryItOutEnabled: true,
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