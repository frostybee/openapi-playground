<?php

class CSRFProtection
{
    private const TOKEN_KEY = 'csrf_token';
    private const TOKEN_LIFETIME = 86400; // 24 hours

    /**
     * Generate a new CSRF token and store it in the session.
     */
    public static function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_KEY] = [
            'token' => $token,
            'timestamp' => time()
        ];
        return $token;
    }

    /**
     * Get the current CSRF token from the session.
     */
    public static function getToken(): string
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return self::generateToken();
        }

        $tokenData = $_SESSION[self::TOKEN_KEY];

        // Handle legacy string tokens or invalid data.
        if (!is_array($tokenData) || !isset($tokenData['timestamp']) || !isset($tokenData['token'])) {
            return self::generateToken();
        }

        // Check if token has expired
        if ((time() - $tokenData['timestamp']) > self::TOKEN_LIFETIME) {
            return self::generateToken();
        }

        return $tokenData['token'];
    }

    /**
     * Verify a CSRF token.
     */
    public static function verifyToken(string $token): bool
    {
        if (!isset($_SESSION[self::TOKEN_KEY])) {
            return false;
        }

        $tokenData = $_SESSION[self::TOKEN_KEY];

        // Handle legacy string tokens or invalid data
        if (!is_array($tokenData) || !isset($tokenData['timestamp']) || !isset($tokenData['token'])) {
            return false;
        }

        // Check if token has expired
        if ((time() - $tokenData['timestamp']) > self::TOKEN_LIFETIME) {
            return false;
        }

        return hash_equals($tokenData['token'], $token);
    }

    /**
     * Generate a hidden input field with the CSRF token.
     */
    public static function getTokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Validate CSRF token from POST request.
     */
    public static function validateRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // Only validate POST requests
        }

        $token = $_POST['csrf_token'] ?? '';
        return self::verifyToken($token);
    }
}
