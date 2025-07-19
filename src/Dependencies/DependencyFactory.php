<?php 

namespace Kenjiefx\Pluncext\Dependencies;

use Kenjiefx\Pluncext\Services\PathResolver;

class DependencyFactory
{

    public function __construct(
        private PathResolver $pathResolver
    ) {}

    /**
     * Creates a DependencyModel instance from a given import statement.
     *
     * @param string $importStatement The import statement to parse.
     * @return DependencyModel
     */
    public function createFromImportStatement(
        string $importStatement,
        string $absPathOfImportingModule
    ): DependencyModel {
        $parsed = $this->parseImportStatement($importStatement);
        $importRelativePath = $parsed['relativePath'];
        // Resolve the absolute path of the imported module
        $importAbsolutePath = $this->pathResolver->resolveRelativePath(
            $importRelativePath,
            $absPathOfImportingModule
        );
        return new DependencyModel(
            imports: $parsed['imports'],
            absolutePath: $importAbsolutePath
        );
    }

    /**
     * Parses an import statement to extract the imported modules and their relativePath.
     * 
     * @param string $import The import statement to parse.
     * @return array An associative array containing 'imports' and 'relativePath'.
     */
    public function parseImportStatement(
        string $import
    ): array {
        $result = [
            'imports' => [],
            'relativePath' => ''
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
            $result['relativePath'] = $location;
        }
        return $result;
    }
}