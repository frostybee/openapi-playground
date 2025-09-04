<?php

// Validate the OpenAPI file.
function validateOpenAPIFile($filePath, $originalName)
{
    $extension = strtolower(pathinfo(basename($originalName), PATHINFO_EXTENSION));

    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only JSON, YAML, and YML files are allowed.'];
    }

    // Check file size
    $fileSize = filesize($filePath);
    if ($fileSize === false || $fileSize > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds maximum allowed size.'];
    }

    $content = file_get_contents($filePath);
    if ($content === false) {
        return ['valid' => false, 'error' => 'Unable to read file contents.'];
    }

    // Sanitize content - remove potential security risks
    $content = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $content);

    if ($extension === 'json') {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['valid' => false, 'error' => 'Invalid JSON format: ' . json_last_error_msg()];
        }
        
        // Validate OpenAPI structure
        if (!validateOpenAPIStructure($decoded)) {
            return ['valid' => false, 'error' => 'File does not appear to be a valid OpenAPI specification.'];
        }
    } else {
        // Enhanced YAML validation
        if (!validateYAMLContent($content)) {
            return ['valid' => false, 'error' => 'Invalid YAML format or not a valid OpenAPI specification.'];
        }
    }

    return ['valid' => true];
}

// Validate OpenAPI structure for JSON files
function validateOpenAPIStructure($data)
{
    if (!is_array($data)) {
        return false;
    }
    
    // Check for OpenAPI version indicators
    return isset($data['openapi']) || isset($data['swagger']);
}

// Enhanced YAML validation
function validateYAMLContent($content)
{
    // Check for basic YAML structure indicators
    if (!preg_match('/^[\s]*(?:openapi|swagger)[\s]*:/', $content, $matches)) {
        return false;
    }
    
    // Check for common YAML syntax errors
    $lines = explode("\n", $content);
    $indentStack = [];
    
    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);
        
        // Skip empty lines and comments
        if (empty($trimmed) || $trimmed[0] === '#') {
            continue;
        }
        
        // Basic YAML syntax validation
        if (preg_match('/[^\x20-\x7E\t\n\r]/', $line)) {
            // Contains non-printable characters (potential security risk)
            return false;
        }
        
        // Check for tabs (YAML doesn't allow tabs for indentation)
        if (strpos($line, "\t") !== false && !empty(trim($line))) {
            return false;
        }
    }
    
    return true;
}

// Convert YAML to JSON.
function convertYamlToJson($yamlPath)
{
    // Simple YAML to JSON conversion for basic cases.
    // In production, you'd want to use a proper YAML parser like symfony/yaml.
    $content = file_get_contents($yamlPath);

    // This is a very basic conversion - consider using a proper YAML library.
    // For now, we'll serve YAML files directly to the viewers as they support both formats.
    return $content;
}
