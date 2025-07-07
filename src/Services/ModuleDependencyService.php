<?php 

namespace Kenjiefx\Pluncext\Services;

class ModuleDependencyService {

    public function __construct(

    ) {}

    /**
     * Parses the module content to extract import statements.
     * This method uses a regular expression to find all import statements 
     * in the provided module content and returns an array of unique dependencies.
     * 
     * @param string $moduleContent
     * @return array
     */
    public function extractImportStmts(
        string $moduleContent
    ): array {
        $lines = preg_split('/\r\n|\r|\n/', $moduleContent);
        $statements = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Match static ES6 import statements (with or without 'from')
            if (preg_match('/^import\s.+\sfrom\s+[\'"].+[\'"];?$/', $trimmed) ||
                preg_match('/^import\s+[\'"].+[\'"];?$/', $trimmed)) {
                $statements[] = $trimmed;
            }
        }
        return $statements;
    }

    /**
     * Parses an import statement to extract the imported modules and their location.
     * 
     * @param string $import The import statement to parse.
     * @return array An associative array with 'imports' and 'location'.
     */
    public function parseImportStatement(string $import): array
    {
        $result = [
            'imports' => [],
            'location' => ''
        ];
        // Regex to extract the import block and the module path
        $pattern = '/import\s*{([^}]+)}\s*from\s*[\'"]([^\'"]+)[\'"]/';
        if (preg_match($pattern, $import, $matches)) {
            $importsPart = trim($matches[1]);
            $location = trim($matches[2]);
            // Split multiple imports by comma
            $imports = array_map('trim', explode(',', $importsPart));
            foreach ($imports as $imp) {
                // Check for alias using "as"
                if (preg_match('/(\w+)\s+as\s+(\w+)/', $imp, $aliasMatch)) {
                    $result['imports'][$aliasMatch[1]] = $aliasMatch[2];
                } else {
                    $result['imports'][$imp] = $imp;
                }
            }
            $result['location'] = $location;
        }
        return $result;
    }

    /**
     * Resolves the import path relative to the file where the import is written.
     * 
     * @param string $importPath The import path to resolve.
     * @param string $fromFilePath The absolute path of the file where the import is written.
     * @return string The resolved absolute path of the imported module.
     */
    public function resolveImportPath(string $importPath, string $fromJsFilePath): string {
        // Normalize slashes to system directory separator
        $importPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $importPath);
        $fromJsFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fromJsFilePath);
    
        // Get the directory of the file where the import is happening
        $fromDir = dirname($fromJsFilePath);
    
        // Join and normalize path
        $fullPath = realpath($fromDir . DIRECTORY_SEPARATOR . $importPath);
    
        // If realpath fails (file doesn't exist yet), we manually resolve the path
        if (!$fullPath) {
            $parts = explode(DIRECTORY_SEPARATOR, $fromDir . DIRECTORY_SEPARATOR . $importPath);
            $resolved = [];
            foreach ($parts as $part) {
                if ($part === '' || $part === '.') continue;
                if ($part === '..') {
                    array_pop($resolved);
                } else {
                    $resolved[] = $part;
                }
            }
            $fullPath = implode(DIRECTORY_SEPARATOR, $resolved);
        }
    
        // Add the file extension
        return $fullPath . '.js';
    }

}