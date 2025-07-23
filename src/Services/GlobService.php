<?php

namespace Kenjiefx\Pluncext\Services;

class GlobService {

    public function __construct(

    ) {}

    /**
     * Uses the glob function to find files matching a specified pattern.
     *
     * @param string $pattern The pattern to match files against.
     * @param int $flags Optional flags for the glob function.
     * @return array An array of file paths that match the pattern.
     */
    public function glob(
        string $pattern,
        int $flags = 0
    ): array {
        // Use the built-in glob function to find files matching the pattern
        $files = glob($pattern, $flags);
        if ($files === false) {
            return [];
        }
        return $files;
    }

}