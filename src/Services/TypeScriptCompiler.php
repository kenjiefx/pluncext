<?php 

namespace Kenjiefx\Pluncext\Services;

use Kenjiefx\Pluncext\PluncSettings;

class TypeScriptCompiler {

    /**
     * Indicates whether the TypeScript compiler has been executed.
     * This is used to prevent multiple compilations during the same request.
     */
    public static bool $hasCompiled = false;

    public function __construct(
        private PluncSettings $settings
    ) {}

    public function compile() {
        // Execute the TypeScript compiler, if autoTsc is enabled
        if ($this->settings->autoTsc() && !self::$hasCompiled) {
            exec("tsc");
            self::$hasCompiled = true;
        }
    }

}