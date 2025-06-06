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
            mkdir($uploadDir, 0755, true);
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
     * Process an uploaded file and return the result.
     */
    public static function processUploadedFile(array $uploadedFile, string $customName = ''): array
    {
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => 'Upload error: ' . $uploadedFile['error']
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

        // Generate unique file ID and prepare file info
        $fileId = uniqid();
        $extension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
        $fileName = $fileId . '.' . $extension;
        $destination = self::getUploadDirectory() . $fileName;

        // Move the uploaded file
        if (!move_uploaded_file($uploadedFile['tmp_name'], $destination)) {
            return [
                'success' => false,
                'error' => 'Failed to save uploaded file.'
            ];
        }

        // Prepare file data
        $fileData = [
            'id' => $fileId,
            'original_name' => $uploadedFile['name'],
            'custom_name' => $customName ?: pathinfo($uploadedFile['name'], PATHINFO_FILENAME),
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
        $filePath = self::getUploadDirectory() . $fileName;

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
        return file_exists(self::getUploadDirectory() . $fileName);
    }

    /**
     * Get the full path to a file.
     */
    public static function getFilePath(string $fileName): string
    {
        return self::getUploadDirectory() . $fileName;
    }

    /**
     * Get file size in bytes.
     */
    public static function getFileSize(string $fileName): int
    {
        $filePath = self::getUploadDirectory() . $fileName;
        return file_exists($filePath) ? filesize($filePath) : 0;
    }

    /**
     * Get file contents.
     */
    public static function getFileContents(string $fileName): string|false
    {
        $filePath = self::getUploadDirectory() . $fileName;
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

    /**
     * Fix misplaced files by moving them from storage/ to storage/uploads/
     */
    public static function fixMisplacedFiles(): int
    {
        $movedCount = 0;
        $storageDir = __DIR__ . '/../../storage/';
        $uploadDir = self::getUploadDirectory();

        // Ensure upload directory exists
        self::ensureUploadDirectoryExists();

        if (!is_dir($storageDir)) {
            return $movedCount;
        }

        $filesInStorage = scandir($storageDir);

        foreach ($filesInStorage as $file) {
            // Skip directories and system files
            if ($file === '.' || $file === '..' || is_dir($storageDir . $file)) {
                continue;
            }

            // Only move files that look like uploaded files (with unique ID pattern)
            if (preg_match('/^[a-f0-9]{13}\.(json|yaml|yml)$/i', $file)) {
                $sourcePath = $storageDir . $file;
                $destinationPath = $uploadDir . $file;

                if (rename($sourcePath, $destinationPath)) {
                    $movedCount++;
                }
            }
        }

        return $movedCount;
    }
}
