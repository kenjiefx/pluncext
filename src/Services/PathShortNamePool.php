<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\PluncExtension;
use Kenjiefx\Pluncext\PluncExtensionSettings;

class PathShortNamePool {

    private static $registry = [];
    private const CHARS = 'abcdefghijklmnOPQRSTUVWXYZ';
    private static array $usedTokens = [];

    public function __construct(
        public readonly PluncExtensionSettings $settings
    ) {}

    public function getShortName(string $absolutePath): string {
        // Normalize the absolute path to ensure consistent formatting
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
        if (isset(self::$registry[$normalizedPath])) {
            return $this->returnShortOrMinifiedName($normalizedPath);
        }
        // Extract the last part of the path as the short name
        $shortName = $this->generateShortName($normalizedPath);
        self::$registry[$normalizedPath] = [
            "shortName" => $shortName,
            "minifiedName" => $this->generateMinifiedName()
        ];
        return $this->returnShortOrMinifiedName($normalizedPath);
    }

    public function exportRegisteredShortNames() {
        $registeredNames = [];
        foreach (self::$registry as $normalizedPath => $registryData) {
            array_push($registeredNames, $this->returnShortOrMinifiedName($normalizedPath));
        }
        return $registeredNames;
    }

    private function returnShortOrMinifiedName(string $normalizedPath): string {
        if ($this->settings->obfuscateHandlers()) {
            return "x" . self::$registry[$normalizedPath]["minifiedName"];
        }
        return self::$registry[$normalizedPath]["shortName"];
    }

    private function generateShortName(
        string $normalizedPath,
        int $differentiator = 1
    ) {
        $baseName = basename($normalizedPath);
        // Remove file extension if present
        $baseName = pathinfo($baseName, PATHINFO_FILENAME);
        $shortName = "{$baseName}_{$differentiator}";
        $doesShortNameExist = false;
        foreach (self::$registry as $absolutePath => $registryData) {
            if ($registryData["shortName"] === $shortName) {
                $doesShortNameExist = true;
                break;
            }
        }
        if ($doesShortNameExist) {
            return $this->generateShortName($normalizedPath, $differentiator + 1);
        } else {
            return $shortName;
        }
    }

    /**
     * Generates a unique token name. 
     * The name consists of three random characters from the defined set,
     * optionally followed by a numeric extension.
     * @param int $nameExt
     * @param string|null $baseName
     * @return string
     */
    public function generateMinifiedName(
        int $nameExt = 0,
        string|null $baseName = null
    ): string {
        $chars = str_split(self::CHARS);
        if ($baseName === null) {
            $baseName = $chars[rand(0,25)].
                        $chars[rand(0,25)].
                        $chars[rand(0,25)];
        }
        // Generate the minified name
        $minifiedName = ($nameExt > 0) ? $baseName.$nameExt : $baseName;

        // Check if the generated name is unique
        if (!in_array($minifiedName, static::$usedTokens)) {

            // If unique, add it to the used tokens and return
            array_push(static::$usedTokens, $minifiedName);

            // Finally, return the unique minified name
            return $minifiedName;
        }

        // If the name is not unique, increment the extension and try again
        $nameExt++;
        return $this->generateMinifiedName($nameExt, $baseName);
    }

}