<?php

// Validate the OpenAPI file.
function validateOpenAPIFile($filePath, $originalName)
{
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file type. Only JSON, YAML, and YML files are allowed.'];
    }

    $content = file_get_contents($filePath);

    if ($extension === 'json') {
        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['valid' => false, 'error' => 'Invalid JSON format.'];
        }
    } else {
        // Basic YAML validation (you might want to use a proper YAML parser).
        if (strpos($content, 'openapi:') === false && strpos($content, 'swagger:') === false) {
            return ['valid' => false, 'error' => 'File does not appear to be a valid OpenAPI specification.'];
        }
    }

    return ['valid' => true];
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
