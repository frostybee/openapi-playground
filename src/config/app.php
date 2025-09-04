<?php

// Set the timezone to America/Toronto (EST timezone).
date_default_timezone_set('America/Toronto');

// Set the upload directory to the storage directory.
define('UPLOAD_DIR', realpath(__DIR__ . '/../../storage/uploads/') ?: __DIR__ . '/../../storage/uploads/');

// Set the maximum file size to 5MB.
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Set the allowed extensions.
define('ALLOWED_EXTENSIONS', ['json', 'yaml', 'yml']);

// Rate limiting configuration.
define('ENABLE_RATE_LIMITING', false); // Set to true to enable rate limiting
define('UPLOAD_RATE_LIMIT', 5); // Maximum uploads per hour
define('UPLOAD_RATE_WINDOW', 3600); // Time window in seconds (1 hour)
define('DELETE_RATE_LIMIT', 10); // Maximum deletes per hour
define('DELETE_RATE_WINDOW', 3600); // Time window in seconds (1 hour)
