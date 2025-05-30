<?php

//TODO: Set the upload directory to the storage directory.
define('UPLOAD_DIR', realpath(__DIR__ . '/../../storage/uploads/'));
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['json', 'yaml', 'yml']);
