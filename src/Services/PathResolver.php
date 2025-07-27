<?php 

namespace Kenjiefx\Pluncext\Services;

class PathResolver {

    public function __construct(

    ) {}

    /**
     * Resolves a relative file path based on the source file's directory.
     *
     * @param string $referencePath The relative path to resolve.
     * @param string $sourceFilePath The path of the source file where the reference is made.
     * @return string The resolved absolute path.
     */
    public function resolveRelativePath (string $referencePath, string $sourceFilePath): string {
        // Normalize slashes to system directory separator
        $referencePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $referencePath);
        $sourceFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $sourceFilePath);
        // Get the directory of the file where the file path is written
        $fromDir = dirname($sourceFilePath);
        // Join and normalize path
        $fullPath = realpath($fromDir . DIRECTORY_SEPARATOR . $referencePath);
        // If realpath fails (file doesn't exist yet), we manually resolve the path
        if (!$fullPath) {
            $parts = explode(DIRECTORY_SEPARATOR, $fromDir . DIRECTORY_SEPARATOR . $referencePath);
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
        // Retrieve the file extension based on the file extension given in the source path
        $fileExtension = pathinfo($sourceFilePath, PATHINFO_EXTENSION);
        return $fullPath . '.' . $fileExtension;
    }

}