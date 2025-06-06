<?php

// Set the timezone to America/Toronto (EST timezone).
date_default_timezone_set('America/Toronto');

// Set the upload directory to the storage directory.
define('UPLOAD_DIR', __DIR__ . '/../../storage/uploads/');

// Set the maximum file size to 5MB.
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Set the allowed extensions.
define('ALLOWED_EXTENSIONS', ['json', 'yaml', 'yml']);
