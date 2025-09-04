<?php

class RateLimiter
{
    private const RATE_LIMIT_KEY = 'rate_limits';

    /**
     * Check if rate limiting is enabled.
     */
    public static function isEnabled(): bool
    {
        return defined('ENABLE_RATE_LIMITING') ? ENABLE_RATE_LIMITING : true;
    }

    /**
     * Check if an action is rate limited.
     */
    public static function isLimited(string $action, ?string $identifier = null): bool
    {
        // If rate limiting is disabled, never limit
        if (!self::isEnabled()) {
            return false;
        }
        $identifier = $identifier ?: self::getClientIdentifier();
        $key = $action . '_' . $identifier;

        if (!isset($_SESSION[self::RATE_LIMIT_KEY])) {
            $_SESSION[self::RATE_LIMIT_KEY] = [];
        }

        $now = time();
        $limits = $_SESSION[self::RATE_LIMIT_KEY];

        // Clean up old entries.
        foreach ($limits as $limitKey => $data) {
            if ($now - $data['first_attempt'] > self::getWindow($action)) {
                unset($_SESSION[self::RATE_LIMIT_KEY][$limitKey]);
            }
        }

        if (!isset($limits[$key])) {
            return false;
        }

        $limit = $limits[$key];
        $window = self::getWindow($action);
        $maxAttempts = self::getMaxAttempts($action);

        // Check if within time window and exceeded limit.
        if ($now - $limit['first_attempt'] < $window && $limit['count'] >= $maxAttempts) {
            return true;
        }

        // Reset if window has passed.
        if ($now - $limit['first_attempt'] >= $window) {
            unset($_SESSION[self::RATE_LIMIT_KEY][$key]);
        }

        return false;
    }

    /**
     * Record an action attempt.
     */
    public static function recordAttempt(string $action, ?string $identifier = null): void
    {
        // If rate limiting is disabled, don't record attempts
        if (!self::isEnabled()) {
            return;
        }
        $identifier = $identifier ?: self::getClientIdentifier();
        $key = $action . '_' . $identifier;

        if (!isset($_SESSION[self::RATE_LIMIT_KEY])) {
            $_SESSION[self::RATE_LIMIT_KEY] = [];
        }

        $now = time();

        if (!isset($_SESSION[self::RATE_LIMIT_KEY][$key])) {
            $_SESSION[self::RATE_LIMIT_KEY][$key] = [
                'count' => 1,
                'first_attempt' => $now,
                'last_attempt' => $now
            ];
        } else {
            $limit = $_SESSION[self::RATE_LIMIT_KEY][$key];

            // Reset if window has passed.
            if ($now - $limit['first_attempt'] >= self::getWindow($action)) {
                $_SESSION[self::RATE_LIMIT_KEY][$key] = [
                    'count' => 1,
                    'first_attempt' => $now,
                    'last_attempt' => $now
                ];
            } else {
                $_SESSION[self::RATE_LIMIT_KEY][$key]['count']++;
                $_SESSION[self::RATE_LIMIT_KEY][$key]['last_attempt'] = $now;
            }
        }
    }

    /**
     * Get remaining attempts for an action.
     */
    public static function getRemainingAttempts(string $action, ?string $identifier = null): int
    {
        if (self::isLimited($action, $identifier)) {
            return 0;
        }

        $identifier = $identifier ?: self::getClientIdentifier();
        $key = $action . '_' . $identifier;

        if (!isset($_SESSION[self::RATE_LIMIT_KEY][$key])) {
            return self::getMaxAttempts($action);
        }

        return max(0, self::getMaxAttempts($action) - $_SESSION[self::RATE_LIMIT_KEY][$key]['count']);
    }

    /**
     * Get time until limit resets.
     */
    public static function getTimeUntilReset(string $action, ?string $identifier = null): int
    {
        $identifier = $identifier ?: self::getClientIdentifier();
        $key = $action . '_' . $identifier;

        if (!isset($_SESSION[self::RATE_LIMIT_KEY][$key])) {
            return 0;
        }

        $limit = $_SESSION[self::RATE_LIMIT_KEY][$key];
        $window = self::getWindow($action);

        return max(0, $window - (time() - $limit['first_attempt']));
    }

    /**
     * Get client identifier for rate limiting.
     */
    private static function getClientIdentifier(): string
    {
        return hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
    }

    /**
     * Get maximum attempts for an action.
     */
    private static function getMaxAttempts(string $action): int
    {
        switch ($action) {
            case 'upload':
                return defined('UPLOAD_RATE_LIMIT') ? UPLOAD_RATE_LIMIT : 5;
            case 'delete':
                return defined('DELETE_RATE_LIMIT') ? DELETE_RATE_LIMIT : 10;
            default:
                return 10;
        }
    }

    /**
     * Get time window for an action.
     */
    private static function getWindow(string $action): int
    {
        switch ($action) {
            case 'upload':
                return defined('UPLOAD_RATE_WINDOW') ? UPLOAD_RATE_WINDOW : 3600;
            case 'delete':
                return defined('DELETE_RATE_WINDOW') ? DELETE_RATE_WINDOW : 3600;
            default:
                return 3600;
        }
    }
}
