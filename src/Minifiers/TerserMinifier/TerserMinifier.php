<?php 

namespace Kenjiefx\Pluncext\Minifiers\TerserMinifier;

use Kenjiefx\Pluncext\PluncExtensionSettings;
use Kenjiefx\Pluncext\Services\PathShortNamePool;

/**
 * A minification service that runs on top of 
 * Terser minifier
 */
class TerserMinifier {

    public function __construct(
        public readonly PathShortNamePool $pathShortNamePool,
        public readonly PluncExtensionSettings $settings
    ) {}
    
    /**
     * Minifies the provided source code using Terser.
     *
     * @param string $sourceCode The source code to be minified.
     * @return string The minified source code.
     */
    public function minify(
        string $sourceCode
    ) {
        // If minification is not enabled, return the original source code
        if (!$this->settings->minify()) {
            return $sourceCode;
        }
        file_put_contents(__dir__ .'/src.js', $sourceCode);
        $reserved = [
            '\"$scope\"',
            '\"$patch\"',
            '\"$block\"',
            '\"$parent\"',
            '\"$children\"',
            '\"$app\"',
            '\"$this\"'
        ];
        foreach ($this->pathShortNamePool->exportRegisteredShortNames() as $uniqueToken) {
            array_push($reserved, '\"' . $uniqueToken .'\"');
        }
        $arg = "[" . implode(',' , $reserved) . "]";
        $exitCode = 0;
        $output = [];
        $sourcePath = __dir__.'/terser.js ';
        exec(
            'node '.$sourcePath.$arg,
            $output,
            $exitCode
        );
        return file_get_contents(__dir__ . '/min.js');
    }

}