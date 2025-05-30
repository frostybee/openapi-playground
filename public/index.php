<?php
session_start();

require_once '../src/config/app.php';
require_once '../src/helpers/functions.php';

//TODO: Move these functions to a helper file.
// Create uploads directory if it doesn't exist.
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Initialize session storage for uploaded files.
if (!isset($_SESSION['uploaded_files'])) {
    $_SESSION['uploaded_files'] = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenAPI Schema Viewer</title>
    <link rel="stylesheet" href="./assets/css/main.css">
</head>

<body>
    <div class="container">
        <h1>OpenAPI Schema Viewer</h1>
        <p class="subtitle">Upload your OpenAPI specifications and view them with Swagger UI or RapiDoc</p>

        <?php
        // Handle file upload
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['openapi_file'])) {
            $uploadedFile = $_FILES['openapi_file'];
            $customName = trim($_POST['custom_name'] ?? '');

            if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
                $validation = validateOpenAPIFile($uploadedFile['tmp_name'], $uploadedFile['name']);

                if ($validation['valid']) {
                    $fileId = uniqid();
                    $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
                    $fileName = $fileId . '.' . $extension;
                    $destination = UPLOAD_DIR . $fileName;

                    if (move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
                        $_SESSION['uploaded_files'][$fileId] = [
                            'id' => $fileId,
                            'original_name' => $uploadedFile['name'],
                            'custom_name' => $customName ?: pathinfo($uploadedFile['name'], PATHINFO_FILENAME),
                            'file_name' => $fileName,
                            'upload_time' => time(),
                            'size' => $uploadedFile['size']
                        ];

                        echo '<div class="alert alert-success">✓ File uploaded successfully!</div>';
                    } else {
                        echo '<div class="alert alert-error">✗ Failed to save uploaded file.</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">✗ ' . htmlspecialchars($validation['error']) . '</div>';
                }
            } else {
                echo '<div class="alert alert-error">✗ Upload error: ' . $uploadedFile['error'] . '</div>';
            }
        }

        // Handle file deletion
        if (isset($_GET['delete'])) {
            $fileId = $_GET['delete'];
            if (isset($_SESSION['uploaded_files'][$fileId])) {
                $fileName = $_SESSION['uploaded_files'][$fileId]['file_name'];
                $filePath = UPLOAD_DIR . $fileName;

                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                unset($_SESSION['uploaded_files'][$fileId]);
                echo '<div class="alert alert-success">✓ File deleted successfully!</div>';
            }
        }
        ?>

        <div class="upload-section">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="openapi_file">Choose OpenAPI File (.json, .yaml, .yml)</label>
                    <input type="file" id="openapi_file" name="openapi_file" accept=".json,.yaml,.yml" required>
                </div>

                <div class="form-group">
                    <label for="custom_name">Custom Name (Optional)</label>
                    <input type="text" id="custom_name" name="custom_name" placeholder="e.g., My API v1.0">
                </div>

                <button type="submit" class="btn">Upload & View</button>
            </form>
        </div>

        <?php if (!empty($_SESSION['uploaded_files'])): ?>
            <h2>Your Uploaded Files</h2>
            <div class="file-list">
                <?php foreach (array_reverse($_SESSION['uploaded_files'], true) as $file): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <h3><?php echo htmlspecialchars($file['custom_name']); ?></h3>
                            <div class="meta">
                                Original: <?php echo htmlspecialchars($file['original_name']); ?> •
                                <?php echo number_format($file['size'] / 1024, 1); ?> KB •
                                <?php echo date('M j, Y g:i A', $file['upload_time']); ?>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="viewer.php?id=<?php echo $file['id']; ?>&renderer=swagger" class="btn-small btn-swagger">Swagger UI</a>
                            <a href="viewer.php?id=<?php echo $file['id']; ?>&renderer=rapidoc" class="btn-small btn-rapidoc">RapiDoc</a>
                            <a href="?delete=<?php echo $file['id']; ?>" class="btn-small btn-delete" onclick="return confirm('Are you sure you want to delete this file?')">Delete</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="help-text">
            <h3>How to Use</h3>
            <ul>
                <li><strong>Upload:</strong> Select your OpenAPI specification file (.json, .yaml, or .yml)</li>
                <li><strong>View:</strong> Choose between Swagger UI (industry standard) or RapiDoc (modern design)</li>
                <li><strong>Test:</strong> Use the "Try it out" functionality to test your APIs running on localhost</li>
                <li><strong>Compare:</strong> View the same API spec with both renderers to see the differences</li>
            </ul>
        </div>
    </div>
</body>

</html>