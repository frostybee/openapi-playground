<?php

class SessionManager
{
    private const UPLOADED_FILES_KEY = 'uploaded_files';

    /**
     * Start the session if not already started.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session to last 4 days (4 * 24 * 60 * 60 = 345600 seconds).
            $sessionLifetime = 4 * 24 * 60 * 60; // 4 days in seconds

            // Set session cookie lifetime to 4 days.
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            // Set session garbage collection max lifetime to 4 days.
            ini_set('session.gc_maxlifetime', $sessionLifetime);

            // Increase garbage collection probability for better cleanup.
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);

            session_start();
        }
    }

    /**
     * Initialize the uploaded files storage in session.
     */
    public static function initializeUploadedFiles(): void
    {
        if (!isset($_SESSION[self::UPLOADED_FILES_KEY])) {
            $_SESSION[self::UPLOADED_FILES_KEY] = [];
        }
    }

    /**
     * Add a new uploaded file to the session.
     */
    public static function addUploadedFile(string $fileId, array $fileData): void
    {
        $_SESSION[self::UPLOADED_FILES_KEY][$fileId] = $fileData;
    }

    /**
     * Get all uploaded files from the session.
     */
    public static function getUploadedFiles(): array
    {
        return $_SESSION[self::UPLOADED_FILES_KEY] ?? [];
    }

    /**
     * Get a specific uploaded file by ID.
     */
    public static function getUploadedFile(string $fileId): ?array
    {
        return $_SESSION[self::UPLOADED_FILES_KEY][$fileId] ?? null;
    }

    /**
     * Remove an uploaded file from the session.
     */
    public static function removeUploadedFile(string $fileId): bool
    {
        if (isset($_SESSION[self::UPLOADED_FILES_KEY][$fileId])) {
            unset($_SESSION[self::UPLOADED_FILES_KEY][$fileId]);
            return true;
        }
        return false;
    }

    /**
     * Check if a file exists in the session.
     */
    public static function hasUploadedFile(string $fileId): bool
    {
        return isset($_SESSION[self::UPLOADED_FILES_KEY][$fileId]);
    }

    /**
     * Get the count of uploaded files.
     */
    public static function getUploadedFilesCount(): int
    {
        return count($_SESSION[self::UPLOADED_FILES_KEY] ?? []);
    }

    /**
     * Clear all uploaded files from the session.
     */
    public static function clearUploadedFiles(): void
    {
        $_SESSION[self::UPLOADED_FILES_KEY] = [];
    }

    /**
     * Get uploaded files in reverse chronological order (newest first).
     */
    public static function getUploadedFilesReversed(): array
    {
        return array_reverse(self::getUploadedFiles(), true);
    }
}
