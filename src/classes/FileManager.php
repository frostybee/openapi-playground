<?php

require_once __DIR__ . '/../helpers/functions.php';

class FileManager
{
    /**
     * Ensure the upload directory exists.
     */
    public static function ensureUploadDirectoryExists(): void
    {
        $uploadDir = self::getUploadDirectory();
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true); // More restrictive permissions
            
            // Add .htaccess to prevent direct access
            $htaccessContent = "Order Deny,Allow\nDeny from all\n";
            file_put_contents($uploadDir . '.htaccess', $htaccessContent);
        }
    }

    /**
     * Get the normalized upload directory path.
     */
    private static function getUploadDirectory(): string
    {
        $path = realpath(UPLOAD_DIR) ?: UPLOAD_DIR;
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Validate that a file name is safe and within allowed patterns.
     */
    private static function validateFileName(string $fileName): bool
    {
        // Remove any directory separators and path traversal attempts
        $fileName = basename($fileName);
        
        // Ensure filename contains only alphanumeric, dots, dashes, and underscores
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $fileName)) {
            return false;
        }
        
        // Check for path traversal attempts
        if (strpos($fileName, '..') !== false || strpos($fileName, '/') !== false || strpos($fileName, '\\') !== false) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate that a file path is within the allowed upload directory (for existing files).
     */
    private static function validateExistingFilePath(string $fileName): bool
    {
        if (!self::validateFileName($fileName)) {
            return false;
        }
        
        $uploadDir = realpath(self::getUploadDirectory());
        $filePath = realpath(self::getUploadDirectory() . basename($fileName));
        
        // Check if file path starts with upload directory (prevent directory traversal)
        return $filePath !== false && strpos($filePath, $uploadDir) === 0;
    }

    /**
     * Process an uploaded file and return the result.
     */
    public static function processUploadedFile(array $uploadedFile, string $customName = ''): array
    {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Upload error occurred.'
            ];
        }

        // Check file size
        if ($uploadedFile['size'] > MAX_FILE_SIZE) {
            return [
                'success' => false,
                'error' => 'File size exceeds maximum allowed size of ' . number_format(MAX_FILE_SIZE / (1024 * 1024), 1) . 'MB.'
            ];
        }

        // Validate the file
        $validation = validateOpenAPIFile($uploadedFile['tmp_name'], $uploadedFile['name']);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => $validation['error']
            ];
        }

        // Generate unique file ID and prepare file info.
        $fileId = uniqid('', true); // More entropy
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $fileName = $fileId . '.' . $extension;
        
        // Validate the generated filename
        if (!self::validateFileName($fileName)) {
            return [
                'success' => false,
                'error' => 'Invalid file name generated.'
            ];
        }
        
        $destination = self::getUploadDirectory() . $fileName;

        // Move the uploaded file with file locking
        if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
            return [
                'success' => false,
                'error' => 'Failed to save uploaded file.'
            ];
        }

        // Set secure file permissions
        chmod($destination, 0644);

        // Sanitize custom name
        $customName = trim(strip_tags($customName));
        
        // Prepare file data
        $fileData = [
            'id' => $fileId,
            'original_name' => basename($uploadedFile['name']),
            'custom_name' => $customName ?: pathinfo(basename($uploadedFile['name']), PATHINFO_FILENAME),
            'file_name' => $fileName,
            'upload_time' => time(),
            'size' => $uploadedFile['size']
        ];

        return [
            'success' => true,
            'file_id' => $fileId,
            'file_data' => $fileData
        ];
    }

    /**
     * Delete a file from the filesystem.
     */
    public static function deleteFile(string $fileName): bool
    {
        if (!self::validateExistingFilePath($fileName)) {
            return false;
        }
        
        $filePath = self::getUploadDirectory() . basename($fileName);

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Check if a file exists in the filesystem.
     */
    public static function fileExists(string $fileName): bool
    {
        if (!self::validateFileName($fileName)) {
            return false;
        }
        
        return file_exists(self::getUploadDirectory() . basename($fileName));
    }

    /**
     * Get the full path to a file.
     */
    public static function getFilePath(string $fileName): string
    {
        if (!self::validateFileName($fileName)) {
            return '';
        }
        
        return self::getUploadDirectory() . basename($fileName);
    }

    /**
     * Get file size in bytes.
     */
    public static function getFileSize(string $fileName): int
    {
        if (!self::validateFileName($fileName)) {
            return 0;
        }
        
        $filePath = self::getUploadDirectory() . basename($fileName);
        return file_exists($filePath) ? filesize($filePath) : 0;
    }

    /**
     * Get file contents.
     */
    public static function getFileContents(string $fileName): string|false
    {
        if (!self::validateFileName($fileName)) {
            return false;
        }
        
        $filePath = self::getUploadDirectory() . basename($fileName);
        return file_exists($filePath) ? file_get_contents($filePath) : false;
    }

    /**
     * Validate if uploaded file is a valid OpenAPI specification.
     */
    public static function validateUploadedFile(string $tmpPath, string $originalName): array
    {
        return validateOpenAPIFile($tmpPath, $originalName);
    }

    /**
     * Clean up orphaned files (files that exist in filesystem but not in session).
     */
    public static function cleanupOrphanedFiles(array $sessionFiles): int
    {
        $cleanedCount = 0;
        $uploadDir = self::getUploadDirectory();

        if (!is_dir($uploadDir)) {
            return $cleanedCount;
        }

        $sessionFileNames = array_column($sessionFiles, 'file_name');
        $filesInDirectory = scandir($uploadDir);

        foreach ($filesInDirectory as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (!in_array($file, $sessionFileNames)) {
                $filePath = $uploadDir . $file;
                if (is_file($filePath) && unlink($filePath)) {
                    $cleanedCount++;
                }
            }
        }

        return $cleanedCount;
    }

}
