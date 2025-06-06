<?php
require_once '../src/config/app.php';
require_once '../src/helpers/functions.php';
require_once '../src/classes/SessionManager.php';
require_once '../src/classes/FileManager.php';

// Start session and initialize uploaded files storage
SessionManager::start();
SessionManager::initializeUploadedFiles();

// Ensure upload directory exists
FileManager::ensureUploadDirectoryExists();
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

            $result = FileManager::processUploadedFile($uploadedFile, $customName);

            if ($result['success']) {
                SessionManager::addUploadedFile($result['file_id'], $result['file_data']);
                echo '<div class="alert alert-success">✓ File uploaded successfully!</div>';
            } else {
                echo '<div class="alert alert-error">✗ ' . htmlspecialchars($result['error']) . '</div>';
            }
        }

        // Handle file deletion.
        if (isset($_GET['delete'])) {
            $fileId = $_GET['delete'];
            $fileData = SessionManager::getUploadedFile($fileId);

            if ($fileData) {
                // Delete the file from filesystem
                FileManager::deleteFile($fileData['file_name']);

                // Remove from session
                SessionManager::removeUploadedFile($fileId);
                echo '<div class="alert alert-success">✓ File deleted successfully!</div>';
            }
        }

        // Handle cleanup of orphaned files (optional feature)
        if (isset($_GET['cleanup'])) {
            $cleanedCount = FileManager::cleanupOrphanedFiles(SessionManager::getUploadedFiles());
            if ($cleanedCount > 0) {
                echo '<div class="alert alert-success">✓ Cleaned up ' . $cleanedCount . ' orphaned file(s).</div>';
            } else {
                echo '<div class="alert alert-success">✓ No orphaned files found.</div>';
            }
        }

        // Handle fixing misplaced files (move from storage/ to storage/uploads/)
        if (isset($_GET['fix_misplaced'])) {
            $movedCount = FileManager::fixMisplacedFiles();
            if ($movedCount > 0) {
                echo '<div class="alert alert-success">✓ Moved ' . $movedCount . ' misplaced file(s) to the correct uploads directory.</div>';
            } else {
                echo '<div class="alert alert-success">✓ No misplaced files found.</div>';
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

        <?php if (SessionManager::getUploadedFilesCount() > 0): ?>
            <h2>Your Uploaded Files</h2>
            <div class="file-list">
                <?php foreach (SessionManager::getUploadedFilesReversed() as $file): ?>
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
