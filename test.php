<?php 

$tsFilePath = __dir__ . '/theme/nucleus/services/Posts/PostService.ts';
$tsContent = file_get_contents($tsFilePath);

function getExportedClasses(string $typescript): array {
    $exportedClasses = [];

    // Match all `export class ClassName` declarations
    preg_match_all('/\bexport\s+class\s+([A-Za-z_][A-Za-z0-9_]*)\b/', $typescript, $matches);

    if (!empty($matches[1])) {
        $exportedClasses = $matches[1];
    }

    return $exportedClasses;
}

$exportedClasses = getExportedClasses($tsContent);
var_dump($exportedClasses);