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
            // Configure session to last 1 day (for local student use).
            $sessionLifetime = 24 * 60 * 60; // 1 day in seconds

            // Set session cookie lifetime to 1 day.
            session_set_cookie_params([
                'lifetime' => $sessionLifetime,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                'httponly' => true,
                'samesite' => 'Strict' // More secure.
            ]);

            // Set session garbage collection max lifetime to 1 day.
            ini_set('session.gc_maxlifetime', $sessionLifetime);

            // Increase garbage collection probability for better cleanup.
            ini_set('session.gc_probability', 1);
            ini_set('session.gc_divisor', 100);

            // Use strong session ID generation.
            ini_set('session.entropy_length', 32);
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_httponly', 1);

            session_start();

            // Regenerate session ID periodically for security
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
                session_regenerate_id(true);
            } elseif (time() - $_SESSION['last_regeneration'] > 900) { // 15 minutes
                $_SESSION['last_regeneration'] = time();
                session_regenerate_id(true);
            }

            // Add session fingerprinting for security
            $fingerprint = self::generateFingerprint();
            if (!isset($_SESSION['fingerprint'])) {
                $_SESSION['fingerprint'] = $fingerprint;
            } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
                // Session hijacking detected - destroy session
                session_destroy();
                session_start();
                $_SESSION['fingerprint'] = $fingerprint;
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    /**
     * Generate a browser fingerprint for session security.
     */
    private static function generateFingerprint(): string
    {
        $factors = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? ''
        ];

        return hash('sha256', implode('|', $factors));
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
