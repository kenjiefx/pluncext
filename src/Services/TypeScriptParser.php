<?php 

namespace Kenjiefx\Pluncext\Services;

class TypeScriptParser {

    public function __construct() {}

    /**
     * Parses TypeScript content and returns an array of exported class names.
     *
     * @param string $tsContent The TypeScript content to parse.
     * @return array An array of exported class names.
     */
    public function getExportedClasses(
        string $tsContent
    ) {
        $exportedClasses = [];
        preg_match_all('/\bexport\s+class\s+([A-Za-z_][A-Za-z0-9_]*)\b/', $tsContent, $matches);
        if (!empty($matches[1])) {
            $exportedClasses = $matches[1];
        }
        return $exportedClasses;
    }

    /**
     * Parses the module content to extract import statements.
     * This method uses a regular expression to find all import statements 
     * in the provided module content and returns an array of unique dependencies.
     * 
     * @param string $tsContent
     * @return array
     */
    public function extractImportStatements(
        string $tsContent
    ): array {
        $lines = preg_split('/\r\n|\r|\n/', $tsContent);
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
     * Extracts the constructor arguments from a TypeScript class.
     * This method uses a regular expression to find the constructor method
     * and extracts its parameters, returning them as an array.
     * 
     * It is expected that this function will return the constructor 
     * arguments of the first class declared in the TypeScript content.
     * 
     * If multiple classes are present, it will only parse the first 
     * one found and it shouldn't be used for files with multiple classes.
     * 
     * @param string $tsContent The TypeScript content to parse.
     * @return array An array of constructor argument names.
     */
    public function getArgumentOfFirstClassConstructor(
        string $tsContent
    ) {
        if (preg_match('/constructor\s*\((.*?)\)/s', $tsContent, $matches)) {
            $constructorArgs = trim($matches[1]);
            if ($constructorArgs === "") return [];
            return array_map('trim', explode(',', $constructorArgs));
        }
        return [];
    }

}