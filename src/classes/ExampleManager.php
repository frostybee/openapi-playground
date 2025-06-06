<?php

require_once __DIR__ . '/../helpers/functions.php';

class ExampleManager
{
    /**
     * Get the examples directory path.
     */
    private static function getExamplesDirectory(): string
    {
        $path = realpath(__DIR__ . '/../../examples') ?: __DIR__ . '/../../examples';
        return rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Get all available example files.
     */
    public static function getAvailableExamples(): array
    {
        $examplesDir = self::getExamplesDirectory();
        $examples = [];

        if (!is_dir($examplesDir)) {
            return $examples;
        }

        $files = scandir($examplesDir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $examplesDir . $file;
            if (!is_file($filePath)) {
                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($extension, ['json', 'yaml', 'yml'])) {
                continue;
            }

            // Validate the OpenAPI file.
            $validation = validateOpenAPIFile($filePath, $file);
            if (!$validation['valid']) {
                continue;
            }

            $examples[] = [
                'id' => 'example_' . pathinfo($file, PATHINFO_FILENAME),
                'file_name' => $file,
                'display_name' => self::generateDisplayName($file),
                'description' => self::getFileDescription($filePath),
                'size' => filesize($filePath),
                'is_example' => true
            ];
        }

        return $examples;
    }

    /**
     * Get a specific example file by ID.
     */
    public static function getExample(string $exampleId): ?array
    {
        if (!str_starts_with($exampleId, 'example_')) {
            return null;
        }

        $examples = self::getAvailableExamples();
        foreach ($examples as $example) {
            if ($example['id'] === $exampleId) {
                return $example;
            }
        }

        return null;
    }

    /**
     * Check if an example file exists.
     */
    public static function exampleExists(string $fileName): bool
    {
        $filePath = self::getExamplesDirectory() . $fileName;
        return file_exists($filePath);
    }

    /**
     * Get the full path to an example file.
     */
    public static function getExampleFilePath(string $fileName): string
    {
        return self::getExamplesDirectory() . $fileName;
    }

    /**
     * Get example file contents.
     */
    public static function getExampleContents(string $fileName): string|false
    {
        $filePath = self::getExamplesDirectory() . $fileName;
        return file_exists($filePath) ? file_get_contents($filePath) : false;
    }

    /**
     * Generate a display name from filename.
     */
    private static function generateDisplayName(string $fileName): string
    {
        $name = pathinfo($fileName, PATHINFO_FILENAME);

        // Convert snake_case and kebab-case to Title Case.
        $name = str_replace(['_', '-'], ' ', $name);
        $name = ucwords($name);

        return $name;
    }

    /**
     * Extract description from OpenAPI file.
     */
    private static function getFileDescription(string $filePath): string
    {
        try {
            $content = file_get_contents($filePath);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if ($extension === 'json') {
                $data = json_decode($content, true);
            } else {
                // For YAML files, we'll do a simple regex extraction.
                // Since we don't want to add YAML parsing dependency.
                if (preg_match('/description:\s*[|>-]?\s*(.+?)(?=\n\s*[a-zA-Z_]|\n\n|\Z)/s', $content, $matches)) {
                    return trim(preg_replace('/^\s*-?\s*/m', '', $matches[1]));
                }
                return 'Example OpenAPI specification';
            }

            return $data['info']['description'] ?? 'Example OpenAPI specification';
        } catch (Exception $e) {
            return 'Example OpenAPI specification';
        }
    }
}
