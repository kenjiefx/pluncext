<?php 

namespace Kenjiefx\Pluncext\Implementations\QuarkBundler\QuarkApp\Services;

class JSContentProcessor {

    public function __construct(

    ) {}

    /**
     * Cleans up the JavaScript content for web usage.
     * This method removes comments, empty lines, and import statements,
     * and also processes export statements.
     *
     * @param string $content The JavaScript content to clean up.
     * @return string The cleaned-up JavaScript content.
     */
    public function cleanUpForWeb(string $content): string {
        $result = "";
        // Process each lines
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $leadingSpaces = $this->getLeadingWhitespaceBeforeFirstChar($line);
            $line = trim($line);
            // Skip empty lines
            if (empty($line)) {
                continue;
            }
            // Skip comments
            if (str_starts_with($line, "//") || str_starts_with($line, "#")) {
                continue;
            }
            // Skip import statements
            if (str_starts_with($line, "import") || str_starts_with($line, "require")) {
                continue;
            }
            // If the line starts with export, just remove it
            if (str_starts_with($line, "export ")) {
                $line = substr($line, 7); // Remove 'export ' prefix
            }
            $result .= $leadingSpaces . rtrim($line).PHP_EOL;
        }
        return rtrim($result);
    }

    /**
     * Extracts the class declaration from the provided JavaScript content.
     * @param string $content
     */
    public function getClassDeclaration(string $content) {
        $regex = '/class\s+(\w+)/';
        if (preg_match($regex, $content, $matches)) {
            return $matches[1]; // Return the class name
        }
    }

    public function getLeadingWhitespaceBeforeFirstChar(string $line): string {
        // Match any spaces or tabs at the beginning of the line
        if (preg_match('/^([ \t]*)\S/', $line, $matches)) {
            return $matches[1]; // The captured whitespace
        }
    
        return ''; // No match found
    }
}